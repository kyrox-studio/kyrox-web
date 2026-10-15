<?php
/**
 * Güvenlik ve sadelik: XML-RPC kapalı, yazar adları gizli, yorumlar kapalı.
 */

defined( 'ABSPATH' ) || exit;

// XML-RPC kullanılmıyor; kaba kuvvet denemelerine açık bir kapı.
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'wp_headers', function ( $basliklar ) {
	unset( $basliklar['X-Pingback'] );
	return $basliklar;
} );

// ?author=1 ile kullanıcı adı öğrenilmesin; yazar arşivleri ana sayfaya gider.
add_action(
	'template_redirect',
	function () {
		if ( is_author() || ( isset( $_GET['author'] ) && ! is_admin() ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	}
);

// REST API'deki kullanıcı listesi yalnız giriş yapmış kullanıcılara.
add_filter(
	'rest_endpoints',
	function ( $uclar ) {
		if ( ! is_user_logged_in() ) {
			unset( $uclar['/wp/v2/users'], $uclar['/wp/v2/users/(?P<id>[\d]+)'] );
		}
		return $uclar;
	}
);

// Hatalı girişte hangi bilginin yanlış olduğunu söyleme.
add_filter( 'login_errors', fn() => 'Kullanıcı adı veya şifre hatalı.' );

// Sitede yorum yok.
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 10 );
add_action(
	'admin_menu',
	function () {
		remove_menu_page( 'edit-comments.php' );
	}
);
add_action(
	'admin_bar_menu',
	function ( $cubuk ) {
		$cubuk->remove_node( 'comments' );
	},
	999
);
