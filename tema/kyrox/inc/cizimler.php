<?php
/**
 * Satır içi SVG çizimler ve ikonlar. Onaylı tasarımdaki çizimlerin birebir kopyasıdır;
 * renk ve çizgi kalınlığı site.css'ten gelir.
 */

defined( 'ABSPATH' ) || exit;

/** İş kartlarında seçilebilen teknik çizimler: anahtar => panelde görünen ad. */
function kyrox_cizim_secenekleri(): array {
	return array(
		'ekran'  => 'Ekran / yazılım arayüzü',
		'stand'  => 'İzometrik stand',
		'rakle'  => 'Kesit / ahşap rakle',
		'harf'   => 'Kutu harf',
		'yok'    => 'Çizim yok (öne çıkan görsel kullanılır)',
	);
}

function kyrox_cizim( string $anahtar ): string {
	$yazi = 'font-family="Inter,system-ui,sans-serif" font-size="11" letter-spacing="1" fill="#cad8d9" stroke="none"';
	switch ( $anahtar ) {
		case 'ekran':
			return '<svg viewBox="0 0 400 300" aria-hidden="true"><rect x="70" y="60" width="260" height="170" rx="4"/><path d="M70 82h260"/><circle cx="84" cy="71" r="3"/><circle cx="96" cy="71" r="3"/><path d="M70 82v148M130 82v148"/><path d="M84 100h32M84 116h24M84 132h28"/><path d="M146 104h120M146 126h150M146 148h110M146 170h140M146 192h90"/><path class="c" d="M300 100h18M300 144h18M300 188h18"/><path class="c" d="M70 40h260M70 34v12M330 34v12"/><text x="200" y="32" text-anchor="middle" ' . $yazi . '>1440 PX</text><text x="70" y="256" ' . $yazi . '>EKRAN / FİRMALAR</text></svg>';
		case 'stand':
			return '<svg viewBox="0 0 400 300" aria-hidden="true"><path d="M80 200l120 50 120-50-120-50z"/><path d="M80 200V110l120-50v90"/><path d="M200 60l120 50v90"/><path d="M110 172v-40l60-25v40"/><path class="c" d="M220 214l40-17v-26l-40 17zM220 188l-24-10 40-17 24 10"/><path d="M220 214v-26"/><path class="c" d="M80 222l120 50M80 216v12M200 266v12"/><text x="112" y="268" ' . $yazi . ' transform="rotate(22 112 268)">3000</text><text x="250" y="40" ' . $yazi . '>İZOMETRİK / STAND</text></svg>';
		case 'rakle':
			return '<svg viewBox="0 0 400 300" aria-hidden="true"><rect x="80" y="120" width="240" height="56" rx="10"/><path d="M110 132v32M126 132v32M142 132v32M158 132v32"/><path class="c" d="M80 176h240v8H80z"/><path stroke-dasharray="6 5" d="M200 96v112M60 148h280"/><path class="c" d="M80 222h240M80 216v12M320 216v12"/><path class="c" d="M344 120v64M338 120h12M338 184h12"/><text x="200" y="244" text-anchor="middle" ' . $yazi . '>180 MM</text><text x="352" y="156" ' . $yazi . '>45</text><text x="80" y="84" ' . $yazi . '>KESİT / AHŞAP RAKLE</text></svg>';
		case 'harf':
			return '<svg viewBox="0 0 400 300" aria-hidden="true"><path d="M130 70h34v62l56-62h42l-62 66 66 94h-42l-48-72-12 13v59h-34z"/><path d="M150 56h34v62l56-62h42l-62 66 66 94h-42l-48-72-12 13v59h-34z" opacity=".5"/><path d="M130 70l20-14M164 70l20-14M262 70l20-14M268 230l20-14M226 230l20-14M164 230l20-14M130 230l20-14" opacity=".6"/><path class="c" d="M104 70v160M98 70h12M98 230h12"/><text x="86" y="154" ' . $yazi . ' transform="rotate(-90 86 154)" text-anchor="middle">400</text><text x="130" y="268" ' . $yazi . '>KUTU HARF / 3D BASKI</text></svg>';
	}
	return '';
}

/** Atölye kartlarının ikonları (1–4). */
function kyrox_arac_ikonu( int $sira ): string {
	$ikonlar = array(
		1 => '<svg viewBox="0 0 54 54"><rect x="5" y="9" width="44" height="31" rx="2"/><path d="M18 48h18m-9-8v8M18 21l-6 5 6 5m18-10 6 5-6 5m-4-12-6 14"/></svg>',
		2 => '<svg viewBox="0 0 54 54"><path d="m10 44 17-17 9 9-17 17M26 26l7-7 7 7-7 7m4-18 6-6 7 7-6 6M8 49l4-4"/></svg>',
		3 => '<svg viewBox="0 0 54 54"><rect x="9" y="5" width="36" height="44" rx="2"/><path d="M9 16h36M17 16v8h20v-8M23 24v7h8v-7M16 40h22"/></svg>',
		4 => '<svg viewBox="0 0 54 54"><path d="M4 46h46M9 46V18h36v28M9 18l8-11h20l8 11M27 18v13m-5 0h10l-5 10z"/></svg>',
	);
	return $ikonlar[ $sira ] ?? '';
}

/** "Biz ne yapıyoruz?" akışının ikonları (1–3). */
function kyrox_akis_ikonu( int $sira ): string {
	$ikonlar = array(
		1 => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="12" rx="1.5"/><path d="M9 20h6M12 16v4"/></svg>',
		2 => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="1.5"/><path d="M4 8h16M9 8v3h6V8M11 11v2h2v-2M8 17h8"/></svg>',
		3 => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 20h20M4 20V10h16v10M4 10l3-5h10l3 5M12 10v5M10 15h4l-2 3z"/></svg>',
	);
	return $ikonlar[ $sira ] ?? '';
}

/** Sağ alttaki WhatsApp düğmesinin ikonu. */
function kyrox_whatsapp_ikonu(): string {
	return '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20l1.3-3.9A8 8 0 1 1 8 19z"/><path d="M9 9.5c.3 1.8 1.7 3.7 3.6 4.6l1.2-1.2 1.8.8-.4 1.6c-3.3.2-6.6-3.2-6.4-6.5l1.6-.4.8 1.8z"/></svg>';
}
