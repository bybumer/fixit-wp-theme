<?php
/**
 * Bazadakı məzmunun tərcüməsi (məhsullar, xidmətlər, rəylər, səhifə adları).
 *
 * Tərcümələr `_fixit_i18n_en` və `_fixit_i18n_ru` meta sahələrində saxlanılır.
 * Başlanğıc tərcümələr kodda (inc/i18n-data.php) olur və relizlə birlikdə
 * sayta düşür — yəni yeni dil üçün əl ilə məzmun yazmaq lazım gəlmir.
 *
 * Admin paneldə hər məhsul/xidmət/rəy üçün "Tərcümələr" qutusu var:
 * orada yazılan mətn kodun üstündədir və tema yeniləndikdə pozulmur.
 *
 * @package FIXIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tərcümə sahələri.
 */
function fixit_i18n_fields() {
	return array(
		'title'    => __( 'Başlıq', 'fixit' ),
		'short'    => __( 'Qısa təsvir', 'fixit' ),
		'body'     => __( 'Mətn', 'fixit' ),
		'features' => __( 'Üstünlüklər (hər sətirdə bir ədəd)', 'fixit' ),
		'chips'    => __( 'Teqlər (vergüllə)', 'fixit' ),
	);
}

/**
 * Yazının müəyyən dildəki tərcümə dəsti.
 *
 * @param int    $post_id Yazı.
 * @param string $lang    Dil (boşdursa hazırkı).
 * @return array
 */
function fixit_i18n_get( $post_id, $lang = '' ) {
	$lang = $lang ? $lang : fixit_lang();

	if ( fixit_default_lang() === $lang ) {
		return array();
	}

	$data = get_post_meta( $post_id, '_fixit_i18n_' . $lang, true );

	return is_array( $data ) ? $data : array();
}

/**
 * Tərcümə dəstini yazır.
 *
 * @param int    $post_id Yazı.
 * @param string $lang    Dil.
 * @param array  $data    Sahələr.
 */
function fixit_i18n_set( $post_id, $lang, $data ) {
	$clean = array();

	foreach ( array_keys( fixit_i18n_fields() ) as $field ) {
		if ( ! empty( $data[ $field ] ) ) {
			$clean[ $field ] = is_string( $data[ $field ] ) ? trim( $data[ $field ] ) : $data[ $field ];
		}
	}

	if ( $clean ) {
		update_post_meta( $post_id, '_fixit_i18n_' . $lang, $clean );
	} else {
		delete_post_meta( $post_id, '_fixit_i18n_' . $lang );
	}
}

/**
 * Bir sahənin tərcüməsi; yoxdursa əsas dildəki mətn qaytarılır.
 *
 * @param int    $post_id  Yazı.
 * @param string $field    title|short|body|features|chips.
 * @param string $fallback Əsas dildəki dəyər.
 */
function fixit_i18n_field( $post_id, $field, $fallback ) {
	$data = fixit_i18n_get( $post_id );

	return ( isset( $data[ $field ] ) && '' !== $data[ $field ] ) ? $data[ $field ] : $fallback;
}

/* ============================================================
   Şablonlar üçün qısa funksiyalar
   ============================================================ */

/**
 * Yazının başlığı (tərcümə ilə).
 *
 * @param WP_Post|int $post Yazı.
 */
function fixit_t_title( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}

	return fixit_i18n_field( $post->ID, 'title', $post->post_title );
}

/**
 * Yazının mətni (tərcümə ilə).
 *
 * @param WP_Post|int $post Yazı.
 */
function fixit_t_content( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}

	return fixit_i18n_field( $post->ID, 'body', $post->post_content );
}

/**
 * Qısa təsvir (_fixit_short, yoxdursa excerpt).
 *
 * @param WP_Post|int $post Yazı.
 */
function fixit_t_short( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}

	$short = get_post_meta( $post->ID, '_fixit_short', true );
	$short = $short ? $short : $post->post_excerpt;

	return fixit_i18n_field( $post->ID, 'short', $short );
}

/**
 * Üstünlüklər siyahısı (tərcümə ilə).
 *
 * @param WP_Post|int $post Yazı.
 */
function fixit_t_features( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return array();
	}

	$raw = fixit_i18n_field( $post->ID, 'features', (string) get_post_meta( $post->ID, '_fixit_features', true ) );

	$lines = preg_split( '/\r\n|\r|\n/', (string) $raw );

	return array_values( array_filter( array_map( 'trim', $lines ) ) );
}

/**
 * Teqlər (tərcümə ilə).
 *
 * @param WP_Post|int $post Yazı.
 */
function fixit_t_chips( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return array();
	}

	$raw = fixit_i18n_field( $post->ID, 'chips', (string) get_post_meta( $post->ID, '_fixit_chips', true ) );

	return array_values( array_filter( array_map( 'trim', explode( ',', (string) $raw ) ) ) );
}

/* ============================================================
   Başlıqlar: səhifə adları, menyu, sənəd başlığı
   ============================================================ */

/**
 * Ön üzdə göstərilən başlıqları tərcümə edir.
 *
 * Səhifə və menyu adları bazadan gəlir; onların tərcüməsi
 * inc/i18n-data.php-dəki cədvəldə saxlanılır.
 *
 * @param string   $title   Başlıq.
 * @param int|null $post_id Yazı.
 */
function fixit_translate_title( $title, $post_id = null ) {
	if ( is_admin() || fixit_is_default_lang() || '' === $title ) {
		return $title;
	}

	// Yazının öz tərcüməsi varsa, o üstündür.
	if ( $post_id ) {
		$data = fixit_i18n_get( $post_id );
		if ( ! empty( $data['title'] ) ) {
			return $data['title'];
		}
	}

	$map = fixit_title_translations();
	$key = trim( $title );

	return isset( $map[ $key ] ) ? $map[ $key ] : $title;
}
add_filter( 'the_title', 'fixit_translate_title', 10, 2 );

/**
 * Menyu bəndlərinin adları.
 *
 * @param array $items Menyu bəndləri.
 */
function fixit_translate_menu_items( $items ) {
	if ( is_admin() || fixit_is_default_lang() ) {
		return $items;
	}

	foreach ( $items as $item ) {
		$item->title = fixit_translate_title( $item->title, ( 'post_type' === $item->type ) ? (int) $item->object_id : null );
	}

	return $items;
}
add_filter( 'wp_nav_menu_objects', 'fixit_translate_menu_items' );

/**
 * Brauzerin başlıq sətri.
 *
 * @param array $parts Hissələr.
 */
function fixit_translate_document_title( $parts ) {
	if ( fixit_is_default_lang() ) {
		return $parts;
	}

	if ( ! empty( $parts['title'] ) ) {
		$parts['title'] = fixit_translate_title( $parts['title'], is_singular() ? get_queried_object_id() : null );
	}
	if ( ! empty( $parts['site'] ) ) {
		$parts['site'] = fixit_translate_title( $parts['site'] );
	}
	if ( ! empty( $parts['tagline'] ) ) {
		$parts['tagline'] = fixit_translate_title( $parts['tagline'] );
	}

	return $parts;
}
add_filter( 'document_title_parts', 'fixit_translate_document_title', 5 );

/**
 * Sayt adı və şüar (loqonun alt mətni, paylaşım kartları və s.).
 *
 * @param string $output Dəyər.
 * @param string $show   Hansı sahə.
 */
function fixit_translate_bloginfo( $output, $show = '' ) {
	if ( in_array( $show, array( 'name', 'description' ), true ) ) {
		return fixit_translate_title( $output );
	}
	return $output;
}
add_filter( 'bloginfo', 'fixit_translate_bloginfo', 10, 2 );

/**
 * Xam sayt adı/şüar (get_bloginfo( 'name' ) filtrsiz çağırılanda).
 *
 * @param string $value Dəyər.
 */
function fixit_translate_blog_option( $value ) {
	return fixit_translate_title( (string) $value );
}
add_filter( 'option_blogname', 'fixit_translate_blog_option' );
add_filter( 'option_blogdescription', 'fixit_translate_blog_option' );

/* ============================================================
   Admin: tərcümə qutusu
   ============================================================ */

/**
 * Tərcümə qutusu hansı növlərdə görünsün.
 */
function fixit_i18n_post_types() {
	return array( 'fixit_product', 'fixit_service', 'fixit_review', 'page' );
}

function fixit_i18n_add_meta_box() {
	foreach ( fixit_i18n_post_types() as $type ) {
		add_meta_box(
			'fixit_i18n_box',
			__( 'Tərcümələr (EN / RU)', 'fixit' ),
			'fixit_i18n_meta_box',
			$type,
			'normal',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'fixit_i18n_add_meta_box' );

/**
 * Tərcümə qutusu.
 *
 * @param WP_Post $post Yazı.
 */
function fixit_i18n_meta_box( $post ) {
	wp_nonce_field( 'fixit_save_i18n', 'fixit_i18n_nonce' );

	$langs  = fixit_langs();
	$fields = fixit_i18n_fields();

	// Səhifələr üçün yalnız başlıq lazımdır.
	if ( 'page' === $post->post_type ) {
		$fields = array( 'title' => $fields['title'] );
	}
	?>
	<style>
		.fixit-i18n .tabs{display:flex;gap:6px;margin-bottom:14px}
		.fixit-i18n .tabs button{padding:6px 16px;border:1px solid #dcdcde;background:#f6f7f7;border-radius:6px;cursor:pointer;font-weight:600}
		.fixit-i18n .tabs button.is-on{background:#0b63e5;border-color:#0b63e5;color:#fff}
		.fixit-i18n .pane{display:none}
		.fixit-i18n .pane.is-on{display:block}
		.fixit-i18n label{display:block;font-weight:600;margin:12px 0 4px}
		.fixit-i18n input[type=text],.fixit-i18n textarea{width:100%;max-width:680px}
		.fixit-i18n textarea{min-height:80px}
		.fixit-i18n .hint{color:#666;font-size:12px;margin:4px 0 0}
	</style>

	<div class="fixit-i18n">
		<p class="hint">
			<?php esc_html_e( 'Boş qoyulan sahə üçün Azərbaycanca mətn göstərilir. Burada yazdığınız mətn tema yeniləndikdə pozulmur.', 'fixit' ); ?>
		</p>

		<div class="tabs">
			<?php
			$first = true;
			foreach ( $langs as $code => $info ) :
				if ( fixit_default_lang() === $code ) {
					continue;
				}
				?>
				<button type="button" data-pane="fixit-pane-<?php echo esc_attr( $code ); ?>" class="<?php echo $first ? 'is-on' : ''; ?>">
					<?php echo esc_html( $info['name'] ); ?>
				</button>
				<?php
				$first = false;
			endforeach;
			?>
		</div>

		<?php
		$first = true;
		foreach ( $langs as $code => $info ) :
			if ( fixit_default_lang() === $code ) {
				continue;
			}
			$data = fixit_i18n_get( $post->ID, $code );
			?>
			<div class="pane <?php echo $first ? 'is-on' : ''; ?>" id="fixit-pane-<?php echo esc_attr( $code ); ?>">
				<?php foreach ( $fields as $field => $label ) : ?>
					<label for="fixit_i18n_<?php echo esc_attr( $code . '_' . $field ); ?>">
						<?php echo esc_html( $label ); ?>
					</label>
					<?php if ( in_array( $field, array( 'body', 'features' ), true ) ) : ?>
						<textarea
							id="fixit_i18n_<?php echo esc_attr( $code . '_' . $field ); ?>"
							name="fixit_i18n[<?php echo esc_attr( $code ); ?>][<?php echo esc_attr( $field ); ?>]"
						><?php echo esc_textarea( isset( $data[ $field ] ) ? $data[ $field ] : '' ); ?></textarea>
					<?php else : ?>
						<input type="text"
							id="fixit_i18n_<?php echo esc_attr( $code . '_' . $field ); ?>"
							name="fixit_i18n[<?php echo esc_attr( $code ); ?>][<?php echo esc_attr( $field ); ?>]"
							value="<?php echo esc_attr( isset( $data[ $field ] ) ? $data[ $field ] : '' ); ?>">
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<?php
			$first = false;
		endforeach;
		?>
	</div>

	<script>
	( function () {
		var box = document.querySelector( '.fixit-i18n' );
		if ( ! box ) { return; }
		box.querySelectorAll( '.tabs button' ).forEach( function ( b ) {
			b.addEventListener( 'click', function () {
				box.querySelectorAll( '.tabs button' ).forEach( function ( x ) { x.classList.remove( 'is-on' ); } );
				box.querySelectorAll( '.pane' ).forEach( function ( x ) { x.classList.remove( 'is-on' ); } );
				b.classList.add( 'is-on' );
				var pane = document.getElementById( b.dataset.pane );
				if ( pane ) { pane.classList.add( 'is-on' ); }
			} );
		} );
	} )();
	</script>
	<?php
}

/**
 * Tərcümələri yadda saxlayır.
 *
 * @param int $post_id Yazı.
 */
function fixit_save_i18n( $post_id ) {
	if ( ! isset( $_POST['fixit_i18n_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fixit_i18n_nonce'] ) ), 'fixit_save_i18n' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( empty( $_POST['fixit_i18n'] ) || ! is_array( $_POST['fixit_i18n'] ) ) {
		return;
	}

	$raw = wp_unslash( $_POST['fixit_i18n'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	foreach ( array_keys( fixit_langs() ) as $code ) {
		if ( fixit_default_lang() === $code || empty( $raw[ $code ] ) ) {
			continue;
		}

		$clean = array();
		foreach ( array_keys( fixit_i18n_fields() ) as $field ) {
			if ( ! isset( $raw[ $code ][ $field ] ) ) {
				continue;
			}
			$value = $raw[ $code ][ $field ];
			$clean[ $field ] = in_array( $field, array( 'body', 'features' ), true )
				? sanitize_textarea_field( $value )
				: sanitize_text_field( $value );
		}

		fixit_i18n_set( $post_id, $code, $clean );
		update_post_meta( $post_id, '_fixit_i18n_edited_' . $code, 1 );
	}
}
add_action( 'save_post', 'fixit_save_i18n' );

/* ============================================================
   Başlanğıc tərcümələrin tətbiqi
   ============================================================ */

/**
 * Kodda saxlanılan tərcümələri yazılara yazır.
 *
 * Admin paneldə əl ilə redaktə edilmiş tərcüməyə toxunulmur.
 */
function fixit_apply_seed_translations() {
	$seed = fixit_content_translations();

	foreach ( array( 'fixit_product', 'fixit_service', 'fixit_review' ) as $type ) {
		$posts = get_posts(
			array(
				'post_type'      => $type,
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);

		foreach ( $posts as $post ) {
			$key = $post->post_title;
			if ( empty( $seed[ $key ] ) ) {
				continue;
			}

			foreach ( $seed[ $key ] as $lang => $fields ) {
				if ( get_post_meta( $post->ID, '_fixit_i18n_edited_' . $lang, true ) ) {
					continue; // Əl ilə redaktə olunub.
				}
				fixit_i18n_set( $post->ID, $lang, $fields );
			}
		}
	}
}
