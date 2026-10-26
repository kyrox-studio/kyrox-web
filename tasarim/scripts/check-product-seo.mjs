// Statik önizlemede veya --live ile yayımdaki ürün metalarını doğrular.
import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'../..');
const live=process.argv.includes('--live');
const products=JSON.parse(fs.readFileSync(path.join(root,'tema/kyrox/inc/baslangic/urunler.json'),'utf8'));
const decode=value=>value.replaceAll('&amp;','&').replaceAll('&#038;','&').replaceAll('&quot;','"').replaceAll('&#039;',"'");
function attrs(tag){return Object.fromEntries([...tag.matchAll(/([\w:-]+)=["']([^"']*)["']/g)].map(m=>[m[1],decode(m[2])]));}
for(const product of products){
 const canonical=`https://kyrox.studio/${product.adres}/`;
 let html;
 if(live){const response=await fetch(canonical+'?utm_source=seo-kontrol',{signal:AbortSignal.timeout(15000)});if(response.status!==200)throw new Error(`${product.adres}: HTTP ${response.status}`);html=await response.text();}
 else html=fs.readFileSync(path.join(root,'tasarim/dist',product.adres,'index.html'),'utf8');
 const metas=[...html.matchAll(/<meta\b[^>]*>/g)].map(m=>attrs(m[0]));
 const meta=name=>metas.filter(m=>m.name===name||m.property===name);
 const value=name=>{const values=meta(name);if(values.length!==1||!values[0].content)throw new Error(`${product.adres}: ${name} eksik/tekrarlı`);return values[0].content;};
 const title=decode(html.match(/<title>([^<]+)<\/title>/)?.[1]||'');
 if(title!==product.seoBaslik||value('og:title')!==title||value('twitter:title')!==title)throw new Error('Başlıklar tutarsız: '+product.adres);
 if(value('description')!==value('og:description')||value('description')!==value('twitter:description'))throw new Error('Açıklamalar tutarsız');
 const links=[...html.matchAll(/<link\b[^>]*>/g)].map(m=>attrs(m[0])).filter(m=>m.rel==='canonical');
 if(links.length!==1||links[0].href!==canonical||value('og:url')!==canonical)throw new Error('Canonical/UTM adresi hatalı');
 if(value('og:image')!==value('twitter:image')||value('og:image:alt')!==product.paylasimAlt||value('twitter:image:alt')!==product.paylasimAlt)throw new Error('Görsel metaları tutarsız');
 if(value('og:image:type')!=='image/png'||value('og:image:width')!=='1200'||value('og:image:height')!=='630')throw new Error('Görsel türü/ölçüsü hatalı');
 const graphs=[...html.matchAll(/<script\b[^>]*type=["']application\/ld\+json["'][^>]*>([\s\S]*?)<\/script>/g)].flatMap(m=>JSON.parse(m[1])['@graph']||[]);
 if(!graphs.some(n=>n['@type']==='WebPage'&&n.url===canonical)||!graphs.some(n=>n['@type']==='BreadcrumbList'&&n.itemListElement.at(-1).item===canonical))throw new Error('Sayfa/yol şeması eksik');
 if(meta('robots').some(m=>/noindex/i.test(m.content)))throw new Error('Tanıtım sayfası noindex');
 let png;
 if(live){const r=await fetch(value('og:image'));if(!r.ok||!r.headers.get('content-type')?.includes('image/png'))throw new Error('Paylaşım görseli sunulamıyor');png=Buffer.from(await r.arrayBuffer());}
 else png=fs.readFileSync(path.join(root,'tema/kyrox/assets',product.paylasimGorseli));
 if(png.readUInt32BE(16)!==1200||png.readUInt32BE(20)!==630)throw new Error('Gerçek PNG ölçüsü hatalı');
 console.log(`${product.adres}: başlık/açıklama, UTM-canonical, OG/Twitter, PNG ve JSON-LD başarılı (${live?'canlı':'statik'}).`);
}
if(live){for(const endpoint of ['wp-sitemap-posts-page-1.xml','llms.txt']){const r=await fetch('https://kyrox.studio/'+endpoint);const body=await r.text();if(!r.ok||products.some(p=>!body.includes('/'+p.adres+'/')))throw new Error(endpoint+' ürün adreslerini içermiyor');}console.log('Canlı sitemap ve llms.txt iki ürünü de içeriyor.');}
