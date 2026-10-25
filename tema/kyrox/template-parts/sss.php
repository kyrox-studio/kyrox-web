<?php
/** Ana sayfanın panelden yönetilen, klavyeyle açılabilen SSS bölümü. */
defined( 'ABSPATH' ) || exit;
?>
<section class="faq wrap" id="sss" aria-labelledby="sss-baslik">
  <div class="sectionhead">
    <div><span class="eyebrow"><?php echo esc_html( kyrox_ayar( 'sss_ust' ) ); ?></span><h2 id="sss-baslik"><?php echo esc_html( kyrox_ayar( 'sss_baslik' ) ); ?></h2></div>
    <p><?php echo esc_html( kyrox_ayar( 'sss_metin' ) ); ?></p>
  </div>
  <div class="faq-list">
    <?php foreach ( kyrox_sss() as $kayit ) : ?>
      <details class="faq-item"><summary><?php echo esc_html( $kayit['soru'] ); ?></summary><p><?php echo nl2br( esc_html( $kayit['cevap'] ) ); ?></p></details>
    <?php endforeach; ?>
  </div>
  <a class="all" href="#iletisim">Başka bir sorunuz mu var? Bize yazın →</a>
</section>
