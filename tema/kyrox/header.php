<?php
/**
 * Üst menü. Logo ve bölüm bağlantıları tasarımdaki gibidir.
 */

defined( 'ABSPATH' ) || exit;
$ana = home_url( '/' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="nav"><div class="wrap navin"><a class="brand" style="display:flex;align-items:center;gap:22px" href="<?php echo esc_url( $ana ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> ana sayfa"><img src="<?php echo esc_url( kyrox_varlik( 'img/kyrox-ikon.svg' ) ); ?>" alt="" style="height:46px;width:auto;display:block"><span style="width:1px;height:34px;background:#383b33;display:block"></span><img src="<?php echo esc_url( kyrox_varlik( 'img/kyrox-yazi.svg' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" style="height:46px;width:auto;display:block"></a><div class="links"><a href="<?php echo esc_url( $ana . '#projeler' ); ?>">Projeler</a><a href="<?php echo esc_url( $ana . '#yetenekler' ); ?>">Yetenekler</a><a href="<?php echo esc_url( kyrox_blog_adresi() ); ?>">Blog</a><a class="pill" href="<?php echo esc_url( $ana . '#iletisim' ); ?>">Bir fikir getirin ↗</a></div></div></header>
