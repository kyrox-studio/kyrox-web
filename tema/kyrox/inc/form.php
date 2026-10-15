<?php
/**
 * Ana sayfadaki "İş fişi" formu: talebi sunucuda kaydeder ve e-postayla bildirir.
 *
 * - POST /wp-json/kyrox/v1/talep  (alanlar: ne, iletisim; "web" boş kalması gereken tuzak alan)
 * - Her talep yönetim panelinde "Gelen talepler" altında saklanır; e-posta gitmese de kaybolmaz.
 * - E-posta SMTP ile gider; ayarlar kapsayıcının ortam değişkenlerinden okunur
 *   (SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASSWORD, SMTP_FROM; bkz. sunucu/.env.example).
 * - Alıcı: Görünüm > Özelleştir > İletişim bilgileri > E-posta.
 */

defined( 'ABSPATH' ) || exit;

const KYROX_TALEP_LIMIT  = 5;   // Aynı IP'den süre içinde en fazla talep.
const KYROX_TALEP_SURE   = 600; // Saniye.
const KYROX_TALEP_NE_MAX = 3000;
const KYROX_TALEP_IL_MAX = 200;

add_action(
	'init',
	function () {
		register_post_type(
			'kyrox_talep',
			array(
				'labels'          => array(
					'name'          => 'Gelen talepler',
					'singular_name' => 'Talep',
					'menu_name'     => 'Gelen talepler',
					'all_items'     => 'Tüm talepler',
					'edit_item'     => 'Talep',
					'search_items'  => 'Taleplerde ara',
					'not_found'     => 'Henüz talep yok.',
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_rest'    => false,
				'menu_icon'       => 'dashicons-email-alt',
				'menu_position'   => 25,
				'supports'        => array( 'title', 'editor' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
			)
		);
	}
);

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'kyrox/v1',
			'/talep',
			array(
				'methods'             => 'POST',
				'callback'            => 'kyrox_talep_al',
				'permission_callback' => '__return_true',
				'args'                => array(
					'ne'       => array(
						'type'     => 'string',
						'required' => true,
					),
					'iletisim' => array(
						'type'     => 'string',
						'required' => true,
					),
					'web'      => array( 'type' => 'string' ),
				),
			)
		);
	}
);

/** Formdan gelen talebi doğrular, kaydeder ve e-postayla bildirir. */
function kyrox_talep_al( WP_REST_Request $istek ) {
	$ne       = trim( sanitize_textarea_field( (string) $istek->get_param( 'ne' ) ) );
	$iletisim = trim( sanitize_text_field( (string) $istek->get_param( 'iletisim' ) ) );

	// Tuzak alan doluysa bottur; başarılı görünür ama hiçbir şey yapılmaz.
	if ( '' !== trim( (string) $istek->get_param( 'web' ) ) ) {
		return array( 'ok' => true );
	}
	if ( mb_strlen( $ne ) < 3 || '' === $iletisim ) {
		return new WP_Error( 'kyrox_eksik', 'Lütfen ne lazım olduğunu ve iletişim bilginizi yazın.', array( 'status' => 400 ) );
	}
	if ( mb_strlen( $ne ) > KYROX_TALEP_NE_MAX || mb_strlen( $iletisim ) > KYROX_TALEP_IL_MAX ) {
		return new WP_Error( 'kyrox_uzun', 'Metin çok uzun; lütfen kısaltın.', array( 'status' => 400 ) );
	}

	$anahtar = 'kyrox_talep_' . md5( (string) ( $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$sayi    = (int) get_transient( $anahtar );
	if ( $sayi >= KYROX_TALEP_LIMIT ) {
		return new WP_Error( 'kyrox_sinir', 'Kısa sürede çok fazla talep gönderildi; biraz sonra tekrar deneyin ya da WhatsApp\'tan yazın.', array( 'status' => 429 ) );
	}
	set_transient( $anahtar, $sayi + 1, KYROX_TALEP_SURE );

	$baslik = wp_trim_words( $ne, 8, '…' ) . ' — ' . $iletisim;
	$kayit  = wp_insert_post(
		array(
			'post_type'    => 'kyrox_talep',
			'post_status'  => 'private',
			'post_title'   => $baslik,
			'post_content' => "Ne lazım?\n" . $ne . "\n\nİletişim: " . $iletisim,
		),
		true
	);
	if ( is_wp_error( $kayit ) ) {
		return new WP_Error( 'kyrox_kayit', 'Talep kaydedilemedi; lütfen WhatsApp\'tan yazın.', array( 'status' => 500 ) );
	}

	$ad        = get_bloginfo( 'name' );
	$govde     = "Siteden yeni iş talebi:\n\nNe lazım?\n" . $ne . "\n\nİletişim: " . $iletisim
		. "\n\nPanelde: " . admin_url( 'post.php?post=' . $kayit . '&action=edit' );
	$basliklar = array();
	if ( is_email( $iletisim ) ) {
		$basliklar[] = 'Reply-To: ' . $iletisim;
	}
	$gitti = wp_mail( kyrox_ayar( 'eposta' ), $ad . ' — Yeni iş talebi', $govde, $basliklar );
	update_post_meta( $kayit, '_kyrox_eposta', $gitti ? 'gonderildi' : 'gonderilemedi' );

	return array( 'ok' => true );
}

// SMTP: SMTP_HOST tanımlıysa wp_mail bu sunucu üzerinden gönderir.
add_action(
	'phpmailer_init',
	function ( $posta ) {
		$host = (string) getenv( 'SMTP_HOST' );
		if ( '' === $host ) {
			return;
		}
		$port = (int) ( getenv( 'SMTP_PORT' ) ?: 465 );
		$posta->isSMTP();
		$posta->Host       = $host;
		$posta->Port       = $port;
		$posta->SMTPSecure = 465 === $port ? 'ssl' : 'tls';
		$posta->SMTPAuth   = true;
		$posta->Username   = (string) getenv( 'SMTP_USER' );
		$posta->Password   = (string) getenv( 'SMTP_PASSWORD' );
		$posta->Timeout    = 15;
	}
);

// Gönderen, SMTP hesabıyla aynı olmalı; yoksa sunucu iletiyi reddeder.
add_filter(
	'wp_mail_from',
	function ( $adres ) {
		$gonderen = (string) ( getenv( 'SMTP_FROM' ) ?: getenv( 'SMTP_USER' ) );
		return is_email( $gonderen ) ? $gonderen : $adres;
	}
);
add_filter( 'wp_mail_from_name', fn() => get_bloginfo( 'name' ) );

// Gönderilemeyen e-postanın sebebi PHP hata günlüğüne yazılır (docker logs kyrox-web).
add_action(
	'wp_mail_failed',
	function ( WP_Error $hata ) {
		error_log( 'kyrox: e-posta gönderilemedi: ' . $hata->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
	}
);

// Talep listesinde e-posta durumu sütunu.
add_filter(
	'manage_kyrox_talep_posts_columns',
	function ( $sutunlar ) {
		$sutunlar['kyrox_eposta'] = 'E-posta';
		return $sutunlar;
	}
);
add_action(
	'manage_kyrox_talep_posts_custom_column',
	function ( $sutun, $kayit ) {
		if ( 'kyrox_eposta' === $sutun ) {
			echo 'gonderildi' === get_post_meta( $kayit, '_kyrox_eposta', true ) ? 'Gönderildi' : 'Gönderilemedi';
		}
	},
	10,
	2
);
