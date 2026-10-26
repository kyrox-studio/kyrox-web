<?php
/**
 * Sıradan sayfa (ör. KVKK aydınlatma metni). Blog yazısıyla aynı okuma düzeni.
 */

defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	?>
<main id="bas" tabindex="-1" class="blog">
<article class="wrap post">
<header class="post-head">
<h1><?php the_title(); ?></h1>
</header>
<div class="prose">
<?php the_content(); ?>
</div>
</article>
</main>
	<?php
endwhile;

get_footer();
