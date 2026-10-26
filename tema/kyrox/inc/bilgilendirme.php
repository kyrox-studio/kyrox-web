<?php
/** Panelde yönetilen bilgilendirme sayfalarına yalnız yayımlanmışken bağlantı verir. */
defined( 'ABSPATH' ) || exit;

function kyrox_bilgi_sayfalari(): array {
	$sonuc = array();
	foreach ( array( 'gizlilik' => 'Gizlilik ve veri kullanımı', 'kullanim-kosullari' => 'Kullanım koşulları', 'cerezler' => 'Çerezler ve depolama' ) as $yol => $ad ) {
		$sayfa = get_page_by_path( $yol );
		if ( $sayfa && 'publish' === $sayfa->post_status && '' === $sayfa->post_password ) {
			$sonuc[ $yol ] = array( 'ad' => $ad, 'adres' => get_permalink( $sayfa ) );
		}
	}
	return $sonuc;
}

function kyrox_bilgi_baglantilari(): void {
	$sayfalar = kyrox_bilgi_sayfalari();
	if ( ! $sayfalar ) {
		return;
	}
	echo '<nav class="kyrox-bilgi wrap" aria-label="Gizlilik ve kullanım bilgileri">';
	foreach ( $sayfalar as $sayfa ) {
		echo '<a href="' . esc_url( $sayfa['adres'] ) . '">' . esc_html( $sayfa['ad'] ) . '</a>';
	}
	echo '</nav>';
}

function kyrox_form_bilgilendirmesi(): void {
	$sayfalar = kyrox_bilgi_sayfalari();
	if ( isset( $sayfalar['gizlilik'] ) ) {
		echo '<p class="kyrox-form-bilgi">KYROX STUDIO, yazdığınız talep ve iletişim bilgilerini talebinizi değerlendirmek ve size dönüş yapmak için kaydeder; e-posta bildirimi alır. WhatsApp seçeneği bu bilgileri WhatsApp bağlantısına ekler. <a href="' . esc_url( $sayfalar['gizlilik']['adres'] ) . '">Veri kullanımı hakkında bilgi</a>.</p>';
	}
}
