<?php
/**
 * Takip bandı (sosyal medya), alt bilgi ve sağ alttaki WhatsApp düğmesi.
 */

defined( 'ABSPATH' ) || exit;
$eposta   = kyrox_ayar( 'eposta' );
$hesaplar = kyrox_sosyal_hesaplar();
$baslik   = preg_split( '/\r\n|\r|\n/', trim( kyrox_ayar( 'takip_baslik' ) ), 2 );
?>
<?php if ( $hesaplar ) : ?><section class="takip" aria-label="Sosyal medya"><div class="wrap takipin"><div><span class="eyebrow"><?php echo esc_html( kyrox_ayar( 'takip_ust' ) ); ?></span><h2><?php echo esc_html( $baslik[0] ); ?><?php if ( isset( $baslik[1] ) ) : ?><br><span><?php echo esc_html( $baslik[1] ); ?></span><?php endif; ?></h2></div><div class="takipkartlar"><?php foreach ( $hesaplar as $h ) : ?><a class="takipkart" href="<?php echo esc_url( $h['adres'] ); ?>" target="_blank" rel="noopener noreferrer me"><?php echo kyrox_sosyal_ikonu( $h['ad'], 26 ); // phpcs:ignore WordPress.Security.EscapeOutput -- sabit SVG. ?><strong><?php echo esc_html( $h['hesap'] ); ?></strong><?php if ( '' !== $h['metin'] ) : ?><p><?php echo esc_html( $h['metin'] ); ?></p><?php endif; ?><span class="takipbtn"><?php echo esc_html( $h['dugme'] ); ?> ↗</span></a><?php endforeach; ?></div></div></section><?php endif; ?>
<footer class="wrap footer"><a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" style="display:flex;align-items:center;gap:16px"><img src="<?php echo esc_url( kyrox_varlik( 'img/kyrox-ikon.svg' ) ); ?>" alt="" style="height:32px;width:auto;display:block"><span style="width:1px;height:24px;background:#383b33;display:block"></span><img src="<?php echo esc_url( kyrox_varlik( 'img/kyrox-yazi.svg' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" style="height:32px;width:auto;display:block"></a><span lang="en"><?php echo esc_html( kyrox_ayar( 'footer_slogan' ) ); ?> — © <?php echo esc_html( wp_date( 'Y' ) ); ?></span><span class="faddr"><a href="<?php echo esc_url( kyrox_harita_adresi() ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( kyrox_ayar( 'adres_ilce' ) . ' / ' . kyrox_ayar( 'adres_il' ) ); ?> ↗</a><a href="<?php echo esc_url( 'mailto:' . $eposta ); ?>"><?php echo esc_html( $eposta ); ?> ↗</a><?php if ( $hesaplar ) : ?><span class="ficons"><?php foreach ( $hesaplar as $h ) : ?><a class="sicon" href="<?php echo esc_url( $h['adres'] ); ?>" target="_blank" rel="noopener noreferrer me" aria-label="<?php echo esc_attr( $h['ad'] ); ?>" title="<?php echo esc_attr( $h['ad'] ); ?>"><?php echo kyrox_sosyal_ikonu( $h['ad'], 16 ); // phpcs:ignore WordPress.Security.EscapeOutput -- sabit SVG. ?></a><?php endforeach; ?></span><?php endif; ?></span></footer><a class="wa-float" href="<?php echo esc_url( kyrox_whatsapp_adresi() ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( 'WhatsApp ile yazın: ' . kyrox_ayar( 'telefon_gorunen' ) ); ?>"><?php echo kyrox_whatsapp_ikonu(); // phpcs:ignore WordPress.Security.EscapeOutput -- sabit SVG. ?></a>
<?php wp_footer(); ?>
</body>
</html>
