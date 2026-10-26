<?php
/**
 * Ana sayfa. Metinler Görünüm > Özelleştir > Kyrox Studio'dan, iş kartları "İşler"den gelir.
 * Krem görünümde giriş ve iş fişi bölümleri krem şeride alınır (tasarımdaki iki varyant).
 */

defined( 'ABSPATH' ) || exit;
get_header();

$a      = 'kyrox_ayar';
$krem   = kyrox_krem();
$eposta = $a( 'eposta' );
$adres  = $a( 'adres_sokak' ) . ', ' . $a( 'adres_ilce' ) . ' / ' . $a( 'adres_il' );
$isler  = kyrox_isler();
$son    = get_posts( array( 'numberposts' => 3, 'post_status' => 'publish' ) );
?>
<main id="bas" tabindex="-1">
<section class="wrap hero"><div class="herohead"><div><div class="eyebrow"><?php echo esc_html( $a( 'hero_ust' ) ); ?></div><h1><?php echo esc_html( $a( 'hero_baslik1' ) ); ?><br><em><?php echo esc_html( $a( 'hero_baslik2' ) ); ?></em></h1></div><div class="heroright"><p class="heroaside"><span class="trio"><span style="color:#C9CCD0"><?php echo esc_html( $a( 'hero_tasarliyoruz' ) ); ?></span><span style="color:#CD8D56"><?php echo esc_html( $a( 'hero_kodluyoruz' ) ); ?></span><span style="color:#A2652C"><?php echo esc_html( $a( 'hero_uretiyoruz' ) ); ?></span></span><?php echo esc_html( $a( 'hero_metin' ) ); ?></p></div></div>
<div class="stage" id="stage" aria-label="Teknik tasarım ve zanaat arasında hareketli karşılaştırma"><div class="precision"><span class="stageword" lang="en">PRECISION</span><div class="specbox">PROJECT / KYROX-001<br>PRECISION / DIGITAL<br>GRID / 28 × 28</div></div><div class="craft"><span class="stageword" lang="en">CRAFT.</span></div><div class="seamline"></div><div class="handle" id="handle" role="slider" tabindex="0" aria-label="Teknik ve üretim yüzeyleri arasındaki ayrım" aria-valuemin="15" aria-valuemax="85" aria-valuenow="54">↔</div><span class="stagehint">← Sürükleyin →</span></div><div class="stagecaption"><span><?php echo esc_html( $a( 'sahne_sol' ) ); ?></span><span lang="en"><?php echo esc_html( $a( 'slogan' ) ); ?></span><span><?php echo esc_html( $a( 'sahne_sag' ) ); ?></span></div></section>
<?php echo $krem ? '<section class="band-cream"><div class="wrap intro">' : '<section class="wrap intro">'; ?><div class="introleft"><div class="eyebrow"><?php echo esc_html( $a( 'giris_ust' ) ); ?></div><ol class="flow" id="flow">
<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
<li><span class="ic"><?php echo kyrox_akis_ikonu( $i ); // phpcs:ignore WordPress.Security.EscapeOutput -- sabit SVG. ?></span><span><span class="n"><?php echo esc_html( sprintf( '%02d', $i ) ); ?></span><b><?php echo esc_html( $a( "akis{$i}_baslik" ) ); ?></b><small><?php echo esc_html( $a( "akis{$i}_metin" ) ); ?></small></span></li>
<?php endfor; ?>
</ol></div><div><h2><?php echo esc_html( $a( 'giris_baslik1' ) ); ?><br><span><?php echo esc_html( $a( 'giris_baslik2' ) ); ?></span></h2><p class="sub"><?php echo esc_html( $a( 'giris_metin' ) ); ?></p></div><?php echo $krem ? '</div></section>' : '</section>'; ?>

<section class="tools wrap" id="yetenekler"><div class="sectionhead"><div><span class="eyebrow"><?php echo esc_html( $a( 'atolye_ust' ) ); ?></span><h2><?php echo kyrox_satirlar( $a( 'atolye_baslik' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- kyrox_satirlar kaçış yapar. ?></h2></div><p><?php echo esc_html( $a( 'atolye_metin' ) ); ?></p></div><div class="toolgrid">
<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
<article class="tool"><?php echo kyrox_arac_ikonu( $i ); // phpcs:ignore WordPress.Security.EscapeOutput -- sabit SVG. ?><div><h3><?php echo esc_html( $a( "arac{$i}_baslik" ) ); ?></h3><p><?php echo esc_html( $a( "arac{$i}_metin" ) ); ?></p></div><span class="meta"><?php echo esc_html( $a( "arac{$i}_etiket" ) ); ?></span></article>
<?php endfor; ?>
</div></section>

<?php if ( $isler ) : ?>
<section class="worksec" id="projeler"><div class="wrap"><div class="sectionhead"><div><span class="eyebrow"><?php echo esc_html( $a( 'isler_ust' ) ); ?></span><h2><?php echo kyrox_satirlar( $a( 'isler_baslik' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2></div><p><?php echo esc_html( $a( 'isler_metin' ) ); ?></p></div><div class="works"><?php array_map( 'kyrox_is_karti', $isler ); ?></div></div></section>
<?php endif; ?>
<?php if ( $son ) : ?>
<section class="wrap home-posts" id="blog"><div class="sectionhead"><div><span class="eyebrow"><?php echo esc_html( $a( 'blog_ust' ) ); ?></span><h2><?php echo kyrox_satirlar( $a( 'blog_baslik' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2></div><p><?php echo esc_html( $a( 'blog_metin' ) ); ?></p></div><ol class="post-list"><?php array_map( 'kyrox_yazi_satiri', $son ); ?></ol><a class="all" href="<?php echo esc_url( kyrox_blog_adresi() ); ?>">Tüm yazılar →</a></section>
<?php endif; ?>
<?php get_template_part( 'template-parts/sss' ); ?>
<?php echo $krem ? '<section class="band-cream" id="iletisim"><div class="order wrap">' : '<section class="order wrap" id="iletisim">'; ?><div class="ordergrid"><div><span class="eyebrow"><?php echo esc_html( $a( 'fis_ust' ) ); ?></span><h2><?php echo kyrox_satirlar( $a( 'fis_baslik' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2><p class="description"><?php echo esc_html( $a( 'fis_metin' ) ); ?></p><div class="contactbox"><div class="cinfo"><div><span class="mono">E-posta</span><a href="<?php echo esc_url( 'mailto:' . $eposta ); ?>"><?php echo esc_html( $eposta ); ?></a></div><div><span class="mono">Atölye</span><a href="<?php echo esc_url( kyrox_harita_adresi() ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $adres ); ?> ↗</a></div><?php if ( kyrox_sosyal_baglantilar() ) : ?><div><span class="mono">Sosyal medya</span><span class="sosyal"><?php foreach ( kyrox_sosyal_baglantilar() as $sosyal_adres => $sosyal_ad ) : ?><a href="<?php echo esc_url( $sosyal_adres ); ?>" target="_blank" rel="noopener noreferrer me"><?php echo esc_html( $sosyal_ad ); ?> ↗</a><?php endforeach; ?></span></div><?php endif; ?></div></div></div><form class="ticket" id="ticket" data-eposta="<?php echo esc_attr( $eposta ); ?>" data-whatsapp="<?php echo esc_attr( kyrox_rakamlar( $a( 'whatsapp' ) ) ); ?>" data-ad="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" data-api="<?php echo esc_url( rest_url( 'kyrox/v1/talep' ) ); ?>"><div class="tickethead"><strong>İŞ FİŞİ</strong><span>KYROX / 001</span></div><div class="fields"><div class="field wide"><label for="what">Ne lazım?</label><textarea id="what" name="ne" required maxlength="3000"placeholder="Bir uygulama, 3D parça, özel ahşap ürün…"></textarea></div><div class="field wide"><label for="contact">İletişim bilginiz</label><input id="contact" name="iletisim" required maxlength="200" placeholder="Adınız, telefon veya e-posta"><input type="text" name="web" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0"></div><?php kyrox_form_bilgilendirmesi(); ?><div class="ticketfooter" style="justify-content:flex-end"><div class="btns"><a class="submit wa-send" id="waSend" href="<?php echo esc_url( kyrox_whatsapp_adresi() ); ?>" target="_blank" rel="noopener noreferrer">WhatsApp'tan gönder ↗</a><button type="submit" class="submit">Gönder</button></div></div><p class="feedback" id="feedback" role="status" aria-live="polite"></p></div></form></div><?php echo $krem ? '</div></section>' : '</section>'; ?>
</main>
<?php
get_footer();
