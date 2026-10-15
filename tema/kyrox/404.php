<?php
/**
 * Bulunamayan sayfa.
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="bas" class="blog"><section class="wrap blog-head"><span class="eyebrow">404</span><h1>Bu sayfa<br><em>tezgâhta yok.</em></h1><p><a class="cta" href="<?php echo esc_url( home_url( '/' ) ); ?>">Ana sayfaya dön</a></p></section></main>
<?php
get_footer();
