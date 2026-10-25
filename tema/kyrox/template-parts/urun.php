<?php
/** Ürün içeriği Sayfalar'dan düzenlenir; uygulama CTA'sı ürün kaydından gelir. */
defined( 'ABSPATH' ) || exit;
get_header();
while ( have_posts() ) :
	the_post();
	$urun = null;
	foreach ( kyrox_urunler() as $aday ) {
		if ( get_post_field( 'post_name' ) === $aday['adres'] ) {
			$urun = $aday;
			break;
		}
	}
	?>
<main id="bas" class="product-page wrap">
  <header class="product-hero">
    <a class="all" href="<?php echo esc_url( home_url( '/#projeler' ) ); ?>">← Tüm projeler</a>
    <?php if ( $urun ) : ?><span class="eyebrow"><?php echo esc_html( $urun['etiket'] ); ?></span><?php endif; ?>
    <h1><?php the_title(); ?></h1>
    <p class="product-lead"><?php echo esc_html( kyrox_ozet() ); ?></p>
    <div class="product-actions">
      <?php if ( $urun ) : ?><a class="cta" href="<?php echo esc_url( $urun['uygulama'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $urun['dugme'] ); ?> ↗</a><?php endif; ?>
      <a class="pill" href="<?php echo esc_url( home_url( '/#iletisim' ) ); ?>">Bilgi alın →</a>
    </div>
  </header>
  <div class="product-content"><?php the_content(); ?></div>
</main>
	<?php
endwhile;
get_footer();
