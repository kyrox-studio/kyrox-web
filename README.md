# kyrox.studio web sitesi

Kyrox Studio'nun web sitesi: WordPress ve bu tasarım için yazılmış özel tema. İçerik (blog yazıları, ana sayfa metinleri, iş kartları, iletişim bilgileri) WordPress panelinden yönetilir; bu repo temayı ve sunucu kurulum dosyalarını tutar.

Karar kaydı: [ADR-0011](https://github.com/kyrox-studio/kyrox-platform/blob/main/ecosystem/decisions/0011-web-site-repository.md). Marka kuralları [kyrox-platform/standards/brand](https://github.com/kyrox-studio/kyrox-platform/blob/main/standards/brand/BRAND_STANDARD.md) altında; logo dosyaları [kyrox-brand](https://github.com/kyrox-studio/kyrox-brand)'den kopyalanır, burada elle düzenlenmez.

## Klasörler

| Klasör | İçerik |
|---|---|
| `tema/kyrox/` | WordPress teması (sunucuya bu klasör bağlanır) |
| `sunucu/` | WordPress + MariaDB için Docker Compose, PHP/MariaDB/Apache ayarları, `.env.example` |
| `tasarim/` | Onaylı statik tasarım (HTML, CSS, görseller) ve onu üreten küçük derleyici. Temanın kaynağı; tema bununla birebir aynı HTML'i üretir |
| `GORSEL_KOMUTLARI.md` | Yapay zekâ ile görsel üretmek için hazır komutlar ve kurallar |

## Panelden neler yönetilir

- **Yazılar:** blog. Yazılar `/blog/yazi-adi/` adresinde. Özet alanı Google ve paylaşım önizlemesinde kullanılır; etiketler yazının üstünde görünür.
- **İşler** (sol menü): ana sayfadaki iş kartları. Her kartta tür, durum (Kullanımda / Tezgâhta), link ve teknik çizim seçimi var; sırayı "Sıra" alanı belirler.
- **Sayfalar > Fuar CRM:** `/fuar-crm/` ürün tanıtımının başlığı, özeti ve içeriği. Tema güncellemesinden sonra yönetici paneli ilk açıldığında eksik başlangıç sayfası eklenir; mevcut sayfa içeriği değiştirilmez. Uygulama giriş adresi ürün tanımından gelir.
- **Sayfalar > Stand Konfiguratör:** `/fuar-stand/` V3 tanıtımı. Elikon'un gerçek 9 × 5 m projesinden üretilen sahne otomatik açılır; sürümlü model tarayıcı önbelleğinde saklanır. İlk indirmede kapak korunur, sonraki açılışta önbellek kullanılır; depolama kullanılamazsa normal indirmeye dönülür. Ziyaretçi döndürür/yakınlaştırır; editör, oturum bilgileri, müşteri verisi veya kayıt API'si tanıtıma taşınmaz. Eski V1/V2 kaynakları korunmuştur. Ürün sayfalarını paneli açmadan kurmak için: `wp eval 'kyrox_urun_sayfalarini_kur();'` (yönetici kullanıcıyla).
- **Görünüm > Özelleştir > Kyrox Studio:**
  - koyu / krem görünüm
  - açılış, giriş, atölye kartları, işler, blog ve iş fişi bölümlerinin metinleri
  - iletişim bilgileri (e-posta, WhatsApp, adres)
  - Sık sorulan sorular bölümünün başlığı ve altı soru-cevabı
  - Google ve yapay zekâ için başlık, açıklama ve kurum tanıtımı

Varsayılan metinler onaylı tasarımdakilerdir; bir alan boş bırakılırsa varsayılana döner.

## Arama motorları ve yapay zekâ

Tema şunları kendisi üretir:
- her sayfada açıklama, canonical ve Open Graph etiketleri
- JSON-LD: `LocalBusiness`, `WebSite`; yazılarda ayrıca `BlogPosting` ve `BreadcrumbList`
- `robots.txt`: yapay zekâ tarayıcıları açıkça davetli
- `/llms.txt`: kurum, hizmetler, ürünler ve yazıların özeti
- ana sayfadaki SSS metinleriyle aynı içerikten üretilen `FAQPage` bilgisi; SSS ayrıca `/llms.txt` içinde yer alır
- site haritası: WordPress'in `/wp-sitemap.xml` dosyası; kullanıcı listesi çıkarılmıştır

Yazı tipleri (Archivo, Inter) temanın içindedir; Google Fonts'a istek gitmez.

## Bilgisayarda çalıştırmak

Docker Desktop gerekir.

```bash
cd sunucu
cp .env.example .env    # değerleri doldurun (rastgele şifreler)
docker compose up -d
```

Site `http://localhost:8090` adresinde açılır. İlk kurulum (WP-CLI ile):

```bash
docker compose run --rm cli wp core install --url=http://localhost:8090 --title="Kyrox Studio" --admin_user=kyrox --admin_password=<şifre> --admin_email=hello@kyrox.studio --skip-email
docker compose run --rm cli wp language core install tr_TR --activate
docker compose run --rm cli wp theme activate kyrox
```

Tema ilk etkinleştiğinde:
- Ana sayfa ve Blog sayfalarını oluşturur.
- Kalıcı bağlantıları `/blog/%postname%/` yapar.
- Saat dilimini İstanbul yapar, yorumları kapatır.
- Örnek yazıyı ve dört iş kartını ekler.

Bunlar bir kez yapılır; var olan içeriğe dokunulmaz.

Tema klasörü kapsayıcıya salt okunur bağlanır; temada yaptığınız değişiklik sayfayı yenileyince görünür.

Statik tasarımı önizlemek için: `node tasarim/serve.mjs` → `http://localhost:4321`.

Stand V3 görüntüleyicisinin kaynağı `tasarim/stand-v3/viewer.js`, dağıtım dosyası `tema/kyrox/assets/stand-v3/viewer.js` içindedir. Yeniden derleme: `node tasarim/scripts/build-viewer.mjs`; kurulu Fair Stand bağımlılıkları kullanılır, farklı konum için `FAIR_STAND_ROOT` verilir. Sunucuya Node veya proje kataloğu gerekmez. Model kaynakları ve lisanslar tanıtım sayfasındaki bağlantıda tutulur. Ham proje/katalog dışa aktarımı ve erişim bilgileri dağıtım paketine konmaz.

## Kontroller

CI her PR'da:
- temadaki PHP dosyalarını PHP 8.4 ile denetler (`tema/lint.sh`),
- Docker Compose dosyasını doğrular,
- statik tasarımı derleyip kontrol eder.

Yerelde PHP denetimi için:

```bash
docker run --rm -v "$PWD/tema:/t:ro" php:8.4-cli sh /t/lint.sh /t/kyrox
```

## Sunucuya kurulum

Repo sunucuda Kyrox projelerinin yanında durur: `/opt/kyrox/kyrox-web`.

```bash
sudo git clone https://github.com/kyrox-studio/kyrox-web.git /opt/kyrox/kyrox-web
sudo bash /opt/kyrox/kyrox-web/sunucu/kur.sh
```

Bağlanırken `ssh -t` kullanın; sihirbaz soru sorar. Her soruda köşeli parantezdeki değer varsayılandır, kabul etmek için Enter'a basın.

| Adım | Ne olur | Ne yazarsınız |
|---|---|---|
| 1. Ön kontroller | docker, nginx, bellek, 8090 portu; GitHub'da yeni değişiklik varsa listeler ve çekilsin mi diye sorar (`git pull`, yalnız ileri sarma; sunucuda kaydedilmemiş değişiklik varsa dokunmaz) | `e` |
| 2. Alan adı ve e-posta | `[kyrox.studio]`, `[hello@kyrox.studio]` | Enter, Enter |
| 3. Ayar dosyası | `sunucu/.env` yoksa rastgele veritabanı şifreleri ve WordPress anahtarlarıyla oluşturulur | — |
| 4. Kapsayıcılar | WordPress + MariaDB başlar, yalnız `127.0.0.1:8090` | — |
| 5. Yönetici hesabı | Site adı, kullanıcı adı (`admin` olamaz), **şifre** (en az 12 karakter, ekranda görünmez, iki kez) | Enter, Enter, şifre |
| 6. Form e-postası | İş fişi formundan gelen talepler e-postayla da bildirilsin mi; evet derseniz SMTP sunucusu (`mail.kurumsaleposta.com`), port (`465`), kullanıcı ve **e-posta şifresi** (ekranda görünmez); sonunda deneme e-postası | `e`, Enter, Enter, Enter, şifre, `e` |
| 7. nginx | `kyrox.studio` ve `www` için site; `www` çıplak alan adına yönlenir; giriş sayfasına hız sınırı; XML-RPC kapalı | — |
| 8. SSL | DNS sunucuyu gösteriyorsa Let's Encrypt kullanım koşullarını kabul edip etmediğinizi sorar; kabul ederseniz sertifika alınır ve site `https`'e geçer | `e` |

Sihirbaz tekrar çalıştırılabilir: kurulu WordPress'e, yönetici hesabına, yazılmış nginx sitesine ve alınmış sertifikaya dokunmaz. DNS sonradan yayıldıysa tekrar çalıştırmak yalnız SSL'i ekler.

**Güncelleme:** Siteyi GitHub'daki son hale getirmek için sihirbazı tekrar çalıştırın; ilk adımda değişiklikleri çeker. Sihirbazın kendisi değiştiyse yeni sürümle baştan başlar. Tema dosyaları kapsayıcıya doğrudan bağlı olduğu için çekilen tema değişiklikleri hemen yayına girer.

```bash
sudo bash /opt/kyrox/kyrox-web/sunucu/kur.sh
```

Mevcut nginx siteleri değişmez; yeni site `/etc/nginx/sites-available/kyrox-studio` dosyasıdır ve `nginx -t` geçmezse nginx yeniden yüklenmez.

**Yedek:** WordPress veritabanı ve dosyaları gece yedeğine, yedek sihirbazı tekrar çalıştırılınca eklenir (sihirbaz MariaDB kapsayıcısını ve WordPress dosyalarını kendisi bulur):

```bash
sudo bash /opt/kyrox/kyrox-fair-crm/scripts/server/backup/install-backup.sh
```

**İletişim formu:** Ana sayfadaki iş fişi "Gönder"e basılınca talebi arka planda sunucuya yollar (`/wp-json/kyrox/v1/talep`). Sunucu talebi panelde **Gelen talepler** altına kaydeder ve SMTP ayarlıysa Görünüm > Özelleştir > İletişim bilgileri'ndeki e-posta adresine bildirir. E-posta gitmese bile talep panelde kalır; listede "E-posta" sütunu gönderilip gönderilmediğini, gönderilemediyse sebebini gösterir. Bot koruması: görünmez tuzak alan ve aynı IP'den 10 dakikada en fazla 5 talep.

`.env` repoya girmez; içindeki şifreler yalnız sunucuda durur.
