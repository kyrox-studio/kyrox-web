<?php
/**
 * Küçük yardımcılar.
 */

defined( 'ABSPATH' ) || exit;

/** Tema içindeki bir dosyanın adresi (assets/ altına göre). */
function kyrox_varlik( string $yol ): string {
	return get_template_directory_uri() . '/assets/' . ltrim( $yol, '/' );
}

/** Çok satırlı bir başlığı satır sonlarında <br> ile, kaçışlı olarak yazar. */
function kyrox_satirlar( string $metin ): string {
	$satirlar = preg_split( '/\r\n|\r|\n/', trim( $metin ) );
	return implode( '<br>', array_map( 'esc_html', $satirlar ) );
}

/** 2026-10-14 → 14 Ekim 2026 (WordPress dil paketinden bağımsız). */
function kyrox_tarih( $yazi = null ): string {
	$aylar = array( 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık' );
	$zaman = get_post_timestamp( $yazi );
	if ( ! $zaman ) {
		return '';
	}
	$tarih = wp_date( 'j n Y', $zaman );
	list( $gun, $ay, $yil ) = explode( ' ', $tarih );
	return $gun . ' ' . $aylar[ (int) $ay - 1 ] . ' ' . $yil;
}

/** Yazının okuma süresi (dakika, en az 1). */
function kyrox_okuma_suresi( $yazi = null ): int {
	$metin = wp_strip_all_tags( get_post_field( 'post_content', $yazi ) );
	$kelime = count( preg_split( '/\s+/u', $metin, -1, PREG_SPLIT_NO_EMPTY ) );
	return max( 1, (int) ceil( $kelime / 200 ) );
}

/** Yazının özeti: elle yazılmış özet, yoksa içeriğin başı. */
function kyrox_ozet( $yazi = null ): string {
	$yazi = get_post( $yazi );
	if ( ! $yazi ) {
		return '';
	}
	if ( has_excerpt( $yazi ) ) {
		return wp_strip_all_tags( $yazi->post_excerpt );
	}
	return wp_trim_words( wp_strip_all_tags( $yazi->post_content ), 28, '…' );
}

/** Blog listesindeki bir satır (ana sayfa ve blog sayfası ortak). */
function kyrox_yazi_satiri( $yazi = null ): void {
	$yazi = get_post( $yazi );
	printf(
		'<li><a class="post-row" href="%1$s"><time datetime="%2$s">%3$s</time><span><h2>%4$s</h2><p>%5$s</p></span><span class="go">Oku →</span></a></li>',
		esc_url( get_permalink( $yazi ) ),
		esc_attr( get_the_date( 'Y-m-d', $yazi ) ),
		esc_html( kyrox_tarih( $yazi ) ),
		esc_html( get_the_title( $yazi ) ),
		esc_html( kyrox_ozet( $yazi ) )
	);
}

/** Blog sayfasının adresi (Ayarlar > Okuma'daki yazılar sayfası). */
function kyrox_blog_adresi(): string {
	$sayfa = (int) get_option( 'page_for_posts' );
	return $sayfa ? get_permalink( $sayfa ) : home_url( '/blog/' );
}
