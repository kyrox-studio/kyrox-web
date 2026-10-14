// Derlenmiş siteyi kontrol eder: `node build.mjs && node scripts/check.mjs`
// Bozuk iç bağlantı, okunamayan kimlik bilgisi (JSON-LD), doldurulmamış şablon alanı veya eksik dosya varsa hata verir.
import { readFileSync, existsSync, readdirSync, statSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const DIST = join(dirname(fileURLToPath(import.meta.url)), '..', 'dist');
const hatalar = [];

function dosyalar(klasor) {
  return readdirSync(klasor).flatMap((ad) => {
    const yol = join(klasor, ad);
    return statSync(yol).isDirectory() ? dosyalar(yol) : [yol];
  });
}

for (const gerekli of ['index.html', 'blog/index.html', '404.html', 'sitemap.xml', 'robots.txt', 'llms.txt', 'blog/rss.xml', 'og-image.png', 'favicon.svg', 'favicon.ico']) {
  if (!existsSync(join(DIST, gerekli))) hatalar.push(`eksik dosya: ${gerekli}`);
}

const htmller = dosyalar(DIST).filter((f) => f.endsWith('.html'));
for (const f of htmller) {
  const ad = f.slice(DIST.length);
  const s = readFileSync(f, 'utf8');
  if (/\{\{\w+\}\}/.test(s)) hatalar.push(`${ad}: doldurulmamış şablon alanı`);
  const ld = s.match(/<script type="application\/ld\+json">([\s\S]*?)<\/script>/);
  if (!ld) hatalar.push(`${ad}: JSON-LD yok`);
  else {
    try { JSON.parse(ld[1]); } catch (e) { hatalar.push(`${ad}: JSON-LD okunamıyor (${e.message})`); }
  }
  if (!/<title>[^<]+<\/title>/.test(s)) hatalar.push(`${ad}: başlık yok`);
  if (!/<meta name="description" content="[^"]+">/.test(s)) hatalar.push(`${ad}: açıklama yok`);
  for (const [, href] of s.matchAll(/(?:href|src)="(\/[^"#?]*)/g)) {
    let hedef = join(DIST, href);
    if (href.endsWith('/')) hedef = join(hedef, 'index.html');
    if (!existsSync(hedef)) hatalar.push(`${ad}: kırık bağlantı ${href}`);
  }
}

const harita = readFileSync(join(DIST, 'sitemap.xml'), 'utf8');
for (const [, loc] of harita.matchAll(/<loc>https:\/\/kyrox\.studio(\/[^<]*)<\/loc>/g)) {
  if (!existsSync(join(DIST, loc, loc.endsWith('/') ? 'index.html' : ''))) hatalar.push(`sitemap: sayfa yok ${loc}`);
}

if (hatalar.length) {
  console.error(hatalar.map((h) => `HATA ${h}`).join('\n'));
  process.exit(1);
}
console.log(`Kontrol geçti: ${htmller.length} sayfa, bağlantılar, JSON-LD ve sitemap tamam.`);
