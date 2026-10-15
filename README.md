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
- **Görünüm > Özelleştir > Kyrox Studio:**
  - koyu / krem görünüm
  - açılış, giriş, atölye kartları, işler, blog ve iş fişi bölümlerinin metinleri
  - iletişim bilgileri (e-posta, WhatsApp, adres)
  - Google ve yapay zekâ için başlık, açıklama ve kurum tanıtımı

Varsayılan metinler onaylı tasarımdakilerdir; bir alan boş bırakılırsa varsayılana döner.

## Arama motorları ve yapay zekâ

Tema şunları kendisi üretir:
- her sayfada açıklama, canonical ve Open Graph etiketleri
- JSON-LD: `LocalBusiness`, `WebSite`; yazılarda ayrıca `BlogPosting` ve `BreadcrumbList`
- `robots.txt`: yapay zekâ tarayıcıları açıkça davetli
- `/llms.txt`: kurum, hizmetler, ürünler ve yazıların özeti
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

## Kontroller

CI her PR'da:
- temadaki PHP dosyalarını PHP 8.4 ile denetler (`tema/lint.sh`),
- Docker Compose dosyasını doğrular,
- statik tasarımı derleyip kontrol eder.

Yerelde PHP denetimi için:

```bash
docker run --rm -v "$PWD/tema:/t:ro" php:8.4-cli sh /t/lint.sh /t/kyrox
```

## Sunucu

Sunucuda WordPress ve MariaDB yalnız `127.0.0.1:8090`'a açılır; `kyrox.studio` için nginx bloğu, SSL ve yedeğe ekleme ayrı bir adımda kurulur. `.env` repoya girmez.
