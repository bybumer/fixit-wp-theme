<?php
/**
 * Axtarış formu.
 *
 * @package FIXIT
 */

?>
<form role="search" method="get" class="field" action="<?php echo esc_url( home_url( '/' ) ); ?>" style="display:flex;gap:10px;margin:0">
	<label class="screen-reader-text" for="fixit-search"><?php esc_html_e( 'Axtar', 'fixit' ); ?></label>
	<input type="search" id="fixit-search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Axtarış…', 'fixit' ); ?>">
	<button class="btn btn--primary" type="submit"><?php fixit_icon( 'search' ); ?><span class="screen-reader-text"><?php esc_html_e( 'Axtar', 'fixit' ); ?></span></button>
</form>
