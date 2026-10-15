<?php
/**
 * Görünüm > Özelleştir ayarları. Varsayılanlar onaylı tasarımdaki metinlerdir.
 */

defined( 'ABSPATH' ) || exit;

/** Bütün ayarların varsayılanları. */
function kyrox_varsayilanlar(): array {
	static $v = null;
	if ( null !== $v ) {
		return $v;
	}
	$v = array(
		// Görünüm
		'gorunum'           => 'koyu',

		// Açılış
		'hero_ust'          => 'Bağımsız yaratıcı üretim stüdyosu',
		'hero_baslik1'      => 'FİKİRDEN',
		'hero_baslik2'      => 'ÜRETİME.',
		'hero_tasarliyoruz' => 'Tasarlıyoruz.',
		'hero_kodluyoruz'   => 'Kodluyoruz.',
		'hero_uretiyoruz'   => 'Üretiyoruz.',
		'hero_metin'        => 'Bir gün yazılım, ertesi gün ahşap bir parça. Arada 3D yazıcı da çalışıyor.',
		'sahne_sol'         => 'Dijital düşünce',
		'slogan'            => 'Precision meets Craft.',
		'sahne_sag'         => 'Fiziksel üretim',

		// Giriş ve akış
		'giris_ust'         => 'Biz ne yapıyoruz?',
		'akis1_baslik'      => 'Ekran',
		'akis1_metin'       => 'Çizim, 3D model, kod.',
		'akis2_baslik'      => 'Prototip',
		'akis2_metin'       => '3D yazıcıda ilk parça.',
		'akis3_baslik'      => 'Tezgâh',
		'akis3_metin'       => 'Ahşap, CNC, el işçiliği.',
		'giris_baslik1'     => 'Ekranda başlar.',
		'giris_baslik2'     => 'Tezgâhta şekillenir.',
		'giris_metin'       => 'KYROX Studio; yazılım, üç boyutlu tasarım ve atölye üretimini aynı düşünme biçiminde buluşturur. Hazır kalıplara sığmayan fikirleri çalışır hâle getiririz.',

		// Atölye
		'atolye_ust'        => 'Atölye & teknoloji',
		'atolye_baslik'     => "Atölyede\nneler var?",
		'atolye_metin'      => 'Sorunu önce anlıyoruz. Sonra doğru malzemeyi, doğru teknolojiyi ve doğru yöntemi seçiyoruz.',
		'arac1_baslik'      => 'Yazılım',
		'arac1_metin'       => 'CRM, web sistemleri ve iş akışına özel araçlar.',
		'arac1_etiket'      => 'Dijital',
		'arac2_baslik'      => 'El İşçiliği',
		'arac2_metin'       => 'Makinenin bıraktığı yere insan eli dokunur.',
		'arac2_etiket'      => 'Atölye',
		'arac3_baslik'      => '3D Baskı',
		'arac3_metin'       => 'Prototip, maket, parça, kalıp ve özel üretim.',
		'arac3_etiket'      => 'Üretim',
		'arac4_baslik'      => 'CNC & Ahşap',
		'arac4_metin'       => 'Malzeme odaklı tasarım, kesim ve işçilik.',
		'arac4_etiket'      => 'CNC planlanan yetenek',

		// İşler
		'isler_ust'         => 'İşler',
		'isler_baslik'      => "Birbirine benzemeyen\nişleri seviyoruz.",
		'isler_metin'       => 'Yazılım projesinden ahşap parçaya. İş değişiyor, yaklaşım değişmiyor: düşün, dene, üret.',

		// Blog
		'blog_ust'          => 'Blog',
		'blog_baslik'       => "Atölyeden\nnotlar.",
		'blog_metin'        => 'Denediklerimiz, öğrendiklerimiz.',
		'blog_sayfa_metin'  => 'Yazılım, 3D baskı, ahşap ve üretim üzerine; denediklerimiz ve öğrendiklerimiz.',

		// İş fişi
		'fis_ust'           => 'İş fişi',
		'fis_baslik'        => "NE\nYAPALIM?",
		'fis_metin'         => 'Bir çizim, bir fikir, bir problem… Aklınızdakini anlatın. Ölçüyü, malzemeyi ve neye ihtiyacınız olduğunu birlikte netleştirelim.',

		// İletişim ve kurum
		'eposta'            => 'hello@kyrox.studio',
		'telefon'           => '+905469350935',
		'telefon_gorunen'   => '0546 935 0 935',
		'whatsapp'          => '905469350935',
		'adres_sokak'       => 'Altınşehir Mh. Şenel Cad. No:7A',
		'adres_ilce'        => 'Ümraniye',
		'adres_il'          => 'İstanbul',
		'harita'            => 'Altınşehir Mahallesi Şenel Caddesi No:7A Ümraniye İstanbul',
		'footer_slogan'     => 'Limited projects. Lasting impact.',

		// Arama motorları ve yapay zekâ
		'seo_baslik'        => 'Kyrox Studio — Fikirden Üretime',
		'seo_aciklama'      => 'Tasarlıyoruz. Kodluyoruz. Üretiyoruz. Yazılım, 3D baskı, CNC ve el işçiliği; Ümraniye / İstanbul.',
		'kurum_aciklama'    => "Kyrox Studio, İstanbul Ümraniye'de bir yaratıcı üretim atölyesidir. Yazılım geliştirir, 3D baskı ile parça ve kutu harf üretir, ahşap işler ve CNC kesimi yapar. Fuar ve stand firmaları için Fuar CRM ve Fuar Stand yazılımlarını geliştirmiştir.",
		'sosyal'            => '',
	);
	return $v;
}

/** Bir ayarın değeri (boşsa varsayılan). */
function kyrox_ayar( string $anahtar ): string {
	$varsayilan = kyrox_varsayilanlar()[ $anahtar ] ?? '';
	$deger      = get_theme_mod( 'kyrox_' . $anahtar, $varsayilan );
	return '' === trim( (string) $deger ) ? $varsayilan : (string) $deger;
}

/** Krem görünüm seçili mi? */
function kyrox_krem(): bool {
	return 'krem' === kyrox_ayar( 'gorunum' );
}

/** Yalnız rakamlar (WhatsApp ve telefon bağlantıları için). */
function kyrox_rakamlar( string $s ): string {
	return preg_replace( '/\D+/', '', $s );
}

function kyrox_whatsapp_adresi( string $metin = '' ): string {
	$adres = 'https://wa.me/' . kyrox_rakamlar( kyrox_ayar( 'whatsapp' ) );
	return '' === $metin ? $adres : $adres . '?text=' . rawurlencode( $metin );
}

function kyrox_harita_adresi(): string {
	return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( kyrox_ayar( 'harita' ) );
}

/** Sosyal medya adresleri (her satıra bir adres). */
function kyrox_sosyal(): array {
	$satirlar = preg_split( '/\s+/', kyrox_ayar( 'sosyal' ), -1, PREG_SPLIT_NO_EMPTY );
	return array_values( array_filter( array_map( 'esc_url_raw', $satirlar ) ) );
}

add_action(
	'customize_register',
	function ( WP_Customize_Manager $wp_customize ) {
		$v = kyrox_varsayilanlar();

		$wp_customize->add_panel(
			'kyrox',
			array(
				'title'    => 'Kyrox Studio',
				'priority' => 20,
			)
		);

		// Kısa yol: bir bölüme metin alanları ekler.
		$ekle = function ( string $bolum, array $alanlar ) use ( $wp_customize, $v ) {
			foreach ( $alanlar as $anahtar => $tanim ) {
				list( $etiket, $tur ) = array_pad( (array) $tanim, 2, 'text' );
				$wp_customize->add_setting(
					'kyrox_' . $anahtar,
					array(
						'default'           => $v[ $anahtar ],
						'sanitize_callback' => 'textarea' === $tur ? 'sanitize_textarea_field' : ( 'email' === $tur ? 'sanitize_email' : 'sanitize_text_field' ),
						'transport'         => 'refresh',
					)
				);
				$wp_customize->add_control(
					'kyrox_' . $anahtar,
					array(
						'label'   => $etiket,
						'section' => $bolum,
						'type'    => $tur,
					)
				);
			}
		};

		$bolumler = array(
			'kyrox_gorunum'  => 'Görünüm',
			'kyrox_acilis'   => 'Açılış',
			'kyrox_giris'    => 'Giriş ve akış',
			'kyrox_atolye'   => 'Atölye',
			'kyrox_isler'    => 'İşler bölümü',
			'kyrox_blog'     => 'Blog bölümü',
			'kyrox_fis'      => 'İş fişi',
			'kyrox_iletisim' => 'İletişim bilgileri',
			'kyrox_seo'      => 'Arama motorları ve yapay zekâ',
		);
		$sira = 10;
		foreach ( $bolumler as $kimlik => $baslik ) {
			$wp_customize->add_section(
				$kimlik,
				array(
					'title'    => $baslik,
					'panel'    => 'kyrox',
					'priority' => $sira,
				)
			);
			$sira += 10;
		}

		$wp_customize->add_setting(
			'kyrox_gorunum',
			array(
				'default'           => $v['gorunum'],
				'sanitize_callback' => fn( $d ) => in_array( $d, array( 'koyu', 'krem' ), true ) ? $d : 'koyu',
			)
		);
		$wp_customize->add_control(
			'kyrox_gorunum',
			array(
				'label'       => 'Ana sayfa görünümü',
				'description' => 'Krem: giriş ve iş fişi bölümleri krem zemin üzerinde.',
				'section'     => 'kyrox_gorunum',
				'type'        => 'radio',
				'choices'     => array(
					'koyu' => 'Koyu',
					'krem' => 'Siyah-krem',
				),
			)
		);

		$ekle(
			'kyrox_acilis',
			array(
				'hero_ust'          => 'Üst etiket',
				'hero_baslik1'      => 'Büyük başlık, 1. satır',
				'hero_baslik2'      => 'Büyük başlık, 2. satır (bakır)',
				'hero_tasarliyoruz' => 'Sağ üçlü, 1 (çelik)',
				'hero_kodluyoruz'   => 'Sağ üçlü, 2 (bakır)',
				'hero_uretiyoruz'   => 'Sağ üçlü, 3 (ahşap)',
				'hero_metin'        => array( 'Sağdaki kısa metin', 'textarea' ),
				'sahne_sol'         => 'Kaydırıcı altı, sol',
				'slogan'            => 'Slogan (kaydırıcı altı, orta)',
				'sahne_sag'         => 'Kaydırıcı altı, sağ',
			)
		);
		$ekle(
			'kyrox_giris',
			array(
				'giris_ust'     => 'Üst etiket',
				'akis1_baslik'  => 'Akış 1: başlık',
				'akis1_metin'   => 'Akış 1: metin',
				'akis2_baslik'  => 'Akış 2: başlık',
				'akis2_metin'   => 'Akış 2: metin',
				'akis3_baslik'  => 'Akış 3: başlık',
				'akis3_metin'   => 'Akış 3: metin',
				'giris_baslik1' => 'Başlık, 1. satır',
				'giris_baslik2' => 'Başlık, 2. satır (soluk)',
				'giris_metin'   => array( 'Metin', 'textarea' ),
			)
		);
		$ekle(
			'kyrox_atolye',
			array(
				'atolye_ust'    => 'Üst etiket',
				'atolye_baslik' => array( 'Başlık (satır başı için Enter)', 'textarea' ),
				'atolye_metin'  => array( 'Metin', 'textarea' ),
				'arac1_baslik'  => 'Kart 1: başlık',
				'arac1_metin'   => 'Kart 1: metin',
				'arac1_etiket'  => 'Kart 1: alt etiket',
				'arac2_baslik'  => 'Kart 2: başlık',
				'arac2_metin'   => 'Kart 2: metin',
				'arac2_etiket'  => 'Kart 2: alt etiket',
				'arac3_baslik'  => 'Kart 3: başlık',
				'arac3_metin'   => 'Kart 3: metin',
				'arac3_etiket'  => 'Kart 3: alt etiket',
				'arac4_baslik'  => 'Kart 4: başlık',
				'arac4_metin'   => 'Kart 4: metin',
				'arac4_etiket'  => 'Kart 4: alt etiket',
			)
		);
		$ekle(
			'kyrox_isler',
			array(
				'isler_ust'    => 'Üst etiket',
				'isler_baslik' => array( 'Başlık (satır başı için Enter)', 'textarea' ),
				'isler_metin'  => array( 'Metin', 'textarea' ),
			)
		);
		$wp_customize->get_section( 'kyrox_isler' )->description = 'Kartların kendisi soldaki "İşler" menüsünden eklenir ve sıralanır.';
		$ekle(
			'kyrox_blog',
			array(
				'blog_ust'         => 'Ana sayfa: üst etiket',
				'blog_baslik'      => array( 'Ana sayfa: başlık', 'textarea' ),
				'blog_metin'       => 'Ana sayfa: metin',
				'blog_sayfa_metin' => array( 'Blog sayfası: giriş metni', 'textarea' ),
			)
		);
		$ekle(
			'kyrox_fis',
			array(
				'fis_ust'    => 'Üst etiket',
				'fis_baslik' => array( 'Başlık (satır başı için Enter)', 'textarea' ),
				'fis_metin'  => array( 'Metin', 'textarea' ),
			)
		);
		$ekle(
			'kyrox_iletisim',
			array(
				'eposta'          => array( 'E-posta', 'email' ),
				'telefon'         => 'Telefon (uluslararası, ör. +905469350935)',
				'telefon_gorunen' => 'Telefon (görünen)',
				'whatsapp'        => 'WhatsApp numarası (yalnız rakam, ör. 905469350935)',
				'adres_sokak'     => 'Adres: mahalle, cadde, no',
				'adres_ilce'      => 'Adres: ilçe',
				'adres_il'        => 'Adres: il',
				'harita'          => 'Harita için adres (Google Haritalar araması; kısaltmasız yazın)',
				'footer_slogan'   => 'Alt bilgi sloganı',
			)
		);
		$ekle(
			'kyrox_seo',
			array(
				'seo_baslik'     => 'Ana sayfa başlığı (tarayıcı sekmesi ve Google)',
				'seo_aciklama'   => array( 'Ana sayfa açıklaması (Google ve paylaşım önizlemesi)', 'textarea' ),
				'kurum_aciklama' => array( 'Kurum tanıtımı (yapay zekâ asistanları ve schema.org)', 'textarea' ),
				'sosyal'         => array( 'Sosyal medya adresleri (her satıra bir adres)', 'textarea' ),
			)
		);
	}
);
