<?php
/**
 * Pagination - Show numbered pagination for catalog pages
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/pagination.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.3.1
 NM: Modified */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//global $wp_query, $nm_theme_options;
global $nm_theme_options;

$total   = isset( $total ) ? $total : wc_get_loop_prop( 'total_pages' );
$current = isset( $current ) ? $current : wc_get_loop_prop( 'current_page' );
$base    = isset( $base ) ? $base : esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) );
$format  = isset( $format ) ? $format : '';

if ( $total <= 1 ) {
	return;
}

// Enable infinite loading via URL query
// Note: This will not work with filters etc. (WooCommerce doesn't preserve all queries)
if ( isset( $_GET['infload'] ) && $_GET['infload'] === 'scroll' ) {
	$nm_theme_options['shop_infinite_load'] = 'scroll';
}

if ( $nm_theme_options['shop_infinite_load'] !== '0' ) {
	$infload = true;
	$infload_class = ' nm-infload';
} else {
	$infload = false;
	$infload_class = '';
}
?>
<nav class="woocommerce-pagination nm-pagination<?php echo $infload_class; ?>">
	<?php
		echo paginate_links( apply_filters( 'woocommerce_pagination_args', array(
			'base'         	=> esc_url( str_replace( 999999999, '%#%', remove_query_arg( array( 'add-to-cart', 'shop_load', '_', 'infload', 'ajax_filters' ), get_pagenum_link( 999999999, false ) ) ) ),
            'format'        => $format,
            'add_args'     => false,
            'current'      => max( 1, $current ),
            'total'         => $total,
			'prev_text'		=> '&larr;',
			'next_text'    	=> '&rarr;',
			'type'         	=> 'list',
			'end_size'     	=> 3,
			'mid_size'     	=> 3
		) ) );
	?>
</nav>

<?php if ( $infload ) : ?>
<div class="nm-infload-link"><?php next_posts_link( '&nbsp;' ); ?></div>

<div class="nm-infload-controls <?php echo esc_attr( $nm_theme_options['shop_infinite_load'] ); ?>-mode">
    <a href="#" class="nm-infload-btn"><?php esc_html_e( 'Load More', 'nm-framework' ); ?></a>
    
    <a href="#" class="nm-infload-to-top"><?php esc_html_e( 'All products loaded.', 'nm-framework' ); ?></a>
</div>
<?php endif; ?>
