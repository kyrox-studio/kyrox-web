// Siteyi bilgisayarda önizlemek için: `node serve.mjs` → http://localhost:4321
// Önce derler, sonra dist/ klasörünü yayınlar. Dosya değiştirince sayfayı yenilemeden önce yeniden çalıştırın.
import { createServer } from 'node:http';
import { readFile, stat } from 'node:fs/promises';
import { join, extname, normalize } from 'node:path';
import { fileURLToPath } from 'node:url';
import { derle } from './build.mjs';

const DIST = join(fileURLToPath(new URL('.', import.meta.url)), 'dist');
const TUR = { '.html': 'text/html; charset=utf-8', '.css': 'text/css; charset=utf-8', '.js': 'text/javascript; charset=utf-8', '.svg': 'image/svg+xml', '.png': 'image/png', '.jpg': 'image/jpeg', '.jpeg': 'image/jpeg', '.webp': 'image/webp', '.ico': 'image/x-icon', '.xml': 'application/xml; charset=utf-8', '.txt': 'text/plain; charset=utf-8', '.json': 'application/json' };
const PORT = Number(process.env.PORT) || 4321;

derle();
createServer(async (req, res) => {
  let yol = decodeURIComponent(new URL(req.url, 'http://x').pathname);
  if (yol.endsWith('/')) yol += 'index.html';
  const dosya = normalize(join(DIST, yol));
  if (!dosya.startsWith(DIST)) { res.writeHead(403).end(); return; }
  try {
    const s = await stat(dosya);
    const gercek = s.isDirectory() ? join(dosya, 'index.html') : dosya;
    res.writeHead(200, { 'content-type': TUR[extname(gercek)] || 'application/octet-stream' });
    res.end(await readFile(gercek));
  } catch {
    res.writeHead(404, { 'content-type': TUR['.html'] });
    res.end(await readFile(join(DIST, '404.html')));
  }
}).listen(PORT, () => console.log(`Önizleme: http://localhost:${PORT}`));
