<?php
/** Ürün tanıtım sayfalarının başlangıç içeriği ve mevcut içeriği koruyan kurulumu. */
defined( 'ABSPATH' ) || exit;

function kyrox_gecerli_urun(): ?array {
	foreach ( kyrox_urunler() as $urun ) {
		if ( is_page( $urun['adres'] ) ) {
			return $urun;
		}
	}
	return null;
}

function kyrox_urunler(): array {
	static $urunler = null;
	if ( null === $urunler ) {
		$urunler = json_decode( (string) file_get_contents( __DIR__ . '/baslangic/urunler.json' ), true ) ?: array();
	}
	return $urunler;
}

/** Yeni sayfaları ekler; panelde düzenlenmiş veya taslak mevcut sayfaları değiştirmez. */
function kyrox_urun_sayfalarini_kur(): bool {
	foreach ( kyrox_urunler() as $urun ) {
		if ( get_page_by_path( $urun['adres'] ) ) {
			continue;
		}
		$icerik = file_get_contents( __DIR__ . '/baslangic/' . $urun['icerik'] );
		if ( false === $icerik ) {
			return false;
		}
		if ( ! empty( $urun['bagimsiz'] ) ) {
			$icerik = "<!-- wp:html -->\n" . $icerik . "\n<!-- /wp:html -->";
		}
		$id = wp_insert_post( array(
			'post_type' => 'page', 'post_status' => 'publish',
			'post_title' => $urun['baslik'], 'post_name' => $urun['adres'],
			'post_excerpt' => $urun['aciklama'], 'post_content' => $icerik,
			'comment_status' => 'closed', 'ping_status' => 'closed',
		), true );
		if ( ! $id || is_wp_error( $id ) ) {
			return false;
		}
	}
	return true;
}

/**
 * Mevcut Stand Konfiguratör sayfasına oturumsuz demo bağlantısını ekler (bir kez).
 * Yalnız başlangıç içeriğindeki metin hâlâ aynıysa değiştirir; panelden düzenlenmiş
 * metne dokunmaz. Doğrudan veritabanına yazılır: içerik `wp:html` bloğu ve kses
 * süzgeci data-* özniteliklerini silebilir.
 */
function kyrox_stand_demo_baglantisini_ekle(): void {
	$sayfa = get_page_by_path( 'fuar-stand' );
	if ( ! $sayfa ) {
		return;
	}
	$degisim = array(
		'<a href="https://fuar.kyrox.studio/stand-projects" class="btn secondary">Platformu incele ↗</a>'
			=> '<a href="https://fuar.kyrox.studio/stand-demo" class="btn secondary">Giriş yapmadan dene ↗</a>',
		'<p>Hayır. Bu sayfadaki Elikon sahnesi giriş yapmadan incelenebilir. Kendi projelerinizi oluşturmak ve kaydetmek için uygulamaya giriş yapmanız gerekir.</p>'
			=> '<p>Hayır. Bu sayfadaki Elikon sahnesi giriş yapmadan incelenebilir. Editörü de <a href="https://fuar.kyrox.studio/stand-demo">demo sayfasında</a> giriş yapmadan günde 25 dakika deneyebilirsiniz; demoda proje kaydedilmez. Kendi projelerinizi oluşturmak ve kaydetmek için uygulamaya giriş yapmanız gerekir.</p>',
	);
	$icerik = str_replace( array_keys( $degisim ), array_values( $degisim ), $sayfa->post_content );
	if ( $icerik !== $sayfa->post_content ) {
		global $wpdb;
		$wpdb->update( $wpdb->posts, array( 'post_content' => $icerik ), array( 'ID' => $sayfa->ID ) );
		clean_post_cache( $sayfa->ID );
	}
}

add_action( 'init', function () {
	if ( (int) get_option( 'kyrox_stand_demo_baglantisi' ) < 1 ) {
		kyrox_stand_demo_baglantisini_ekle();
		update_option( 'kyrox_stand_demo_baglantisi', 1 );
	}
} );

/** Eski başlangıç içeriğini V4 olarak göster; paneldeki sayfa kaydını değiştirme. */
function kyrox_stand_v4_icerigi( string $icerik ): string {
	$degisim = array(
		'href="https://fuar.kyrox.studio/stand-projects">Stand tasarla'
			=> 'href="https://fuar.kyrox.studio/stand-demo">Stand tasarla',
		'<a class="btn secondary" href="https://fuar.kyrox.studio/stand-demo">Giriş yapmadan dene ↗</a>'
			=> '<a class="btn secondary" href="#kanit">Tasarımdan gerçeğe ↓</a>',
		'data-showcase data-state="idle"><div class="hud"><span><b>●</b> GERÇEK PROJE / ELIKON</span><span>9 × 5 METRE</span>'
			=> 'data-showcase data-showcase-project="simes-ada" data-state="idle"><div class="hud"><span><b>●</b> GERÇEK PROJE / 3DM + SİMES</span><span>12 × 9 METRE</span>',
		'{{STAND_ASSETS}}/elikon-cover.webp" alt="Elikon\'un 9 × 5 metrelik kayıtlı stand tasarımı'
			=> '{{STAND_ASSETS}}/simes-ada-cover.webp" alt="3DM + SİMES için tasarlanan 12 × 9 metrelik ada standı',
		'aria-label="Elikon standını üç boyutlu inceleyin"'
			=> 'aria-label="3DM + SİMES ada standını üç boyutlu inceleyin"',
		'Gösterilen 9 × 5 metrelik stand, Fair Stand uygulamasında kayıtlı Elikon projesinden'
			=> 'Gösterilen 12 × 9 metrelik ada standı, Fair Stand uygulamasında kayıtlı 3DM + SİMES projesinden',
		'Bu sayfadaki Elikon sahnesi' => 'Bu sayfadaki 3DM + SİMES sahnesi',
	);
	return strtr( $icerik, $degisim );
}

// Tema güncellemesinde yönetici ilk giriş yaptığında eksik ürün sayfalarını hazırla.
add_action( 'admin_init', function () {
	if ( current_user_can( 'manage_options' ) && (int) get_option( 'kyrox_urun_sayfalari_surumu' ) < 1 ) {
		if ( kyrox_urun_sayfalarini_kur() ) {
			update_option( 'kyrox_urun_sayfalari_surumu', 1 );
		}
	}
} );
