<?php
/**
 * Kyrox Studio teması.
 *
 * inc/yardimci.php   küçük yardımcılar (tarih, okuma süresi, metin)
 * inc/ayarlar.php    Görünüm > Özelleştir: ana sayfa metinleri, iletişim, koyu/krem
 * inc/cizimler.php   işler kartlarındaki teknik çizimler ve ikonlar (satır içi SVG)
 * inc/isler.php      "İşler" içerik türü
 * inc/seo.php        meta, Open Graph, JSON-LD, robots.txt, llms.txt, site haritası
 * inc/guvenlik.php   XML-RPC, kullanıcı numaralandırma, yorumlar
 * inc/kurulum.php    tema etkinleşince sayfalar, kalıcı bağlantılar ve başlangıç içeriği
 * inc/form.php       iş fişi formu: talebi kaydeder, SMTP ile e-postayla bildirir
 */

defined( 'ABSPATH' ) || exit;

define( 'KYROX_SURUM', '1.3.7' );
define( 'KYROX_DIZIN', __DIR__ );

require_once KYROX_DIZIN . '/inc/yardimci.php';
require_once KYROX_DIZIN . '/inc/urunler.php';
require_once KYROX_DIZIN . '/inc/ayarlar.php';
require_once KYROX_DIZIN . '/inc/cizimler.php';
require_once KYROX_DIZIN . '/inc/isler.php';
require_once KYROX_DIZIN . '/inc/seo.php';
require_once KYROX_DIZIN . '/inc/guvenlik.php';
require_once KYROX_DIZIN . '/inc/kurulum.php';
require_once KYROX_DIZIN . '/inc/form.php';
require_once KYROX_DIZIN . '/inc/bilgilendirme.php';

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_post_type_support( 'page', 'excerpt' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'responsive-embeds' );
	}
);

add_filter( 'document_title_separator', fn() => '—' );

add_action(
	'wp_enqueue_scripts',
	function () {
		$v = KYROX_SURUM;
		wp_enqueue_style( 'kyrox-fonts', kyrox_varlik( 'css/fonts.css' ), array(), $v );
		wp_enqueue_style( 'kyrox-depolama', kyrox_varlik( 'css/depolama.css' ), array( 'kyrox-fonts' ), $v );
		wp_enqueue_script( 'kyrox-depolama', kyrox_varlik( 'js/depolama.js' ), array(), $v, array( 'strategy' => 'defer', 'in_footer' => true ) );
		if ( is_page( 'fuar-stand' ) ) {
			wp_enqueue_style( 'kyrox-stand-colors', kyrox_varlik( 'stand-v3/colors.css' ), array( 'kyrox-fonts' ), $v );
			wp_enqueue_style( 'kyrox-stand-v3', kyrox_varlik( 'stand-v3/styles-v3.css' ), array( 'kyrox-stand-colors' ), $v );
			wp_enqueue_script( 'kyrox-stand-v3', kyrox_varlik( 'stand-v3/interactions-v3.js' ), array( 'kyrox-depolama' ), $v, array( 'strategy' => 'defer', 'in_footer' => true ) );
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'global-styles' );
			wp_dequeue_style( 'classic-theme-styles' );
			return;
		}
		wp_enqueue_style( 'kyrox-site', kyrox_varlik( 'css/site.css' ), array( 'kyrox-fonts' ), $v );
		wp_enqueue_style( 'kyrox-blog', kyrox_varlik( 'css/blog.css' ), array( 'kyrox-site' ), $v );
		if ( is_front_page() ) {
			wp_enqueue_script( 'kyrox-anasayfa', kyrox_varlik( 'js/anasayfa.js' ), array(), $v, array( 'strategy' => 'defer' ) );
		}
		// Blok düzenleyicinin ön yüz stilleri bu temada kullanılmıyor.
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	},
	20
);

// Sayfa başına gereksiz betikleri kaldır.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
