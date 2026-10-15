<?php
/**
 * Blog yazısı.
 */

defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	$etiketler = get_the_tags() ?: array();
	?>
<main id="bas" class="blog">
<article class="wrap post">
<header class="post-head">
<a class="back" href="<?php echo esc_url( kyrox_blog_adresi() ); ?>">← Blog</a>
<div class="post-meta"><time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( kyrox_tarih() ); ?></time><span><?php echo esc_html( kyrox_okuma_suresi() ); ?> dk okuma</span><?php foreach ( $etiketler as $etiket ) : ?><span class="ptag"><?php echo esc_html( $etiket->name ); ?></span><?php endforeach; ?></div>
<h1><?php the_title(); ?></h1>
<?php if ( has_excerpt() ) : ?>
<p class="lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
<?php endif; ?>
</header>
<?php if ( has_post_thumbnail() ) : ?>
<figure class="post-cover"><?php the_post_thumbnail( 'large' ); ?></figure>
<?php endif; ?>
<div class="prose">
<?php the_content(); ?>
</div>
<aside class="post-cta">
<div>
<span class="eyebrow">Bir fikriniz mi var?</span>
<p>Ne lazım olduğunu yazın, ölçüsünü ve malzemesini birlikte netleştirelim.</p>
</div>
<div class="post-cta-btns">
<a class="cta" href="<?php echo esc_url( kyrox_whatsapp_adresi() ); ?>" target="_blank" rel="noopener noreferrer">WhatsApp'tan yazın ↗</a>
<a class="pill" href="<?php echo esc_url( home_url( '/#iletisim' ) ); ?>">İş fişi</a>
</div>
</aside>
</article>
</main>
	<?php
endwhile;

get_footer();
