<?php
/**
 * Arama motorları ve yapay zekâ asistanları için: meta, Open Graph, JSON-LD,
 * robots.txt, /llms.txt ve site haritası ayarları.
 */

defined( 'ABSPATH' ) || exit;

/** Geçerli sayfanın açıklaması. */
function kyrox_sayfa_aciklamasi(): string {
	if ( is_front_page() ) {
		return kyrox_ayar( 'seo_aciklama' );
	}
	if ( is_home() ) {
		return wp_strip_all_tags( kyrox_ayar( 'blog_sayfa_metin' ) );
	}
	if ( is_singular() ) {
		return kyrox_ozet();
	}
	if ( is_404() ) {
		return 'Aradığınız sayfa burada değil.';
	}
	return kyrox_ayar( 'seo_aciklama' );
}

/** Geçerli sayfanın kalıcı adresi. */
function kyrox_sayfa_adresi(): string {
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_home() ) {
		$sayfa = get_query_var( 'paged' );
		return $sayfa > 1 ? get_pagenum_link( $sayfa ) : kyrox_blog_adresi();
	}
	if ( is_singular() ) {
		return get_permalink();
	}
	return '';
}

add_filter(
	'pre_get_document_title',
	function ( $baslik ) {
		return is_front_page() ? kyrox_ayar( 'seo_baslik' ) : $baslik;
	}
);

add_filter( 'document_title_parts', function ( $parcalar ) {
	if ( is_404() ) {
		$parcalar['title'] = 'Sayfa bulunamadı';
	}
	return $parcalar;
} );

// WordPress tekil sayfalarda canonical yazar; diğerlerini biz ekleriz.
add_action(
	'wp_head',
	function () {
		$aciklama = kyrox_sayfa_aciklamasi();
		$adres    = kyrox_sayfa_adresi();
		$baslik   = wp_get_document_title();
		$gorsel   = kyrox_varlik( 'img/og-image.png' );
		$tur      = is_singular( 'post' ) ? 'article' : 'website';

		echo '<meta name="theme-color" content="#111210">' . "\n";
		if ( $aciklama ) {
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( $aciklama ) );
		}
		if ( $adres && ! is_singular() ) {
			printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $adres ) );
		}
		printf( '<link rel="icon" type="image/svg+xml" href="%s">' . "\n", esc_url( kyrox_varlik( 'img/favicon.svg' ) ) );
		printf( '<link rel="icon" href="%s" sizes="any">' . "\n", esc_url( kyrox_varlik( 'img/favicon.ico' ) ) );

		$og = array(
			'og:type'      => $tur,
			'og:site_name' => get_bloginfo( 'name' ),
			'og:locale'    => 'tr_TR',
			'og:url'       => $adres,
			'og:title'     => $baslik,
			'og:description' => $aciklama,
			'og:image'     => $gorsel,
			'og:image:width'  => '1200',
			'og:image:height' => '630',
		);
		foreach ( $og as $ozellik => $deger ) {
			if ( '' !== $deger ) {
				printf( '<meta property="%s" content="%s">' . "\n", esc_attr( $ozellik ), esc_attr( $deger ) );
			}
		}
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";

		printf(
			'<script type="application/ld+json">%s</script>' . "\n",
			wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => kyrox_jsonld() ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG )
		);
	},
	1
);

/** schema.org bilgisi: kurum, web sitesi ve (yazılarda) blog yazısı. */
function kyrox_jsonld(): array {
	$kok     = untrailingslashit( home_url() );
	$araclar = array();
	for ( $i = 1; $i <= 4; $i++ ) {
		$araclar[] = array(
			'ad'      => kyrox_ayar( "arac{$i}_baslik" ),
			'aciklama' => kyrox_ayar( "arac{$i}_metin" ),
		);
	}
	$kurum = array(
		'@type'       => 'LocalBusiness',
		'@id'         => $kok . '/#kurum',
		'name'        => get_bloginfo( 'name' ),
		'url'         => $kok . '/',
		'logo'        => kyrox_varlik( 'img/favicon.svg' ),
		'image'       => kyrox_varlik( 'img/og-image.png' ),
		'description' => kyrox_ayar( 'kurum_aciklama' ),
		'slogan'      => kyrox_ayar( 'slogan' ),
		'email'       => kyrox_ayar( 'eposta' ),
		'telephone'   => kyrox_ayar( 'telefon' ),
		'address'     => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => kyrox_ayar( 'adres_sokak' ),
			'addressLocality' => kyrox_ayar( 'adres_ilce' ),
			'addressRegion'   => kyrox_ayar( 'adres_il' ),
			'addressCountry'  => 'TR',
		),
		'areaServed'  => array(
			array( '@type' => 'City', 'name' => kyrox_ayar( 'adres_il' ) ),
			array( '@type' => 'Country', 'name' => 'Türkiye' ),
		),
		'knowsAbout'  => wp_list_pluck( $araclar, 'ad' ),
		'makesOffer'  => array_map(
			fn( $a ) => array(
				'@type'       => 'Offer',
				'itemOffered' => array( '@type' => 'Service', 'name' => $a['ad'], 'description' => $a['aciklama'] ),
			),
			$araclar
		),
	);
	if ( kyrox_sosyal() ) {
		$kurum['sameAs'] = kyrox_sosyal();
	}
	$site = array(
		'@type'      => 'WebSite',
		'@id'        => $kok . '/#site',
		'url'        => $kok . '/',
		'name'       => get_bloginfo( 'name' ),
		'inLanguage' => 'tr-TR',
		'publisher'  => array( '@id' => $kok . '/#kurum' ),
	);
	$graf = array( $kurum, $site );

	if ( is_home() ) {
		$graf[] = array(
			'@type'      => 'Blog',
			'@id'        => kyrox_blog_adresi() . '#blog',
			'url'        => kyrox_blog_adresi(),
			'name'       => get_bloginfo( 'name' ) . ' Blog',
			'inLanguage' => 'tr-TR',
			'publisher'  => array( '@id' => $kok . '/#kurum' ),
		);
	}

	if ( is_singular( 'post' ) ) {
		$adres    = get_permalink();
		$etiketler = wp_list_pluck( get_the_tags() ?: array(), 'name' );
		$yazi     = array(
			'@type'            => 'BlogPosting',
			'@id'              => $adres . '#yazi',
			'headline'         => get_the_title(),
			'description'      => kyrox_ozet(),
			'datePublished'    => get_the_date( 'c' ),
			'dateModified'     => get_the_modified_date( 'c' ),
			'inLanguage'       => 'tr-TR',
			'mainEntityOfPage' => $adres,
			'image'            => has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'large' ) : kyrox_varlik( 'img/og-image.png' ),
			'author'           => array( '@id' => $kok . '/#kurum' ),
			'publisher'        => array( '@id' => $kok . '/#kurum' ),
		);
		if ( $etiketler ) {
			$yazi['keywords'] = implode( ', ', $etiketler );
		}
		$graf[] = $yazi;
		$graf[] = array(
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Ana sayfa', 'item' => $kok . '/' ),
				array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => kyrox_blog_adresi() ),
				array( '@type' => 'ListItem', 'position' => 3, 'name' => get_the_title(), 'item' => $adres ),
			),
		);
	}
	return $graf;
}

// robots.txt: arama motorları ve yapay zekâ tarayıcıları açıkça davetli.
add_filter(
	'robots_txt',
	function ( $cikti, $acik ) {
		if ( ! $acik ) {
			return $cikti; // Ayarlar > Okuma'da "arama motorlarını engelle" seçiliyse WordPress'inkini bırak.
		}
		$botlar = array( 'Googlebot', 'Bingbot', 'GPTBot', 'OAI-SearchBot', 'ChatGPT-User', 'ClaudeBot', 'Claude-SearchBot', 'Claude-User', 'PerplexityBot', 'Perplexity-User', 'Google-Extended', 'Applebot', 'Applebot-Extended', 'YandexBot', 'DuckDuckBot' );
		$satir  = array( '# Kyrox Studio: tüm arama motorları ve yapay zekâ asistanları bu siteyi okuyabilir.' );
		foreach ( $botlar as $bot ) {
			$satir[] = "User-agent: {$bot}\nAllow: /\n";
		}
		$satir[] = "User-agent: *\nAllow: /\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\n";
		$satir[] = 'Sitemap: ' . home_url( '/wp-sitemap.xml' );
		return implode( "\n", $satir ) . "\n";
	},
	10,
	2
);

// /llms.txt: yapay zekâ asistanları için sitenin özeti (llmstxt.org biçimi).
add_action( 'init', fn() => add_rewrite_rule( '^llms\.txt$', 'index.php?kyrox_llms=1', 'top' ) );
add_filter( 'query_vars', fn( $q ) => array_merge( $q, array( 'kyrox_llms' ) ) );
add_filter(
	'redirect_canonical',
	fn( $hedef ) => get_query_var( 'kyrox_llms' ) ? false : $hedef
);
add_action(
	'template_redirect',
	function () {
		if ( ! get_query_var( 'kyrox_llms' ) ) {
			return;
		}
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo kyrox_llms_metni(); // phpcs:ignore WordPress.Security.EscapeOutput -- düz metin.
		exit;
	}
);

function kyrox_llms_metni(): string {
	$s   = array();
	$s[] = '# ' . get_bloginfo( 'name' );
	$s[] = '';
	$s[] = '> ' . kyrox_ayar( 'kurum_aciklama' );
	$s[] = '';
	$s[] = '- Adres: ' . kyrox_ayar( 'adres_sokak' ) . ', ' . kyrox_ayar( 'adres_ilce' ) . ' / ' . kyrox_ayar( 'adres_il' );
	$s[] = '- Telefon ve WhatsApp: ' . kyrox_ayar( 'telefon_gorunen' ) . ' (' . kyrox_whatsapp_adresi() . ')';
	$s[] = '- E-posta: ' . kyrox_ayar( 'eposta' );
	$s[] = '- Web: ' . home_url( '/' );
	$s[] = '';
	$s[] = '## Hizmetler';
	$s[] = '';
	for ( $i = 1; $i <= 4; $i++ ) {
		$s[] = '- ' . kyrox_ayar( "arac{$i}_baslik" ) . ': ' . kyrox_ayar( "arac{$i}_metin" );
	}
	$isler = array_filter( kyrox_isler(), fn( $is ) => (bool) get_post_meta( $is->ID, 'kyrox_link', true ) );
	if ( $isler ) {
		$s[] = '';
		$s[] = '## Yazılım ürünleri';
		$s[] = '';
		foreach ( $isler as $is ) {
			$s[] = '- [' . get_the_title( $is ) . '](' . get_post_meta( $is->ID, 'kyrox_link', true ) . '): ' . wp_strip_all_tags( $is->post_excerpt );
		}
	}
	$s[]     = '';
	$s[]     = '## Blog';
	$s[]     = '';
	$yazilar = get_posts( array( 'numberposts' => 50, 'post_status' => 'publish' ) );
	if ( $yazilar ) {
		foreach ( $yazilar as $y ) {
			$s[] = '- [' . get_the_title( $y ) . '](' . get_permalink( $y ) . '): ' . kyrox_ozet( $y );
		}
	} else {
		$s[] = '- Henüz yazı yok.';
	}
	return html_entity_decode( implode( "\n", $s ), ENT_QUOTES, 'UTF-8' ) . "\n";
}

// Site haritası: kullanıcı listesini çıkar (yazar adlarını yaymamak için).
add_filter(
	'wp_sitemaps_add_provider',
	fn( $saglayici, $ad ) => 'users' === $ad ? false : $saglayici,
	10,
	2
);
