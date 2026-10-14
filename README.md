# kyrox.studio web sitesi

Kyrox Studio'nun web sitesi: ana sayfa, blog, arama motorları ve yapay zekâ asistanları için kimlik dosyaları.

Karar kaydı: [ADR-0011](https://github.com/kyrox-studio/kyrox-platform/blob/main/ecosystem/decisions/0011-web-site-repository.md). Marka kuralları [kyrox-platform/standards/brand](https://github.com/kyrox-studio/kyrox-platform/blob/main/standards/brand/BRAND_STANDARD.md) altında; logo dosyaları [kyrox-brand](https://github.com/kyrox-studio/kyrox-brand)'den kopyalanır, burada elle düzenlenmez.

Hiçbir paket kurmanız gerekmez. Bilgisayarda Node.js (18 veya üstü) olması yeterli.

## Bilgisayarda önizleme

```bash
node serve.mjs
```

Tarayıcıda `http://localhost:4321` adresini açın. Bir dosyayı değiştirdikten sonra komutu durdurup (Ctrl+C) yeniden çalıştırın.

HTML dosyalarını çift tıklayarak açmayın; bağlantılar ve görseller ancak bu önizleme sunucusuyla doğru çalışır.

## Blog yazısı eklemek

1. `src/blog/` klasörüne yeni bir `.md` dosyası açın. Dosya adı tarih ile başlasın: `2026-11-02-cnc-ile-ahsap-rakle.md`
2. En üste bu bilgi bloğunu yazın:

   ```
   ---
   baslik: Yazının başlığı
   ozet: Bir iki cümlelik özet. Google'da ve WhatsApp önizlemesinde bu görünür.
   tarih: 2026-11-02
   etiketler: [CNC, Ahşap]
   ---
   ```

3. Altına yazıyı yazın. Kullanabileceğiniz biçimler:
   - `## Ara başlık` ve `### Küçük başlık`
   - `**kalın**`, `*italik*`, `[bağlantı](https://...)`
   - `- madde` ve `1. sıralı madde`
   - `> alıntı`
   - `![açıklama](/blog-gorsel/dosya.jpg)` (görseli `public/blog-gorsel/` klasörüne koyun)
   - Tablo: `| Başlık | Başlık |` satırları
4. Yayına hazır değilse bilgi bloğuna `taslak: true` ekleyin; site o yazıyı atlar.

Yazının adresi başlıktan otomatik oluşur (`/blog/yazinin-basligi/`). İsterseniz `adres: kisa-adres` ile kendiniz belirleyin.

Yeni yazı eklenince ana sayfadaki "Atölyeden notlar" bölümü, `sitemap.xml`, `blog/rss.xml` ve `llms.txt` kendiliğinden güncellenir.

## Bilgileri değiştirmek

Adres, telefon, e-posta, hizmetler ve ürünler `site.json` dosyasında. Burayı değiştirince sitedeki kimlik bilgisi (Google ve yapay zekâlar için), `llms.txt` ve `robots.txt` birlikte güncellenir.

Ana sayfadaki yazılar ve bölümler `src/pages/` altında:

- `anasayfa-koyu.html`: tamamen koyu sürüm
- `anasayfa-krem.html`: siyah-krem sürüm

Hangisinin yayında olacağını `site.json` içindeki `"anasayfa": "koyu"` satırı belirler (`"koyu"` ya da `"krem"`).

Üst menü `src/partials/header.html`, alt bilgi `src/partials/footer.html` dosyasında; ana sayfa ve blog aynı dosyaları kullanır.

## Klasörler

```
site.json            Kurum bilgileri (tek kaynak)
build.mjs            Siteyi dist/ klasörüne derler
serve.mjs            Bilgisayarda önizleme
scripts/check.mjs    Derlenmiş siteyi kontrol eder (kırık bağlantı, kimlik bilgisi, sitemap)
src/pages/           Ana sayfa (koyu ve krem)
src/partials/        Üst menü ve alt bilgi
src/templates/       Sayfa iskeleti, blog listesi, blog yazısı
src/blog/            Blog yazıları (.md)
public/              Olduğu gibi kopyalanan dosyalar: stiller, logo, favicon, paylaşım görseli
dist/                Derlenmiş site (git'e girmez)
.github/workflows/   Her PR'da siteyi derleyip kontrol eden CI
GORSEL_KOMUTLARI.md  Yapay zekâ ile görsel üretmek için hazır komutlar
```

## Derleme neler üretir

| Dosya | Ne işe yarar |
|---|---|
| `index.html` | Ana sayfa |
| `blog/` ve `blog/<yazi>/` | Blog listesi ve yazılar |
| `sitemap.xml` | Google ve Bing'e sayfa listesi |
| `blog/rss.xml` | Blog akışı |
| `robots.txt` | Arama motorları ve yapay zekâ tarayıcılarına (GPTBot, ClaudeBot, PerplexityBot, Google-Extended…) açık izin |
| `llms.txt` | Yapay zekâ asistanları için sitenin kısa özeti |
| `404.html` | Bulunamayan sayfalar |

Her sayfanın başında Google ve yapay zekâların okuduğu kimlik bilgisi (schema.org JSON-LD) var: atölyenin adı, adresi, telefonu, hizmetleri; blog yazılarında yazı bilgisi.

## Yayına alma (Cloudflare Pages)

1. Cloudflare hesabında **Workers & Pages → Create → Pages → Connect to Git** ile bu repoyu seçin.
2. Ayarlar:
   - Root directory: boş bırakın (repo kökü)
   - Build command: `node build.mjs`
   - Build output directory: `dist`
3. **Custom domains** bölümünden `kyrox.studio` alan adını bağlayın.

Her `main` güncellemesinde site kendiliğinden yeniden yayınlanır.

## Yayından sonra yapılacaklar

- **Google Search Console:** siteyi ekleyin, `https://kyrox.studio/sitemap.xml` adresini gönderin.
- **Bing Webmaster Tools:** aynı şekilde ekleyin. ChatGPT'nin web aramaları Bing dizinini kullanır.
- **Google Business Profile:** atölyeyi haritaya ekleyin. Adres, telefon ve web sitesi `site.json` ile birebir aynı yazılmalı.
- Instagram, LinkedIn ve diğer hesaplar açılınca adreslerini `site.json` içindeki `"sosyal"` listesine ekleyin.
