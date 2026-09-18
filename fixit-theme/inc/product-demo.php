<?php
/**
 * Məhsul: demo ayarları və ekran görüntüləri qalereyası.
 *
 * Admin → Məhsullar → məhsulu açın → "Demo və ekran görüntüləri" qutusu.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Demo rejimləri.
 *
 * request — "Demonu sına" düyməsi saytdakı formaya aparır, girişi siz göndərirsiniz
 * open    — düymə birbaşa demo ünvanına aparır (ortaq, yalnız baxış üçün hesab)
 * off     — demo bölməsi göstərilmir
 */
function fixit_demo_modes() {
	return array(
		'request' => __( 'Sorğu ilə — müştəri formu doldurur, girişi biz göndəririk (tövsiyə)', 'fixit' ),
		'open'    => __( 'Açıq — düymə birbaşa demo ünvanına aparır', 'fixit' ),
		'off'     => __( 'Söndürülüb — demo bölməsi görünmür', 'fixit' ),
	);
}

function fixit_product_extra_box() {
	add_meta_box(
		'fixit_product_demo',
		__( 'Demo və ekran görüntüləri', 'fixit' ),
		'fixit_product_demo_box',
		'fixit_product',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'fixit_product_extra_box' );

/**
 * Media seçicisi yalnız məhsul redaktə səhifəsində yüklənir.
 */
function fixit_product_admin_assets( $hook ) {
	$screen = get_current_screen();
	if ( $screen && 'fixit_product' === $screen->post_type && in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		wp_enqueue_media();
		wp_enqueue_script(
			'fixit-admin-gallery',
			get_template_directory_uri() . '/assets/js/admin-gallery.js',
			array(),
			fixit_ver( 'assets/js/admin-gallery.js' ),
			true
		);
		wp_localize_script(
			'fixit-admin-gallery',
			'fixitGallery',
			array(
				'title'  => __( 'Ekran görüntüləri', 'fixit' ),
				'button' => __( 'Əlavə et', 'fixit' ),
				'remove' => __( 'Sil', 'fixit' ),
			)
		);
	}
}
add_action( 'admin_enqueue_scripts', 'fixit_product_admin_assets' );

function fixit_product_demo_box( $post ) {
	wp_nonce_field( 'fixit_save_demo', 'fixit_demo_nonce' );

	$demo    = fixit_product_demo( $post->ID );
	$gallery = fixit_product_gallery_ids( $post->ID );
	?>
	<style>
		.fixit-demo label{display:block;font-weight:600;margin:14px 0 5px}
		.fixit-demo input[type=url],.fixit-demo input[type=text],.fixit-demo select{width:100%;max-width:640px}
		.fixit-demo .hint{color:#666;font-size:12px;margin-top:3px}
		.fixit-gallery{display:flex;flex-wrap:wrap;gap:10px;margin:10px 0}
		.fixit-gallery figure{margin:0;position:relative;width:150px;height:100px;border:1px solid #dcdcde;border-radius:6px;overflow:hidden;background:#f6f7f7;cursor:move}
		.fixit-gallery img{width:100%;height:100%;object-fit:cover;display:block}
		.fixit-gallery button{position:absolute;top:4px;right:4px;border:0;border-radius:50%;width:22px;height:22px;line-height:20px;background:rgba(0,0,0,.65);color:#fff;cursor:pointer}
	</style>

	<div class="fixit-demo">
		<label for="fixit_demo_mode"><?php esc_html_e( 'Demo rejimi', 'fixit' ); ?></label>
		<select name="fixit_demo_mode" id="fixit_demo_mode">
			<?php foreach ( fixit_demo_modes() as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $demo['mode'], $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>

		<label for="fixit_demo_url"><?php esc_html_e( 'Demo ünvanı', 'fixit' ); ?></label>
		<input type="url" name="fixit_demo_url" id="fixit_demo_url" value="<?php echo esc_attr( $demo['url'] ); ?>" placeholder="https://ticket-demo.fixit.az">
		<p class="hint"><?php esc_html_e( '"Sorğu ilə" rejimində bu ünvan saytda görünmür — yalnız sizə gələn sorğu məktubunda xatırlatma kimi yazılır.', 'fixit' ); ?></p>

		<label for="fixit_demo_note"><?php esc_html_e( 'Açıq demo üçün qeyd', 'fixit' ); ?></label>
		<input type="text" name="fixit_demo_note" id="fixit_demo_note" value="<?php echo esc_attr( $demo['note'] ); ?>" placeholder="<?php esc_attr_e( 'Məs: Məlumatlar hər gecə sıfırlanır', 'fixit' ); ?>">
		<p class="hint"><?php esc_html_e( 'Yalnız "Açıq" rejimdə düymənin altında göstərilir.', 'fixit' ); ?></p>

		<label><?php esc_html_e( 'Ekran görüntüləri', 'fixit' ); ?></label>
		<p class="hint"><?php esc_html_e( 'Məhsul səhifəsində qalereya kimi göstərilir; ilk şəkil səhifənin yuxarısında da görünür. Sıranı sürüşdürərək dəyişə bilərsiniz. Real müştəri məlumatı olan ekran şəkli yükləməyin — demo məlumatlarından çəkin.', 'fixit' ); ?></p>

		<div class="fixit-gallery" id="fixit-gallery">
			<?php foreach ( $gallery as $img_id ) : ?>
				<figure data-id="<?php echo esc_attr( $img_id ); ?>" draggable="true">
					<?php echo wp_get_attachment_image( $img_id, 'medium' ); ?>
					<button type="button" class="fixit-gallery-remove" aria-label="<?php esc_attr_e( 'Sil', 'fixit' ); ?>">×</button>
				</figure>
			<?php endforeach; ?>
		</div>

		<input type="hidden" name="fixit_gallery" id="fixit_gallery_ids" value="<?php echo esc_attr( implode( ',', $gallery ) ); ?>">
		<button type="button" class="button" id="fixit-gallery-add"><?php esc_html_e( 'Şəkil əlavə et', 'fixit' ); ?></button>
	</div>
	<?php
}

/**
 * Demo ayarlarını və qalereyanı yadda saxlayır.
 */
function fixit_save_demo( $post_id ) {
	if ( ! isset( $_POST['fixit_demo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fixit_demo_nonce'] ) ), 'fixit_save_demo' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$mode = isset( $_POST['fixit_demo_mode'] ) ? sanitize_key( wp_unslash( $_POST['fixit_demo_mode'] ) ) : 'request';
	if ( ! array_key_exists( $mode, fixit_demo_modes() ) ) {
		$mode = 'request';
	}

	update_post_meta( $post_id, '_fixit_demo_mode', $mode );
	update_post_meta( $post_id, '_fixit_demo_url', isset( $_POST['fixit_demo_url'] ) ? esc_url_raw( wp_unslash( $_POST['fixit_demo_url'] ) ) : '' );
	update_post_meta( $post_id, '_fixit_demo_note', isset( $_POST['fixit_demo_note'] ) ? sanitize_text_field( wp_unslash( $_POST['fixit_demo_note'] ) ) : '' );

	$ids = isset( $_POST['fixit_gallery'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['fixit_gallery'] ) ) ) : array();
	$ids = array_values( array_filter( array_map( 'absint', $ids ) ) );
	update_post_meta( $post_id, '_fixit_gallery', implode( ',', $ids ) );
}
add_action( 'save_post_fixit_product', 'fixit_save_demo' );

/**
 * Qalereyadakı şəkil ID-ləri (yalnız mövcud şəkillər).
 *
 * @param int $post_id Məhsul.
 * @return int[]
 */
function fixit_product_gallery_ids( $post_id ) {
	$raw = (string) get_post_meta( $post_id, '_fixit_gallery', true );
	$ids = array_filter( array_map( 'absint', explode( ',', $raw ) ) );

	return array_values(
		array_filter(
			$ids,
			function ( $id ) {
				return wp_attachment_is_image( $id );
			}
		)
	);
}

/**
 * Məhsulun demo ayarları.
 *
 * @param int $post_id Məhsul.
 * @return array ['mode','url','note']
 */
function fixit_product_demo( $post_id ) {
	$mode = get_post_meta( $post_id, '_fixit_demo_mode', true );

	return array(
		'mode' => array_key_exists( $mode, fixit_demo_modes() ) ? $mode : 'request',
		'url'  => (string) get_post_meta( $post_id, '_fixit_demo_url', true ),
		'note' => (string) get_post_meta( $post_id, '_fixit_demo_note', true ),
	);
}
