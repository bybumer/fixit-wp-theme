<?php
/**
 * Xüsusi məzmun növləri: Xidmətlər və Müştəri rəyləri.
 *
 * Beləliklə xidmətlər və rəylər admin paneldən (kod açmadan) idarə olunur.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
   1. Post növlərinin qeydiyyatı
   ============================================================ */
function fixit_register_post_types() {

	register_post_type(
		'fixit_service',
		array(
			'labels'        => array(
				'name'               => __( 'Xidmətlər', 'fixit' ),
				'singular_name'      => __( 'Xidmət', 'fixit' ),
				'add_new'            => __( 'Yeni xidmət', 'fixit' ),
				'add_new_item'       => __( 'Yeni xidmət əlavə et', 'fixit' ),
				'edit_item'          => __( 'Xidməti redaktə et', 'fixit' ),
				'search_items'       => __( 'Xidmətlərdə axtar', 'fixit' ),
				'not_found'          => __( 'Xidmət tapılmadı', 'fixit' ),
				'menu_name'          => __( 'Xidmətlər', 'fixit' ),
			),
			'public'        => true,
			'has_archive'   => false,
			'menu_icon'     => 'dashicons-shield-alt',
			'menu_position' => 21,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
			'rewrite'       => array( 'slug' => 'xidmet', 'with_front' => false ),
			'show_in_rest'  => true,
		)
	);

	register_post_type(
		'fixit_product',
		array(
			'labels'        => array(
				'name'          => __( 'Məhsullar', 'fixit' ),
				'singular_name' => __( 'Məhsul', 'fixit' ),
				'add_new'       => __( 'Yeni məhsul', 'fixit' ),
				'add_new_item'  => __( 'Yeni məhsul əlavə et', 'fixit' ),
				'edit_item'     => __( 'Məhsulu redaktə et', 'fixit' ),
				'not_found'     => __( 'Məhsul tapılmadı', 'fixit' ),
				'menu_name'     => __( 'Məhsullar', 'fixit' ),
			),
			'public'        => true,
			'has_archive'   => false,
			'menu_icon'     => 'dashicons-archive',
			'menu_position' => 20,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
			'rewrite'       => array( 'slug' => 'mehsul', 'with_front' => false ),
			'show_in_rest'  => true,
		)
	);

	register_post_type(
		'fixit_review',
		array(
			'labels'        => array(
				'name'          => __( 'Müştəri rəyləri', 'fixit' ),
				'singular_name' => __( 'Rəy', 'fixit' ),
				'add_new'       => __( 'Yeni rəy', 'fixit' ),
				'add_new_item'  => __( 'Yeni rəy əlavə et', 'fixit' ),
				'edit_item'     => __( 'Rəyi redaktə et', 'fixit' ),
				'menu_name'     => __( 'Müştəri rəyləri', 'fixit' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'menu_icon'     => 'dashicons-format-quote',
			'menu_position' => 22,
			'supports'      => array( 'title', 'editor', 'page-attributes' ),
		)
	);
}
add_action( 'init', 'fixit_register_post_types' );

/* ============================================================
   2. Meta qutuları
   ============================================================ */
function fixit_add_meta_boxes() {
	add_meta_box(
		'fixit_service_meta',
		__( 'Xidmət detalları', 'fixit' ),
		'fixit_service_meta_box',
		'fixit_service',
		'normal',
		'high'
	);

	// Məhsullar xidmətlərlə eyni sahələrdən istifadə edir.
	add_meta_box(
		'fixit_service_meta',
		__( 'Məhsul detalları', 'fixit' ),
		'fixit_service_meta_box',
		'fixit_product',
		'normal',
		'high'
	);

	add_meta_box(
		'fixit_review_meta',
		__( 'Rəy detalları', 'fixit' ),
		'fixit_review_meta_box',
		'fixit_review',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'fixit_add_meta_boxes' );

/**
 * Xidmət meta qutusu.
 */
function fixit_service_meta_box( $post ) {
	wp_nonce_field( 'fixit_save_meta', 'fixit_meta_nonce' );

	$icon     = get_post_meta( $post->ID, '_fixit_icon', true );
	$short    = get_post_meta( $post->ID, '_fixit_short', true );
	$features = get_post_meta( $post->ID, '_fixit_features', true );
	$chips    = get_post_meta( $post->ID, '_fixit_chips', true );
	$icons    = array_keys( fixit_icon_list() );
	?>
	<style>
		.fixit-meta label{display:block;font-weight:600;margin:14px 0 5px}
		.fixit-meta input[type=text],.fixit-meta textarea,.fixit-meta select{width:100%;max-width:640px}
		.fixit-meta textarea{min-height:90px}
		.fixit-meta .hint{color:#666;font-size:12px;margin-top:3px}
	</style>
	<div class="fixit-meta">
		<label for="fixit_icon"><?php esc_html_e( 'İkon', 'fixit' ); ?></label>
		<select name="fixit_icon" id="fixit_icon">
			<?php foreach ( $icons as $ic ) : ?>
				<option value="<?php echo esc_attr( $ic ); ?>" <?php selected( $icon, $ic ); ?>><?php echo esc_html( $ic ); ?></option>
			<?php endforeach; ?>
		</select>
		<p class="hint"><?php esc_html_e( 'Tövsiyə: computer, camera, cloud, mail, phone, support, shield, server, network, lock', 'fixit' ); ?></p>

		<label for="fixit_short"><?php esc_html_e( 'Qısa təsvir (kartlarda görünür)', 'fixit' ); ?></label>
		<textarea name="fixit_short" id="fixit_short"><?php echo esc_textarea( $short ); ?></textarea>

		<label for="fixit_features"><?php esc_html_e( 'Üstünlüklər (hər sətirdə bir ədəd)', 'fixit' ); ?></label>
		<textarea name="fixit_features" id="fixit_features"><?php echo esc_textarea( $features ); ?></textarea>

		<label for="fixit_chips"><?php esc_html_e( 'Teqlər (vergüllə ayırın)', 'fixit' ); ?></label>
		<input type="text" name="fixit_chips" id="fixit_chips" value="<?php echo esc_attr( $chips ); ?>">
		<p class="hint"><?php esc_html_e( 'Məsələn: 3CX, Yeastar, Hikvision', 'fixit' ); ?></p>
	</div>
	<?php
}

/**
 * Rəy meta qutusu.
 */
function fixit_review_meta_box( $post ) {
	wp_nonce_field( 'fixit_save_meta', 'fixit_meta_nonce' );

	$author = get_post_meta( $post->ID, '_fixit_author', true );
	$city   = get_post_meta( $post->ID, '_fixit_city', true );
	$rating = get_post_meta( $post->ID, '_fixit_rating', true );
	$rating = $rating ? (int) $rating : 5;
	?>
	<div class="fixit-meta">
		<label for="fixit_author"><?php esc_html_e( 'Müştərinin adı', 'fixit' ); ?></label>
		<input type="text" name="fixit_author" id="fixit_author" value="<?php echo esc_attr( $author ); ?>">

		<label for="fixit_city"><?php esc_html_e( 'Şəhər / şirkət', 'fixit' ); ?></label>
		<input type="text" name="fixit_city" id="fixit_city" value="<?php echo esc_attr( $city ); ?>">

		<label for="fixit_rating"><?php esc_html_e( 'Reytinq (1–5)', 'fixit' ); ?></label>
		<select name="fixit_rating" id="fixit_rating">
			<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
				<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $rating, $i ); ?>><?php echo esc_html( $i ); ?></option>
			<?php endfor; ?>
		</select>
	</div>
	<?php
}

/* ============================================================
   3. Meta məlumatların yadda saxlanması
   ============================================================ */
function fixit_save_meta( $post_id ) {
	if ( ! isset( $_POST['fixit_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fixit_meta_nonce'] ) ), 'fixit_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	/*
	 * Bu yazı əl ilə redaktə olundu — tema yeniləndikdə başlanğıc mətn
	 * bir daha onun üstündən yazılmayacaq.
	 */
	update_post_meta( $post_id, '_fixit_user_edited', 1 );

	$text_fields = array( 'fixit_icon', 'fixit_chips', 'fixit_author', 'fixit_city', 'fixit_rating' );
	foreach ( $text_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, '_' . $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}

	$area_fields = array( 'fixit_short', 'fixit_features' );
	foreach ( $area_fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, '_' . $field, sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
}
add_action( 'save_post', 'fixit_save_meta' );

/* ============================================================
   4. Şablonlar üçün köməkçilər
   ============================================================ */

/**
 * Xidmətləri gətirir.
 */
function fixit_get_services( $limit = -1 ) {
	return get_posts(
		array(
			'post_type'      => 'fixit_service',
			'posts_per_page' => $limit,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		)
	);
}

/**
 * Məhsulları gətirir.
 */
function fixit_get_products( $limit = -1 ) {
	return get_posts(
		array(
			'post_type'      => 'fixit_product',
			'posts_per_page' => $limit,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		)
	);
}

/**
 * Rəyləri gətirir.
 */
function fixit_get_reviews( $limit = 3 ) {
	return get_posts(
		array(
			'post_type'      => 'fixit_review',
			'posts_per_page' => $limit,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		)
	);
}

/**
 * Sətirlərə bölünmüş meta dəyəri massiv kimi qaytarır.
 */
function fixit_meta_lines( $post_id, $key ) {
	$raw = get_post_meta( $post_id, $key, true );
	if ( ! $raw ) {
		return array();
	}
	$lines = preg_split( '/\r\n|\r|\n/', $raw );
	return array_values( array_filter( array_map( 'trim', $lines ) ) );
}
