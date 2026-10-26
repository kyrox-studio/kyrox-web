<?php
/** V3: mevcut ürün tasarımı ve gerçek Elikon sahnesi için bağımsız sayfa kabuğu. */
defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width,initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class( 'stand-landing' ); ?>>
<?php
wp_body_open();
while ( have_posts() ) {
	the_post();
	$icerik = str_replace( '{{STAND_ASSETS}}', esc_url( kyrox_varlik( 'stand-v3' ) ), get_the_content() );
	echo apply_filters( 'the_content', $icerik ); // phpcs:ignore WordPress.Security.EscapeOutput -- WordPress sayfa içeriği.
}
kyrox_bilgi_baglantilari();
wp_footer();
?>
</body></html>
