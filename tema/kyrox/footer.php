<?php
/**
 * Alt bilgi ve sağ alttaki WhatsApp düğmesi.
 */

defined( 'ABSPATH' ) || exit;
$eposta = kyrox_ayar( 'eposta' );
?>
<footer class="wrap footer"><a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" style="display:flex;align-items:center;gap:16px"><img src="<?php echo esc_url( kyrox_varlik( 'img/kyrox-ikon.svg' ) ); ?>" alt="" style="height:32px;width:auto;display:block"><span style="width:1px;height:24px;background:#383b33;display:block"></span><img src="<?php echo esc_url( kyrox_varlik( 'img/kyrox-yazi.svg' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" style="height:32px;width:auto;display:block"></a><span lang="en"><?php echo esc_html( kyrox_ayar( 'footer_slogan' ) ); ?> — © <?php echo esc_html( wp_date( 'Y' ) ); ?></span><span class="faddr"><a href="<?php echo esc_url( kyrox_whatsapp_adresi() ); ?>" target="_blank" rel="noopener noreferrer">WhatsApp <?php echo esc_html( kyrox_ayar( 'telefon_gorunen' ) ); ?> ↗</a><a href="<?php echo esc_url( kyrox_harita_adresi() ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( kyrox_ayar( 'adres_ilce' ) . ' / ' . kyrox_ayar( 'adres_il' ) ); ?> ↗</a><a href="<?php echo esc_url( 'mailto:' . $eposta ); ?>"><?php echo esc_html( $eposta ); ?> ↗</a></span></footer><a class="wa-float" href="<?php echo esc_url( kyrox_whatsapp_adresi() ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( 'WhatsApp ile yazın: ' . kyrox_ayar( 'telefon_gorunen' ) ); ?>"><?php echo kyrox_whatsapp_ikonu(); // phpcs:ignore WordPress.Security.EscapeOutput -- sabit SVG. ?></a>
<?php wp_footer(); ?>
</body>
</html>
