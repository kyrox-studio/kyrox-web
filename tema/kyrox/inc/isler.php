<?php
/**
 * "İşler" içerik türü: ana sayfadaki iş kartları.
 * Başlık = kart başlığı, Özet = kart metni, Sayfa özellikleri > Sıra = gösterim sırası.
 */

defined( 'ABSPATH' ) || exit;

const KYROX_IS = 'kyrox_is';

/** Kart durumları: anahtar => [panelde görünen ad, kartta görünen metin, canlı mı]. */
function kyrox_is_durumlari(): array {
	return array(
		'kullanimda' => array( 'Kullanımda (link açılır)', 'Kullanımda ↗', true ),
		'tezgahta'   => array( 'Tezgâhta', 'Tezgâhta', false ),
		'yok'        => array( 'Durum gösterme', '', false ),
	);
}

add_action(
	'init',
	function () {
		register_post_type(
			KYROX_IS,
			array(
				'labels'              => array(
					'name'               => 'İşler',
					'singular_name'      => 'İş',
					'add_new'            => 'Yeni iş ekle',
					'add_new_item'       => 'Yeni iş ekle',
					'edit_item'          => 'İşi düzenle',
					'all_items'          => 'Tüm işler',
					'not_found'          => 'İş bulunamadı',
					'not_found_in_trash' => 'Çöp kutusunda iş yok',
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => true,
				'menu_position'       => 5,
				'menu_icon'           => 'dashicons-hammer',
				'supports'            => array( 'title', 'excerpt', 'thumbnail', 'page-attributes' ),
				'exclude_from_search' => true,
				'has_archive'         => false,
				'rewrite'             => false,
			)
		);
		foreach ( array( 'kyrox_tur', 'kyrox_durum', 'kyrox_link', 'kyrox_cizim' ) as $alan ) {
			register_post_meta(
				KYROX_IS,
				$alan,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'kyrox_link' === $alan ? 'esc_url_raw' : 'sanitize_text_field',
					'auth_callback'     => fn() => current_user_can( 'edit_posts' ),
				)
			);
		}
	}
);

// Kaydetme mesajları "Yazı" yerine "İş" desin.
add_filter(
	'post_updated_messages',
	function ( $mesajlar ) {
		$mesajlar[ KYROX_IS ] = array(
			1  => 'İş güncellendi.',
			4  => 'İş güncellendi.',
			6  => 'İş yayımlandı.',
			7  => 'İş kaydedildi.',
			8  => 'İş gönderildi.',
			10 => 'İş taslağı güncellendi.',
		);
		return $mesajlar;
	}
);

// Klasik düzenleyici: kart ayarları için basit bir kutu.
add_filter( 'use_block_editor_for_post_type', fn( $kullan, $tur ) => KYROX_IS === $tur ? false : $kullan, 10, 2 );

add_action(
	'add_meta_boxes',
	function () {
		add_meta_box( 'kyrox_is_kart', 'Kart ayarları', 'kyrox_is_kutu', KYROX_IS, 'normal', 'high' );
	}
);

function kyrox_is_kutu( WP_Post $is ): void {
	wp_nonce_field( 'kyrox_is_kaydet', 'kyrox_is_nonce' );
	$tur   = get_post_meta( $is->ID, 'kyrox_tur', true );
	$durum = get_post_meta( $is->ID, 'kyrox_durum', true ) ?: 'tezgahta';
	$link  = kyrox_is_adresi( $is );
	$cizim = get_post_meta( $is->ID, 'kyrox_cizim', true ) ?: 'yok';
	?>
	<p><label><strong>Tür</strong> (kartın üstündeki küçük etiket, ör. "Yazılım", "Kod + 3D")<br>
		<input type="text" name="kyrox_tur" value="<?php echo esc_attr( $tur ); ?>" class="widefat"></label></p>
	<p><label><strong>Durum</strong><br><select name="kyrox_durum">
		<?php foreach ( kyrox_is_durumlari() as $anahtar => $d ) : ?>
			<option value="<?php echo esc_attr( $anahtar ); ?>" <?php selected( $durum, $anahtar ); ?>><?php echo esc_html( $d[0] ); ?></option>
		<?php endforeach; ?>
	</select></label></p>
	<p><label><strong>Link</strong> (boşsa kart iş fişine gider)<br>
		<input type="url" name="kyrox_link" value="<?php echo esc_attr( $link ); ?>" class="widefat" placeholder="https://"></label></p>
	<p><label><strong>Çizim</strong><br><select name="kyrox_cizim">
		<?php foreach ( kyrox_cizim_secenekleri() as $anahtar => $ad ) : ?>
			<option value="<?php echo esc_attr( $anahtar ); ?>" <?php selected( $cizim, $anahtar ); ?>><?php echo esc_html( $ad ); ?></option>
		<?php endforeach; ?>
	</select></label></p>
	<p class="description">Kart metni için "Özet" kutusunu kullanın. Sırayı sağdaki "Sayfa özellikleri &gt; Sıra" belirler (küçük olan önce). Öne çıkan görsel yalnız "Çizim yok" seçiliyse gösterilir; ürün fotoğrafı yerine teknik çizim tercih edilir.</p>
	<?php
}

add_action(
	'save_post_' . KYROX_IS,
	function ( int $is_id ) {
		if ( ! isset( $_POST['kyrox_is_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['kyrox_is_nonce'] ) ), 'kyrox_is_kaydet' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $is_id ) ) {
			return;
		}
		$durum = sanitize_key( wp_unslash( $_POST['kyrox_durum'] ?? '' ) );
		$cizim = sanitize_key( wp_unslash( $_POST['kyrox_cizim'] ?? '' ) );
		update_post_meta( $is_id, 'kyrox_tur', sanitize_text_field( wp_unslash( $_POST['kyrox_tur'] ?? '' ) ) );
		update_post_meta( $is_id, 'kyrox_durum', array_key_exists( $durum, kyrox_is_durumlari() ) ? $durum : 'tezgahta' );
		update_post_meta( $is_id, 'kyrox_link', esc_url_raw( wp_unslash( $_POST['kyrox_link'] ?? '' ) ) );
		update_post_meta( $is_id, 'kyrox_cizim', array_key_exists( $cizim, kyrox_cizim_secenekleri() ) ? $cizim : 'yok' );
	}
);

// Yönetim listesinde sıra ve durum sütunları.
add_filter(
	'manage_' . KYROX_IS . '_posts_columns',
	fn( $s ) => array_merge(
		array_slice( $s, 0, 2 ),
		array(
			'kyrox_sira'  => 'Sıra',
			'kyrox_durum' => 'Durum',
		),
		array_slice( $s, 2 )
	)
);
add_action(
	'manage_' . KYROX_IS . '_posts_custom_column',
	function ( $sutun, $is_id ) {
		if ( 'kyrox_sira' === $sutun ) {
			echo (int) get_post_field( 'menu_order', $is_id );
		}
		if ( 'kyrox_durum' === $sutun ) {
			$d = kyrox_is_durumlari()[ get_post_meta( $is_id, 'kyrox_durum', true ) ] ?? null;
			echo esc_html( $d ? $d[0] : '' );
		}
	},
	10,
	2
);
add_action(
	'pre_get_posts',
	function ( WP_Query $q ) {
		if ( is_admin() && $q->is_main_query() && KYROX_IS === $q->get( 'post_type' ) && ! $q->get( 'orderby' ) ) {
			$q->set( 'orderby', 'menu_order' );
			$q->set( 'order', 'ASC' );
		}
	}
);

/** Ana sayfada gösterilecek işler. */
function kyrox_isler(): array {
	return get_posts(
		array(
			'post_type'      => KYROX_IS,
			'post_status'    => 'publish',
			'posts_per_page' => 12,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'ASC',
			),
		)
	);
}

/** Başlangıç ürün kartlarını güncel hedefe bağlar; özel adresleri korur. */
function kyrox_is_adresi( WP_Post $is ): string {
	$link = (string) get_post_meta( $is->ID, 'kyrox_link', true );
	if ( 'Fuar CRM' === get_the_title( $is )
		&& 'ekran' === get_post_meta( $is->ID, 'kyrox_cizim', true )
		&& 'https://fuar.kyrox.studio' === untrailingslashit( $link ) ) {
		$sayfa = get_page_by_path( 'fuar-crm' );
		if ( $sayfa && 'publish' === get_post_status( $sayfa ) ) {
			return get_permalink( $sayfa );
		}
	}
	if ( 'Fuar Stand' === get_the_title( $is )
		&& 'stand' === get_post_meta( $is->ID, 'kyrox_cizim', true )
		&& in_array( untrailingslashit( $link ), array( 'https://fuar.kyrox.studio', 'https://fuar.kyrox.studio/stand-projects' ), true ) ) {
		$sayfa = get_page_by_path( 'fuar-stand' );
		if ( $sayfa && 'publish' === get_post_status( $sayfa ) ) {
			return get_permalink( $sayfa );
		}
		return 'https://fuar.kyrox.studio/stand-projects';
	}
	return $link;
}

/** Bir iş kartının HTML'i. */
function kyrox_is_karti( WP_Post $is ): void {
	$tur    = get_post_meta( $is->ID, 'kyrox_tur', true );
	$durum  = kyrox_is_durumlari()[ get_post_meta( $is->ID, 'kyrox_durum', true ) ] ?? kyrox_is_durumlari()['yok'];
	$link   = kyrox_is_adresi( $is );
	$cizim  = get_post_meta( $is->ID, 'kyrox_cizim', true ) ?: 'yok';
	$dis    = (bool) $link && wp_parse_url( $link, PHP_URL_HOST ) && wp_parse_url( $link, PHP_URL_HOST ) !== wp_parse_url( home_url( '/' ), PHP_URL_HOST );
	$href   = $link ?: '#iletisim';
	$gorsel = 'yok' === $cizim
		? get_the_post_thumbnail( $is, 'large', array( 'loading' => 'lazy' ) )
		: kyrox_cizim( $cizim );
	printf(
		'<a class="work" href="%1$s"%2$s><div class="vis">%3$s</div><div class="wbody">%4$s<h3>%5$s</h3>%6$s%7$s</div></a>',
		esc_url( $href ),
		$dis ? ' target="_blank" rel="noopener noreferrer"' : '',
		$gorsel, // phpcs:ignore WordPress.Security.EscapeOutput -- sabit SVG ya da WordPress görsel etiketi.
		$tur ? '<span class="kind">' . esc_html( $tur ) . '</span>' : '',
		esc_html( get_the_title( $is ) ),
		has_excerpt( $is ) ? '<p>' . esc_html( wp_strip_all_tags( $is->post_excerpt ) ) . '</p>' : '',
		$durum[1] ? '<span class="wstate' . ( $durum[2] ? ' live' : '' ) . '">' . esc_html( $durum[1] ) . '</span>' : ''
	);
}
