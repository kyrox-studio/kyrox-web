<?php
/**
 * Yedek şablon: etiket ve kategori arşivleri gibi diğer listeler blog listesi düzeninde.
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="bas" class="blog">
<section class="wrap blog-head">
<span class="eyebrow">Blog</span>
<h1><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
</section>
<section class="wrap">
<ol class="post-list">
<?php
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		kyrox_yazi_satiri();
	}
} else {
	echo '<li class="post-empty">Henüz yazı yok.</li>';
}
?>
</ol>
<?php the_posts_pagination( array( 'prev_text' => '← Daha yeni', 'next_text' => 'Daha eski →' ) ); ?>
</section>
</main>
<?php
get_footer();
