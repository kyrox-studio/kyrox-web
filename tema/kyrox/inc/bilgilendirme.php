<?php
/** Panelde yönetilen bilgilendirme sayfalarına yalnız yayımlanmışken bağlantı verir. */
defined( 'ABSPATH' ) || exit;

function kyrox_bilgi_sayfalari(): array {
	$sonuc = array();
	foreach ( array( 'gizlilik' => 'Gizlilik ve veri kullanımı', 'kullanim-kosullari' => 'Kullanım koşulları', 'cerezler' => 'Çerezler ve depolama' ) as $yol => $ad ) {
		$sayfa = get_page_by_path( $yol );
		if ( $sayfa && 'publish' === $sayfa->post_status && '' === $sayfa->post_password ) {
			$sonuc[ $yol ] = array( 'ad' => $ad, 'adres' => get_permalink( $sayfa ) );
		}
	}
	return $sonuc;
}

function kyrox_bilgi_baglantilari(): void {
	$sayfalar = kyrox_bilgi_sayfalari();
	if ( ! $sayfalar ) {
		return;
	}
	echo '<nav class="kyrox-bilgi wrap" aria-label="Gizlilik ve kullanım bilgileri">';
	foreach ( $sayfalar as $sayfa ) {
		echo '<a href="' . esc_url( $sayfa['adres'] ) . '">' . esc_html( $sayfa['ad'] ) . '</a>';
	}
	echo '<button type="button" data-storage-open>Depolama tercihleri</button>';
	echo '</nav>';
	kyrox_depolama_paneli();
}

function kyrox_depolama_paneli(): void {
	?>
<section class="kyrox-storage-prompt" data-storage-prompt hidden aria-label="3D önbellek tercihi">
<p>3D örneklerin sonraki ziyaretlerde hızlı açılması için model dosyasını tarayıcınızda saklamamıza izin verir misiniz? İzin vermeseniz de sahne otomatik açılır. Bu tercih reklam veya analiz izni vermez.</p>
<div class="kyrox-storage-actions"><button type="button" data-storage-allow>İzin ver</button><button type="button" data-storage-deny>İzin verme</button><button type="button" data-storage-open>Tercihleri incele</button></div>
</section>
<dialog class="kyrox-storage-dialog" data-storage-dialog aria-labelledby="kyrox-storage-title">
<h2 id="kyrox-storage-title">Depolama tercihleri</h2>
<p>Bu ayar yalnız kyrox.studio sitesindeki 3D örnek içindir. CRM/Stand oturumunu veya projelerinizi silmez. Reklam ve analiz servisi bu panelle etkinleştirilmez.</p>
<label><input type="checkbox" data-storage-model> 3D modelini sonraki ziyaretler için sakla</label>
<p>Kapalıyken model saklanmadan yüklenir; bu siteye ait eski 3D model önbelleği temizlenir. Açmak, sonraki model yüklemesinde önbelleğe izin verir. Tercihiniz bu tarayıcıda 180 gün hatırlanır; buradan her zaman değiştirebilirsiniz.</p>
<p><a href="<?php echo esc_url( home_url( '/cerezler/' ) ); ?>">Çerezler ve depolama hakkında bilgi</a></p>
<div class="kyrox-storage-actions"><button type="button" data-storage-save>Tercihi kaydet</button><button type="button" data-storage-close>Kapat</button></div>
</dialog>
<p class="kyrox-storage-status wrap" data-storage-status role="status" aria-live="polite"></p>
	<?php
}

function kyrox_form_bilgilendirmesi(): void {
	$sayfalar = kyrox_bilgi_sayfalari();
	if ( isset( $sayfalar['gizlilik'] ) ) {
		echo '<p class="kyrox-form-bilgi">KYROX STUDIO, yazdığınız talep ve iletişim bilgilerini talebinizi değerlendirmek ve size dönüş yapmak için kaydeder; e-posta bildirimi alır. WhatsApp seçeneği bu bilgileri WhatsApp bağlantısına ekler. <a href="' . esc_url( $sayfalar['gizlilik']['adres'] ) . '">Veri kullanımı hakkında bilgi</a>.</p>';
	}
}
