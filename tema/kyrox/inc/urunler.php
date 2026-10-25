<?php
/** Ürün tanıtım sayfalarının başlangıç içeriği ve mevcut içeriği koruyan kurulumu. */
defined( 'ABSPATH' ) || exit;

function kyrox_urunler(): array {
	static $urunler = null;
	if ( null === $urunler ) {
		$urunler = json_decode( (string) file_get_contents( __DIR__ . '/baslangic/urunler.json' ), true ) ?: array();
	}
	return $urunler;
}

/** Yeni sayfaları ekler; panelde düzenlenmiş veya taslak mevcut sayfaları değiştirmez. */
function kyrox_urun_sayfalarini_kur(): bool {
	foreach ( kyrox_urunler() as $urun ) {
		if ( get_page_by_path( $urun['adres'] ) ) {
			continue;
		}
		$icerik = file_get_contents( __DIR__ . '/baslangic/' . $urun['icerik'] );
		if ( false === $icerik ) {
			return false;
		}
		if ( ! empty( $urun['bagimsiz'] ) ) {
			$icerik = "<!-- wp:html -->\n" . $icerik . "\n<!-- /wp:html -->";
		}
		$id = wp_insert_post( array(
			'post_type' => 'page', 'post_status' => 'publish',
			'post_title' => $urun['baslik'], 'post_name' => $urun['adres'],
			'post_excerpt' => $urun['aciklama'], 'post_content' => $icerik,
			'comment_status' => 'closed', 'ping_status' => 'closed',
		), true );
		if ( ! $id || is_wp_error( $id ) ) {
			return false;
		}
	}
	return true;
}

// Tema güncellemesinde yönetici ilk giriş yaptığında eksik ürün sayfalarını hazırla.
add_action( 'admin_init', function () {
	if ( current_user_can( 'manage_options' ) && (int) get_option( 'kyrox_urun_sayfalari_surumu' ) < 1 ) {
		if ( kyrox_urun_sayfalarini_kur() ) {
			update_option( 'kyrox_urun_sayfalari_surumu', 1 );
		}
	}
} );
