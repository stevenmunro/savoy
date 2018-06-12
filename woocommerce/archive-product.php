<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.0
 NM: Modified */

defined( 'ABSPATH' ) || exit;

// Handle AJAX request
if ( isset( $_REQUEST['shop_load'] ) && nm_is_ajax_request() ) {
	if ( 'products' !== $_REQUEST['shop_load'] ) {
        // AJAX: Get full template for filter/search
        wc_get_template( 'ajax/shop-full.php' );
        exit;
	} else {
        // AJAX: Get products template for "infinite load"
        wc_get_template( 'ajax/shop-products.php' );
        exit;
	}
}

global $nm_theme_options, $nm_globals;

nm_add_page_include( 'products' );

// Action: woocommerce_before_main_content
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

// Action: woocommerce_before_main_content
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

// Product taxonomy
$is_product_taxonomy = ( is_product_taxonomy() ) ? true : false;

// Page content
if ( $is_product_taxonomy ) {
    $show_shop_page                 = ( $nm_theme_options['shop_content_taxonomy'] == 'shop_page' ) ? true : false;
    $show_taxonomy_header           = ( $nm_theme_options['shop_content_taxonomy'] == 'taxonomy_header' ) ? true : false;
    $hidden_taxonomy_description    = ( $show_taxonomy_header ) ? true : false;
    $show_taxonomy_description      = ( ! $show_taxonomy_header && $nm_theme_options['shop_category_description'] ) ? true : false;
} else {
    $show_shop_page                 = ( $nm_theme_options['shop_content_home'] ) ? true : false;
    $show_taxonomy_header           = false;
    $hidden_taxonomy_description    = false;
    $show_taxonomy_description      = ( $nm_theme_options['shop_category_description'] ) ? true : false;
}

// Shop header
$shop_class = ( $nm_theme_options['shop_header'] ) ? '' : 'header-disabled ';

// Sidebar/Filters
$show_filters_popup = false;
if ( $nm_theme_options['shop_filters'] == 'default' ) {
    nm_add_page_include( 'shop_filters' );

    $show_filters_sidebar  = true;
    $shop_class            .= 'nm-shop-sidebar-' . $nm_theme_options['shop_filters'] . ' nm-shop-sidebar-position-' . $nm_theme_options['shop_filters_sidebar_position'];
    $shop_column_size      = 'col-md-9 col-sm-12';
} else {
    $show_filters_sidebar  = false;
    $shop_class            .= 'nm-shop-sidebar-' . $nm_theme_options['shop_filters'];
    $shop_column_size      = 'col-xs-12';

    if ( $nm_theme_options['shop_filters'] == 'popup' ) {
        nm_add_page_include( 'shop_filters' );

        $show_filters_popup = true;
    }
}

// Image lazy-loading class
if ( $nm_theme_options['product_image_lazy_loading'] ) {
    $shop_class .= ' images-lazyload';
}

get_header( 'shop' ); ?>

<?php
    if ( $show_taxonomy_header ) :

        // Product taxonomy image
        $header_image_id    = get_woocommerce_term_meta( get_queried_object_id(), 'thumbnail_id', true );
        $header_image_url   = wp_get_attachment_url( $header_image_id );
        $header_image_class = $header_image_style_attr = '';
        if ( $header_image_url ) {
            $header_image_class         = ' has-image';
            $header_image_style_attr    = ' style="background-image: url(' . esc_url( $header_image_url ) . ');"';
        }

        $header_text_column_class = apply_filters( 'nm_category_header_column_class', 'col-xs-12 col-' . $nm_theme_options['shop_taxonomy_header_text_alignment'] );
?>

<div id="nm-shop-taxonomy-header" class="nm-shop-taxonomy-header<?php echo esc_attr( $header_image_class ); ?>">
    <div class="nm-shop-taxonomy-header-inner"<?php echo $header_image_style_attr; ?>>
        <div class="nm-shop-taxonomy-text align-<?php echo esc_attr( $nm_theme_options['shop_taxonomy_header_text_alignment'] ); ?>">
            <div class="nm-row">
                <div class="nm-shop-taxonomy-text-col <?php echo esc_attr( $header_text_column_class ); ?>">
                    <h1><?php woocommerce_page_title(); ?></h1>
                    <?php
                        /**
                         * woocommerce_archive_description hook
                         *
                         * @hooked woocommerce_taxonomy_archive_description - 10
                         * @hooked woocommerce_product_archive_description - 10
                         */
                        do_action( 'woocommerce_archive_description' );
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php elseif ( $hidden_taxonomy_description ) : ?>

<div class="nm-shop-hidden-taxonomy-content">
    <h1><?php woocommerce_page_title(); ?></h1>
    <?php
        // Hidden taxonomy description
        if ( ! $show_taxonomy_description ) {
            /**
             * woocommerce_archive_description hook
             *
             * @hooked woocommerce_taxonomy_archive_description - 10
             * @hooked woocommerce_product_archive_description - 10
             */
            do_action( 'woocommerce_archive_description' );
        }
    ?>
</div>

<?php endif; ?>

<?php
    // Note: Keep below "get_header()" (page not loading properly in some cases otherwise)
    $shop_page = ( $show_shop_page ) ? get_post( apply_filters( 'wpml_object_id', $nm_globals['shop_page_id'], 'page' ) ) : false; // WPML: The "wpml_object_id" filter is used to get the translated page (if created)

    if ( $shop_page ) :
?>

<div class="nm-page-full">
    <div class="entry-content">
        <?php
            $shop_page_content = apply_filters( 'the_content', $shop_page->post_content );
            echo $shop_page_content;
        ?>
    </div>
</div>

<?php endif; ?>

<div id="nm-shop" class="nm-shop <?php echo esc_attr( $shop_class ); ?>">
    <?php
        /**
         * Hook: woocommerce_before_main_content.
         *
         * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
         * @hooked woocommerce_breadcrumb - 20
         * @hooked WC_Structured_Data::generate_website_data() - 30
         */
        do_action( 'woocommerce_before_main_content' );
    ?>

    <?php 
        // Shop header
        if ( $nm_theme_options['shop_header'] ) {
            wc_get_template_part( 'content', 'product_nm_header' );
        }
    ?>

    <?php nm_print_shop_notices(); // Note: Don't remove (WooCommerce will output multiple messages otherwise) ?>

    <div id="nm-shop-products" class="nm-shop-products">
        <div class="nm-row">
            <?php 
                if ( $show_filters_sidebar ) {
                    /**
                     * woocommerce_sidebar hook.
                     *
                     * @hooked woocommerce_get_sidebar - 10
                     */
                    do_action( 'woocommerce_sidebar' );
                }
            ?>

            <div class="nm-shop-products-col <?php echo esc_attr( $shop_column_size ); ?>">
                <div id="nm-shop-products-overlay" class="nm-loader"></div>
                <div id="nm-shop-browse-wrap" class="nm-shop-description-<?php echo esc_attr( $nm_theme_options['shop_description_layout'] ); ?>">
                    <?php
                        // Results bar/button
                        wc_get_template_part( 'content', 'product_nm_results_bar' );
                    ?>

                    <?php
                        // Taxonomy description
                        if ( $show_taxonomy_description ) {
                            if ( $is_product_taxonomy ) {
                                /**
                                 * Hook: woocommerce_archive_description.
                                 *
                                 * @hooked woocommerce_taxonomy_archive_description - 10
                                 * @hooked woocommerce_product_archive_description - 10
                                 */
                                do_action( 'woocommerce_archive_description' );
                            } else if ( strlen( $nm_theme_options['shop_default_description'] ) > 0 && ! isset( $_REQUEST['s'] ) ) { // Don't display on search
                                // Default description
                                nm_shop_description( $nm_theme_options['shop_default_description'] );
                            }
                        }
                    ?>

                    <?php
                    if ( woocommerce_product_loop() ) {
                        /**
                         * Hook: woocommerce_before_shop_loop.
                         *
                         * @hooked wc_print_notices - 10
                         * @hooked woocommerce_result_count - 20
                         * @hooked woocommerce_catalog_ordering - 30
                         */
                        do_action( 'woocommerce_before_shop_loop' );

                        // Set column sizes
                        global $woocommerce_loop;
                        $woocommerce_loop['columns'] = $nm_theme_options['shop_columns'];
                        $woocommerce_loop['columns_small'] = '2';
                        $woocommerce_loop['columns_medium'] = '3';

                        woocommerce_product_loop_start();

                        $nm_globals['is_categories_shortcode'] = false;
                        
                        if ( wc_get_loop_prop( 'total' ) ) {
                            while ( have_posts() ) {
                                the_post();

                                // Note: Don't place in another template (image lazy-loading is only used in the Shop and WooCommerce shortcodes can use the other product templates)                 
                                $nm_globals['shop_image_lazy_loading'] = ( $nm_theme_options['product_image_lazy_loading'] ) ? true : false;

                                /**
                                 * Hook: woocommerce_shop_loop.
                                 *
                                 * @hooked WC_Structured_Data::generate_product_data() - 10
                                 */
                                do_action( 'woocommerce_shop_loop' );

                                wc_get_template_part( 'content', 'product' );
                            }
                        }

                        woocommerce_product_loop_end();

                        /**
                         * Hook: woocommerce_after_shop_loop.
                         *
                         * @hooked woocommerce_pagination - 10
                         */
                        do_action( 'woocommerce_after_shop_loop' );
                    } else {
                        /**
                         * Hook: woocommerce_no_products_found.
                         *
                         * @hooked wc_no_products_found - 10
                         */
                        do_action( 'woocommerce_no_products_found' );
                    }
                    ?>
                </div>
            </div>
        </div>

        <?php
            /**
             * Hook: woocommerce_after_main_content.
             *
             * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
             */
            do_action( 'woocommerce_after_main_content' );
        ?>
    </div>

    <?php
        // Sidebar/filters popup
        if ( $show_filters_popup ) {
            wc_get_template_part( 'content', 'product_nm_filters_popup' );
        }
    ?>

</div>

<?php
    /**
     * Hook: nm_after_shop hook.
     */
    do_action( 'nm_after_shop' );

    get_footer( 'shop' );
?>
