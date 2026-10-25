<?php
/**
 * Tema ilk etkinleştiğinde: Ana sayfa ve Blog sayfaları, kalıcı bağlantılar,
 * Türkçe tarih/saat ayarları, "Atölye notları" kategorisi, örnek yazı ve iş kartları. Bir kez çalışır; var olan
 * içeriğe dokunmaz. Elle tekrar çalıştırmak: wp eval 'kyrox_kur();'
 */

defined( 'ABSPATH' ) || exit;

const KYROX_KURULUM_SURUMU = 1;

add_action(
	'after_switch_theme',
	function () {
		if ( (int) get_option( 'kyrox_kurulum' ) < KYROX_KURULUM_SURUMU ) {
			kyrox_kur();
		}
		flush_rewrite_rules();
	}
);

// Eski kategori adresi (Uncategorized) yeni adrese kalıcı yönlenir; arama motorlarındaki eski bağlantı boşa düşmez.
add_action(
	'template_redirect',
	function () {
		if ( is_404() && 'uncategorized' === get_query_var( 'category_name' ) ) {
			$kategori = get_term( (int) get_option( 'default_category' ), 'category' );
			if ( $kategori instanceof WP_Term ) {
				wp_safe_redirect( get_term_link( $kategori ), 301 );
				exit;
			}
		}
	}
);

/** Başlığa göre sayfayı bulur, yoksa oluşturur. */
function kyrox_sayfa( string $baslik, string $adres ): int {
	$sayfa = get_page_by_path( $adres );
	if ( $sayfa ) {
		return $sayfa->ID;
	}
	return (int) wp_insert_post(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'post_title'     => $baslik,
			'post_name'      => $adres,
			'comment_status' => 'closed',
		)
	);
}

function kyrox_kur(): void {
	// Ana sayfa ve blog sayfası.
	$ana  = kyrox_sayfa( 'Ana sayfa', 'ana-sayfa' );
	$blog = kyrox_sayfa( 'Blog', 'blog' );
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $ana );
	update_option( 'page_for_posts', $blog );
	update_option( 'posts_per_page', 10 );

	// Yazılar /blog/yazi-adi/ adresinde.
	update_option( 'permalink_structure', '/blog/%postname%/' );

	// Site bilgileri, Türkçe tarih ve saat.
	update_option( 'blogname', 'Kyrox Studio' );
	update_option( 'blogdescription', 'Fikirden üretime' );
	update_option( 'timezone_string', 'Europe/Istanbul' );
	update_option( 'date_format', 'j F Y' );
	update_option( 'time_format', 'H:i' );
	update_option( 'start_of_week', 1 );

	// Yorumlar ve geri bildirimler kapalı; ziyaretçi kaydı kapalı.
	update_option( 'default_comment_status', 'closed' );
	update_option( 'default_ping_status', 'closed' );
	update_option( 'users_can_register', 0 );

	// WordPress'in örnek içeriği, dokunulmamışsa kaldırılır.
	$merhaba = get_page_by_path( 'hello-world', OBJECT, 'post' );
	if ( $merhaba && $merhaba->post_modified === $merhaba->post_date ) {
		wp_delete_post( $merhaba->ID, true );
	}
	$ornek = get_page_by_path( 'sample-page' );
	if ( $ornek && $ornek->post_modified === $ornek->post_date ) {
		wp_delete_post( $ornek->ID, true );
	}

	// Varsayılan kategori İngilizce "Uncategorized" kalmasın.
	$kategori = get_term( (int) get_option( 'default_category' ), 'category' );
	if ( $kategori instanceof WP_Term && 'uncategorized' === $kategori->slug ) {
		wp_update_term(
			$kategori->term_id,
			'category',
			array(
				'name'        => 'Atölye notları',
				'slug'        => 'atolye-notlari',
				'description' => 'Yazılım, 3D baskı, ahşap ve üretim üzerine denediklerimiz ve öğrendiklerimiz.',
			)
		);
	}

	kyrox_ornek_yazi();
	kyrox_urun_sayfalarini_kur();
	kyrox_ornek_isler();

	update_option( 'kyrox_kurulum', KYROX_KURULUM_SURUMU );
}

/** Örnek blog yazısı (statik sitedeki ilk yazı). */
function kyrox_ornek_yazi(): void {
	$dizin = KYROX_DIZIN . '/inc/baslangic/';
	$bilgi = json_decode( (string) file_get_contents( $dizin . 'kutu-harf.json' ), true );
	if ( ! $bilgi || get_page_by_path( $bilgi['adres'], OBJECT, 'post' ) ) {
		return;
	}
	$yazi_id = wp_insert_post(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'post_title'     => $bilgi['baslik'],
			'post_name'      => $bilgi['adres'],
			'post_excerpt'   => $bilgi['ozet'],
			'post_content'   => (string) file_get_contents( $dizin . 'kutu-harf.html' ),
			'post_date'      => $bilgi['tarih'] . ' 09:00:00',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		)
	);
	if ( $yazi_id && ! is_wp_error( $yazi_id ) ) {
		wp_set_post_tags( $yazi_id, $bilgi['etiketler'] );
	}
}

/** Onaylı tasarımdaki dört iş kartı. */
function kyrox_ornek_isler(): void {
	if ( get_posts( array( 'post_type' => KYROX_IS, 'post_status' => 'any', 'numberposts' => 1 ) ) ) {
		return;
	}
	$isler = array(
		array( 'Fuar CRM', 'Fuar, firma, görüşme ve teklif süreçleri tek bir yerde.', 'Yazılım', 'kullanimda', home_url( '/fuar-crm/' ), 'ekran' ),
		array( 'Fuar Stand', 'Gerçek modüllerle 3D stand tasarımı ve malzeme listeleri.', 'Kod + 3D', 'kullanimda', home_url( '/fuar-stand/' ), 'stand' ),
		array( 'Ahşap Rakle', 'Ele oturan, baskı ve folyo uygulamaları için ahşap araç.', 'Ahşap', 'tezgahta', '', 'rakle' ),
		array( 'Kutu Harf', 'Ölçüye göre tasarlanan ve üretilen harfler, logolar.', '3D Üretim', 'tezgahta', '', 'harf' ),
	);
	foreach ( $isler as $sira => list( $baslik, $metin, $tur, $durum, $link, $cizim ) ) {
		wp_insert_post(
			array(
				'post_type'    => KYROX_IS,
				'post_status'  => 'publish',
				'post_title'   => $baslik,
				'post_excerpt' => $metin,
				'menu_order'   => ( $sira + 1 ) * 10,
				'meta_input'   => array(
					'kyrox_tur'   => $tur,
					'kyrox_durum' => $durum,
					'kyrox_link'  => $link,
					'kyrox_cizim' => $cizim,
				),
			)
		);
	}
}
