// Kyrox Studio site derleyicisi. Bağımlılık yok: `node build.mjs` çalıştırınca dist/ klasörü oluşur.
// Ana sayfa src/pages'ten, blog yazıları src/blog/*.md dosyalarından üretilir.
import { readFileSync, writeFileSync, mkdirSync, readdirSync, rmSync, cpSync, existsSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const KOK = dirname(fileURLToPath(import.meta.url));
const DIST = join(KOK, 'dist');
const oku = (p) => readFileSync(join(KOK, p), 'utf8');
const site = JSON.parse(oku('site.json'));
const urunler = JSON.parse(oku('../tema/kyrox/inc/baslangic/urunler.json'));
const URL_KOK = site.url.replace(/\/$/, '');

// ---------- yardımcılar ----------
export const kacis = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
const TR = { ç: 'c', ğ: 'g', ı: 'i', i: 'i', ö: 'o', ş: 's', ü: 'u', â: 'a', î: 'i', û: 'u' };
export function slug(s) {
  return String(s).replace(/İ/g, 'i').replace(/I/g, 'ı').toLowerCase()
    .replace(/[çğıöşüâîû]/g, (c) => TR[c] || c)
    .normalize('NFKD').replace(/[̀-ͯ]/g, '')
    .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
}
function doldur(sablon, veri) {
  return sablon.replace(/\{\{(\w+)\}\}/g, (_, k) => {
    if (!(k in veri)) throw new Error(`Şablonda doldurulmamış alan: {{${k}}}`);
    return veri[k];
  });
}
const AYLAR = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
function tarihTr(iso) {
  const [y, m, d] = iso.split('-').map(Number);
  return `${d} ${AYLAR[m - 1]} ${y}`;
}

// ---------- markdown ----------
function satirIci(s) {
  const kodlar = [];
  s = s.replace(/`([^`]+)`/g, (_, k) => { kodlar.push(k); return `\u0000${kodlar.length - 1}\u0000`; });
  s = kacis(s);
  s = s.replace(/!\[([^\]]*)\]\(([^)\s]+)\)/g, (_, alt, src) => `<img src="${src}" alt="${alt}" loading="lazy">`);
  s = s.replace(/\[([^\]]+)\]\(([^)\s]+)\)/g, (_, t, href) => {
    const dis = /^https?:\/\//.test(href) && !href.startsWith(URL_KOK);
    return `<a href="${href}"${dis ? ' target="_blank" rel="noopener noreferrer"' : ''}>${t}</a>`;
  });
  s = s.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
  s = s.replace(/(^|[^*])\*([^*\s][^*]*)\*/g, '$1<em>$2</em>');
  s = s.replace(/\u0000(\d+)\u0000/g, (_, i) => `<code>${kacis(kodlar[+i])}</code>`);
  return s;
}

export function markdown(md) {
  const satirlar = md.replace(/\r\n?/g, '\n').split('\n');
  const cikti = [];
  const basliklar = [];
  let i = 0;
  const bos = (l) => /^\s*$/.test(l);
  const blokBasi = (l) => /^(#{1,4}\s|```|>\s?|[-*]\s|\d+\.\s|---\s*$|\|)/.test(l);
  while (i < satirlar.length) {
    const l = satirlar[i];
    if (bos(l)) { i++; continue; }
    let m;
    if ((m = l.match(/^(#{1,4})\s+(.*)$/))) {
      const seviye = Math.max(2, m[1].length); // h1 sayfa başlığına ayrılmış
      const id = slug(m[2]);
      basliklar.push({ seviye, metin: m[2], id });
      cikti.push(`<h${seviye} id="${id}">${satirIci(m[2])}</h${seviye}>`);
      i++; continue;
    }
    if (l.startsWith('```')) {
      const dil = l.slice(3).trim();
      const kod = [];
      i++;
      while (i < satirlar.length && !satirlar[i].startsWith('```')) kod.push(satirlar[i++]);
      i++;
      cikti.push(`<pre><code${dil ? ` class="language-${kacis(dil)}"` : ''}>${kacis(kod.join('\n'))}</code></pre>`);
      continue;
    }
    if (/^---\s*$/.test(l)) { cikti.push('<hr>'); i++; continue; }
    if (/^>\s?/.test(l)) {
      const parca = [];
      while (i < satirlar.length && /^>\s?/.test(satirlar[i])) parca.push(satirlar[i++].replace(/^>\s?/, ''));
      cikti.push(`<blockquote><p>${satirIci(parca.join(' '))}</p></blockquote>`);
      continue;
    }
    if (/^[-*]\s/.test(l) || /^\d+\.\s/.test(l)) {
      const sirali = /^\d+\.\s/.test(l);
      const re = sirali ? /^\d+\.\s+/ : /^[-*]\s+/;
      const maddeler = [];
      while (i < satirlar.length && re.test(satirlar[i])) {
        let madde = satirlar[i++].replace(re, '');
        while (i < satirlar.length && /^\s{2,}\S/.test(satirlar[i])) madde += ' ' + satirlar[i++].trim();
        maddeler.push(`<li>${satirIci(madde)}</li>`);
      }
      cikti.push(`<${sirali ? 'ol' : 'ul'}>${maddeler.join('')}</${sirali ? 'ol' : 'ul'}>`);
      continue;
    }
    if (/^\|/.test(l)) {
      const satir = [];
      while (i < satirlar.length && /^\|/.test(satirlar[i])) satir.push(satirlar[i++]);
      const hucre = (r) => r.replace(/^\||\|\s*$/g, '').split('|').map((c) => c.trim());
      const govde = satir.filter((r, k) => !(k === 1 && /^\|[\s:|-]+\|?\s*$/.test(r)));
      const [bas, ...diger] = govde;
      cikti.push('<table><thead><tr>' + hucre(bas).map((c) => `<th>${satirIci(c)}</th>`).join('') + '</tr></thead><tbody>' +
        diger.map((r) => '<tr>' + hucre(r).map((c) => `<td>${satirIci(c)}</td>`).join('') + '</tr>').join('') + '</tbody></table>');
      continue;
    }
    const p = [];
    while (i < satirlar.length && !bos(satirlar[i]) && !blokBasi(satirlar[i])) p.push(satirlar[i++].trim());
    if (!p.length) { p.push(satirlar[i++].trim()); }
    cikti.push(`<p>${satirIci(p.join(' '))}</p>`);
  }
  return { html: cikti.join('\n'), basliklar };
}

export function onBilgi(metin) {
  const m = metin.replace(/\r\n?/g, '\n').match(/^---\n([\s\S]*?)\n---\n?([\s\S]*)$/);
  if (!m) throw new Error('Yazının başında --- ile çevrili bilgi bloğu yok');
  const veri = {};
  for (const satir of m[1].split('\n')) {
    const k = satir.match(/^(\w+):\s*(.*)$/);
    if (!k) continue;
    let v = k[2].trim();
    if (/^".*"$/.test(v) || /^'.*'$/.test(v)) v = v.slice(1, -1);
    if (/^\[.*\]$/.test(v)) v = v.slice(1, -1).split(',').map((x) => x.trim().replace(/^["']|["']$/g, '')).filter(Boolean);
    if (v === 'true') v = true;
    if (v === 'false') v = false;
    veri[k[1]] = v;
  }
  return { veri, govde: m[2] };
}

// ---------- kimlik (yapay zekâ ve arama motorları için) ----------
function kurum() {
  const a = site.adres;
  return {
    '@type': 'LocalBusiness',
    '@id': `${URL_KOK}/#kurum`,
    name: site.ad,
    url: `${URL_KOK}/`,
    logo: `${URL_KOK}/favicon.svg`,
    image: `${URL_KOK}/og-image.png`,
    description: site.uzunAciklama,
    slogan: site.slogan,
    email: site.eposta,
    telephone: site.telefon,
    address: { '@type': 'PostalAddress', streetAddress: a.sokak, addressLocality: a.ilce, addressRegion: a.il, addressCountry: a.ulke },
    areaServed: [{ '@type': 'City', name: 'İstanbul' }, { '@type': 'Country', name: 'Türkiye' }],
    knowsAbout: site.hizmetler.map((h) => h.ad),
    makesOffer: site.hizmetler.map((h) => ({ '@type': 'Offer', itemOffered: { '@type': 'Service', name: h.ad, description: h.aciklama } })),
    ...(site.sosyal.length ? { sameAs: site.sosyal } : {}),
  };
}
const jsonld = (graf) => JSON.stringify({ '@context': 'https://schema.org', '@graph': graf }).replace(/</g, '\\u003c');

// ---------- sayfa ----------
const layout = oku('src/templates/layout.html');
const header = oku('src/partials/header.html').trim();
const footer = oku('src/partials/footer.html').trim();
function sayfa({ baslik, aciklama, yol, icerik, graf, ogTur = 'website', ogGorsel = '/og-image.png', ogGorselAlt = 'Kyrox Studio — Fikirden üretime', ekHead = '', scriptler = '' }) {
  return doldur(layout, {
    baslik: kacis(baslik), aciklama: kacis(aciklama), canonical: `${URL_KOK}${yol}`,
    ogTur, ogGorsel: `${URL_KOK}${ogGorsel}`, ogGorselAlt: kacis(ogGorselAlt), ekHead, jsonld: jsonld(graf),
    header, icerik, footer, scriptler,
  });
}
function yaz(yol, icerik) {
  const p = join(DIST, yol);
  mkdirSync(dirname(p), { recursive: true });
  writeFileSync(p, icerik);
}

// ---------- blog yazılarını oku ----------
export function yazilariOku() {
  const klasor = join(KOK, 'src/blog');
  const yazilar = [];
  for (const dosya of readdirSync(klasor).filter((f) => f.endsWith('.md')).sort()) {
    const { veri, govde } = onBilgi(readFileSync(join(klasor, dosya), 'utf8'));
    for (const zorunlu of ['baslik', 'ozet', 'tarih']) {
      if (!veri[zorunlu]) throw new Error(`${dosya}: "${zorunlu}" alanı eksik`);
    }
    if (!/^\d{4}-\d{2}-\d{2}$/.test(veri.tarih)) throw new Error(`${dosya}: tarih YYYY-AA-GG biçiminde olmalı`);
    if (veri.taslak === true) continue;
    const { html, basliklar } = markdown(govde);
    const kelime = govde.split(/\s+/).filter(Boolean).length;
    yazilar.push({
      ...veri,
      adres: veri.adres ? slug(veri.adres) : slug(veri.baslik),
      etiketler: Array.isArray(veri.etiketler) ? veri.etiketler : veri.etiketler ? [veri.etiketler] : [],
      html, basliklar, okumaSuresi: Math.max(1, Math.round(kelime / 200)),
    });
  }
  const goruldu = new Set();
  for (const y of yazilar) {
    if (goruldu.has(y.adres)) throw new Error(`Aynı adrese sahip iki yazı var: ${y.adres}`);
    goruldu.add(y.adres);
  }
  return yazilar.sort((a, b) => b.tarih.localeCompare(a.tarih));
}

const yaziSatiri = (y) => `<li><a class="post-row" href="/blog/${y.adres}/"><time datetime="${y.tarih}">${tarihTr(y.tarih)}</time><span><h2>${kacis(y.baslik)}</h2><p>${kacis(y.ozet)}</p></span><span class="go">Oku →</span></a></li>`;

// ---------- derle ----------
function derle() {
  const yazilar = yazilariOku();
  rmSync(DIST, { recursive: true, force: true });
  mkdirSync(DIST, { recursive: true });
  cpSync(join(KOK, 'public'), DIST, { recursive: true });
  cpSync(join(KOK, '../tema/kyrox/assets/stand-v3'), join(DIST, 'assets/stand-v3'), {recursive:true});
  cpSync(join(KOK, '../tema/kyrox/assets/social'), join(DIST, 'assets/social'), {recursive:true});
  cpSync(join(KOK, '../tema/kyrox/assets/fonts'), join(DIST, 'assets/fonts'), {recursive:true});
  mkdirSync(join(DIST, 'assets/css'), {recursive:true});
  cpSync(join(KOK, '../tema/kyrox/assets/css/fonts.css'), join(DIST, 'assets/css/fonts.css'));

  const websitesi = { '@type': 'WebSite', '@id': `${URL_KOK}/#site`, url: `${URL_KOK}/`, name: site.ad, inLanguage: 'tr-TR', publisher: { '@id': `${URL_KOK}/#kurum` } };
  const kurumBilgisi = kurum();

  // ana sayfa
  const son = yazilar.slice(0, 3);
  const sonYazilar = son.length
    ? `<section class="wrap home-posts" id="blog"><div class="sectionhead"><div><span class="eyebrow">Blog</span><h2>Atölyeden<br>notlar.</h2></div><p>Denediklerimiz, öğrendiklerimiz.</p></div><ol class="post-list">${son.map(yaziSatiri).join('')}</ol><a class="all" href="/blog/">Tüm yazılar →</a></section>`
    : '';
  const sssKayitlari = JSON.parse(oku('../tema/kyrox/inc/baslangic/sss.json'));
  const sss = `<section class="faq wrap" id="sss" aria-labelledby="sss-baslik"><div class="sectionhead"><div><span class="eyebrow">Merak edilenler</span><h2 id="sss-baslik">Sık sorulan sorular</h2></div><p>Fuar CRM ve Stand Konfiguratör hakkında kısa cevaplar.</p></div><div class="faq-list">${sssKayitlari.map(({soru, cevap}) => `<details class="faq-item"><summary>${kacis(soru)}</summary><p>${kacis(cevap)}</p></details>`).join('')}</div><a class="all" href="#iletisim">Başka bir sorunuz mu var? Bize yazın →</a></section>`;
  const sssSemasi = { '@type': 'FAQPage', '@id': `${URL_KOK}/#sss`, mainEntity: sssKayitlari.map(({soru, cevap}) => ({ '@type': 'Question', name: soru, acceptedAnswer: { '@type': 'Answer', text: cevap } })) };
  const anasayfa = oku(`src/pages/anasayfa-${site.anasayfa}.html`).replace('{{sonYazilar}}', sonYazilar).replace('{{sss}}', sss);
  yaz('index.html', sayfa({
    baslik: site.baslik, aciklama: site.aciklama, yol: '/', icerik: anasayfa,
    graf: [kurumBilgisi, websitesi, sssSemasi],
    ekHead: '<link rel="stylesheet" href="/assets/blog.css">',
    scriptler: '<script src="/assets/anasayfa.js" defer></script>',
  }));

  const urunSemasi = (urun) => {
    const adres = URL_KOK + '/' + urun.adres + '/';
    return [kurumBilgisi, websitesi,
      {'@type':'WebPage','@id':adres+'#sayfa',url:adres,name:urun.seoBaslik,description:urun.aciklama,inLanguage:'tr-TR',isPartOf:{'@id':URL_KOK+'/#site'},about:{'@type':'Thing',name:urun.baslik},primaryImageOfPage:{'@type':'ImageObject',url:URL_KOK+'/assets/'+urun.paylasimGorseli,width:1200,height:630},breadcrumb:{'@id':adres+'#yol'}},
      {'@type':'BreadcrumbList','@id':adres+'#yol',itemListElement:[{'@type':'ListItem',position:1,name:'Ana sayfa',item:URL_KOK+'/'},{'@type':'ListItem',position:2,name:urun.baslik,item:adres}]}
    ];
  };
  // Herkese açık ürün tanıtımları; WordPress başlangıç içeriğiyle aynı kaynak.
  for (const urun of urunler) {
    if (urun.bagimsiz) {
      const icerik = oku('../tema/kyrox/inc/baslangic/' + urun.icerik).replaceAll('{{STAND_ASSETS}}','/assets/stand-v3');
      const adres = `${URL_KOK}/${urun.adres}/`;
      const graf = urunSemasi(urun);
      yaz(`${urun.adres}/index.html`, `<!doctype html><html lang="tr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Kyrox Studio | Stand Konfiguratör</title><meta name="description" content="${kacis(urun.aciklama)}"><link rel="canonical" href="${adres}"><link rel="icon" href="/favicon.svg"><meta property="og:type" content="website"><meta property="og:title" content="Kyrox Studio | Stand Konfiguratör"><meta property="og:description" content="${kacis(urun.aciklama)}"><meta property="og:url" content="${adres}"><meta property="og:image" content="${URL_KOK}/assets/stand-v3/og-image.png"><meta property="og:image:width" content="1200"><meta property="og:image:height" content="630"><meta property="og:image:type" content="image/png"><meta property="og:image:alt" content="${kacis(urun.paylasimAlt)}"><meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="${kacis(urun.seoBaslik)}"><meta name="twitter:description" content="${kacis(urun.aciklama)}"><meta name="twitter:image" content="${URL_KOK}/assets/${urun.paylasimGorseli}"><meta name="twitter:image:alt" content="${kacis(urun.paylasimAlt)}"><link rel="stylesheet" href="/assets/css/fonts.css"><link rel="stylesheet" href="/assets/stand-v3/colors.css"><link rel="stylesheet" href="/assets/stand-v3/styles-v3.css"><script type="application/ld+json">${jsonld(graf)}</script></head><body class="stand-landing">${icerik}<script src="/assets/stand-v3/interactions-v3.js" defer></script></body></html>`);
      continue;
    }
    const icerik = `<main id="bas" tabindex="-1" class="product-page wrap"><header class="product-hero"><a class="all" href="/#projeler">← Tüm projeler</a><span class="eyebrow">${kacis(urun.etiket)}</span><h1>${kacis(urun.baslik)}</h1><p class="product-lead">${kacis(urun.aciklama)}</p><div class="product-actions"><a class="cta" href="${kacis(urun.uygulama)}" target="_blank" rel="noopener noreferrer">${kacis(urun.dugme)} ↗</a><a class="pill" href="/#iletisim">Bilgi alın →</a></div></header><div class="product-content">${oku('../tema/kyrox/inc/baslangic/' + urun.icerik)}</div></main>`;
    yaz(`${urun.adres}/index.html`, sayfa({
      baslik: urun.seoBaslik, aciklama: urun.aciklama, ogGorsel: `/assets/${urun.paylasimGorseli}`, ogGorselAlt: urun.paylasimAlt,
      yol: `/${urun.adres}/`, icerik, graf: urunSemasi(urun),
    }));
  }

  // blog listesi
  const liste = doldur(oku('src/templates/blog-liste.html'), {
    yazilar: yazilar.length ? yazilar.map(yaziSatiri).join('\n') : '<li class="post-empty">Henüz yazı yok.</li>',
  });
  yaz('blog/index.html', sayfa({
    baslik: `Blog — ${site.ad}`, aciklama: 'Yazılım, 3D baskı, ahşap ve üretim üzerine notlar.', yol: '/blog/', icerik: liste,
    graf: [kurumBilgisi, websitesi, { '@type': 'Blog', '@id': `${URL_KOK}/blog/#blog`, url: `${URL_KOK}/blog/`, name: `${site.ad} Blog`, inLanguage: 'tr-TR', publisher: { '@id': `${URL_KOK}/#kurum` } }],
    ekHead: '<link rel="stylesheet" href="/assets/blog.css">',
  }));

  // yazılar
  const sablon = oku('src/templates/blog-yazi.html');
  for (const y of yazilar) {
    const yol = `/blog/${y.adres}/`;
    const icerik = doldur(sablon, {
      tarih: y.tarih, tarihGorunen: tarihTr(y.tarih), okumaSuresi: String(y.okumaSuresi),
      etiketler: y.etiketler.map((e) => `<span class="ptag">${kacis(e)}</span>`).join(''),
      baslik: kacis(y.baslik), ozet: kacis(y.ozet), govde: y.html, whatsapp: site.whatsapp,
    });
    yaz(`blog/${y.adres}/index.html`, sayfa({
      baslik: `${y.baslik} — ${site.ad}`, aciklama: y.ozet, yol, icerik, ogTur: 'article',
      graf: [kurumBilgisi, websitesi, {
        '@type': 'BlogPosting', '@id': `${URL_KOK}${yol}#yazi`, headline: y.baslik, description: y.ozet,
        datePublished: y.tarih, dateModified: y.guncelleme || y.tarih, inLanguage: 'tr-TR',
        mainEntityOfPage: `${URL_KOK}${yol}`, image: `${URL_KOK}/og-image.png`,
        author: { '@id': `${URL_KOK}/#kurum` }, publisher: { '@id': `${URL_KOK}/#kurum` },
        ...(y.etiketler.length ? { keywords: y.etiketler.join(', ') } : {}),
      }, {
        '@type': 'BreadcrumbList', itemListElement: [
          { '@type': 'ListItem', position: 1, name: 'Ana sayfa', item: `${URL_KOK}/` },
          { '@type': 'ListItem', position: 2, name: 'Blog', item: `${URL_KOK}/blog/` },
          { '@type': 'ListItem', position: 3, name: y.baslik, item: `${URL_KOK}${yol}` },
        ],
      }],
      ekHead: '<link rel="stylesheet" href="/assets/blog.css">',
    }));
  }

  // 404
  yaz('404.html', sayfa({
    baslik: `Sayfa bulunamadı — ${site.ad}`, aciklama: 'Aradığınız sayfa burada değil.', yol: '/404.html',
    icerik: '<main id="bas" tabindex="-1" class="blog"><section class="wrap blog-head"><span class="eyebrow">404</span><h1>Bu sayfa<br><em>tezgâhta yok.</em></h1><p><a class="cta" href="/">Ana sayfaya dön</a></p></section></main>',
    graf: [kurumBilgisi, websitesi], ekHead: '<link rel="stylesheet" href="/assets/blog.css">',
  }));

  // sitemap
  const bugun = new Date().toISOString().slice(0, 10);
  const urller = [
    { loc: '/', lastmod: yazilar[0]?.tarih || bugun },
    { loc: '/blog/', lastmod: yazilar[0]?.tarih || bugun },
    ...urunler.map((urun) => ({ loc: `/${urun.adres}/`, lastmod: bugun })),
    ...yazilar.map((y) => ({ loc: `/blog/${y.adres}/`, lastmod: y.guncelleme || y.tarih })),
  ];
  yaz('sitemap.xml', `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${urller.map((u) => `  <url><loc>${URL_KOK}${u.loc}</loc><lastmod>${u.lastmod}</lastmod></url>`).join('\n')}\n</urlset>\n`);

  // rss
  const rssTarih = (iso) => new Date(`${iso}T09:00:00+03:00`).toUTCString();
  yaz('blog/rss.xml', `<?xml version="1.0" encoding="UTF-8"?>\n<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel><title>${kacis(site.ad)} Blog</title><link>${URL_KOK}/blog/</link><description>Yazılım, 3D baskı, ahşap ve üretim üzerine notlar.</description><language>tr</language><atom:link href="${URL_KOK}/blog/rss.xml" rel="self" type="application/rss+xml"/>\n${yazilar.map((y) => `<item><title>${kacis(y.baslik)}</title><link>${URL_KOK}/blog/${y.adres}/</link><guid>${URL_KOK}/blog/${y.adres}/</guid><pubDate>${rssTarih(y.tarih)}</pubDate><description>${kacis(y.ozet)}</description></item>`).join('\n')}\n</channel></rss>\n`);

  // robots.txt: arama motorları ve yapay zekâ tarayıcıları açıkça davetli
  const botlar = ['Googlebot', 'Bingbot', 'GPTBot', 'OAI-SearchBot', 'ChatGPT-User', 'ClaudeBot', 'Claude-SearchBot', 'Claude-User', 'PerplexityBot', 'Perplexity-User', 'Google-Extended', 'Applebot', 'Applebot-Extended', 'YandexBot', 'DuckDuckBot'];
  yaz('robots.txt', `# Kyrox Studio: tüm arama motorları ve yapay zekâ asistanları bu siteyi okuyabilir.\n${botlar.map((b) => `User-agent: ${b}\nAllow: /\n`).join('\n')}\nUser-agent: *\nAllow: /\n\nSitemap: ${URL_KOK}/sitemap.xml\n`);

  // llms.txt: yapay zekâ asistanları için sitenin özeti (llmstxt.org biçimi)
  const a = site.adres;
  yaz('llms.txt', [
    `# ${site.ad}`,
    '',
    `> ${site.uzunAciklama}`,
    '',
    `- Adres: ${a.sokak}, ${a.ilce} / ${a.il}`,
    `- Telefon ve WhatsApp: ${site.telefonGorunen} (https://wa.me/${site.whatsapp})`,
    `- E-posta: ${site.eposta}`,
    `- Web: ${URL_KOK}/`,
    '',
    '## Hizmetler',
    '',
    ...site.hizmetler.map((h) => `- ${h.ad}: ${h.aciklama}`),
    '',
    '## Yazılım ürünleri',
    '',
    ...site.urunler.map((u) => `- [${u.ad}](${u.url}): ${u.aciklama}`),
    '',
    '## Sık sorulan sorular',
    '',
    ...sssKayitlari.map(({soru, cevap}) => `- ${soru}: ${cevap}`),
    '',
    '## Blog',
    '',
    ...(yazilar.length ? yazilar.map((y) => `- [${y.baslik}](${URL_KOK}/blog/${y.adres}/): ${y.ozet}`) : ['- Henüz yazı yok.']),
    '',
  ].join('\n'));

  return yazilar.length;
}

if (process.argv[1] && fileURLToPath(import.meta.url) === process.argv[1]) {
  const n = derle();
  console.log(`Site derlendi: dist/ (${n} blog yazısı)`);
}
export { derle };
