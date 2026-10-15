<?php
/**
 * Blog sayfası: bütün yazılar, yeniden eskiye.
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="bas" class="blog">
<section class="wrap blog-head">
<span class="eyebrow">Blog</span>
<h1>Atölyeden<br><em>notlar.</em></h1>
<p><?php echo esc_html( kyrox_ayar( 'blog_sayfa_metin' ) ); ?></p>
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
<?php
the_posts_pagination(
	array(
		'mid_size'           => 1,
		'prev_text'          => '← Daha yeni',
		'next_text'          => 'Daha eski →',
		'screen_reader_text' => 'Sayfalar',
	)
);
?>
</section>
</main>
<?php
get_footer();
