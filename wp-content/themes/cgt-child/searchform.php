<?php
/**
 * Default search form template.
 *
 * @package CGT_Child
 */

$unique_id = esc_attr( uniqid( 'search-form-' ) );
?>

<form role="search" method="get" class="drawer-search search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="sr-only" for="<?php echo $unique_id; ?>"><?php esc_html_e( 'Rechercher', 'cgt' ); ?></label>
	<input type="search" id="<?php echo $unique_id; ?>" class="search-field" placeholder="<?php esc_attr_e( 'Je cherche un article…', 'cgt' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" />
	<button type="submit" class="btn btn-light search-submit"><?php esc_html_e( 'Rechercher', 'cgt' ); ?></button>
</form>
