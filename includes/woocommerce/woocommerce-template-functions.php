<?php
	
	/* WooCommerce template functins
	=============================================================== */
	
	global $nm_theme_options, $nm_globals;
    
    
    
    /*
	 *	Show shop notices
	 */
	function nm_print_shop_notices() {
		echo '<div id="nm-shop-notices-wrap">';
		  wc_print_notices();
		echo '</div>';
	}
    
    
    
    /*
     * Get my-account/login link
     */
	function nm_get_myaccount_link( $is_header = true ) {
		global $nm_theme_options;
		
		$myaccount_url = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );
		
		// Link title/icon
		if ( $is_header && $nm_theme_options['menu_login_icon'] ) {
            $link_title = apply_filters( 'nm_myaccount_icon', '<i class="nm-myaccount-icon nm-font nm-font-user"></i>', 'nm-font nm-font-user' );
		} else {
			$link_title = ( is_user_logged_in() ) ? esc_html__( 'My Account', 'nm-framework' ) : esc_html__( 'Login', 'woocommerce' );
		}
		
		return '<a href="' . esc_url( $myaccount_url ) . '" id="nm-menu-account-btn">' . apply_filters( 'nm_myaccount_title', $link_title ) . '</a>';
	}
    
    
    
    /*
     * Get cart title/icon
     */
	function nm_get_cart_title() {
		global $nm_theme_options;
		
		if ( $nm_theme_options['menu_cart_icon'] ) {
            $cart_title = apply_filters( 'nm_cart_icon', '<i class="nm-menu-cart-icon nm-font nm-font-shopping-cart"></i>', 'nm-font nm-font-shopping-cart' );
		} else {
			$cart_title = '<span class="nm-menu-cart-title">' . esc_html__( 'Cart', 'nm-framework' ) . '</span>';
		}
		
		return $cart_title;
	}
    
    
    
    /*
	 *	Display default Shop description
     *
     *  Code from "woocommerce_taxonomy_archive_description()" function
	 */
	if ( ! function_exists( 'nm_shop_description' ) ) {
        function nm_shop_description( $description ) {
            $description = wc_format_content( $description );
            if ( $description ) {
                echo '<div class="nm-shop-default-description term-description">' . $description . '</div>';
            }
        }
    }
    
    
    
    /*
	 *	Category menu: Create single category list HTML 
	 */
	if ( ! function_exists( 'nm_category_menu_create_list' ) ) {
        function nm_category_menu_create_list( $category, $current_cat_id, $categories_menu_divider, $current_top_cat_id = null ) {
            $output = '<li class="cat-item-' . $category->term_id;
            
            // Is this the current category?
            if ( $current_cat_id == $category->term_id ) {
                $output .= ' current-cat';
            }
            // Is this the current top parent-category?
            else if ( $current_top_cat_id && $current_top_cat_id == $category->term_id ) {
                $output .= ' current-parent-cat';
            }

            $output .=  '">' . $categories_menu_divider . '<a href="' . esc_url( get_term_link( (int) $category->term_id, 'product_cat' ) ) . '">' . esc_attr( $category->name ) . '</a></li>';

            return $output;
        }
    }
	
	
	
	/*
	 *	Product category menu
	 */
	if ( ! function_exists( 'nm_category_menu' ) ) {
        function nm_category_menu() {
            global $wp_query, $nm_theme_options;

            $current_cat_id = ( is_tax( 'product_cat' ) ) ? $wp_query->queried_object->term_id : '';
            $is_category = ( strlen( $current_cat_id ) > 0 ) ? true : false;
            $hide_empty = ( $nm_theme_options['shop_categories_hide_empty'] ) ? true : false;
            
            // Should top-level categories be displayed?
            if ( $nm_theme_options['shop_categories_top_level'] == '0' && $is_category ) {
                nm_sub_category_menu_output( $current_cat_id, $hide_empty );
            } else {
                nm_category_menu_output( $is_category, $current_cat_id, $hide_empty );
            }
        }
    }
	
		
	
	/*
	 *	Product category menu: Output
	 */
	if ( ! function_exists( 'nm_category_menu_output' ) ) {
        function nm_category_menu_output( $is_category, $current_cat_id, $hide_empty ) {
            global $wp_query, $nm_theme_options;

            $page_id = wc_get_page_id( 'shop' );
            $page_url = get_permalink( $page_id );
            $hide_sub = true;
            $current_top_cat_id = null;
            $all_categories_class = '';

            // Is this a category page?																
            if ( $is_category ) {
                $hide_sub = false;
                
                // Get current category's top-parent id
                $current_cat_parents = get_ancestors( $current_cat_id, 'product_cat' );
                if ( ! empty( $current_cat_parents ) ) {
                    $current_top_cat_id = end( $current_cat_parents ); // Get last item from array
                }

                // Get current category's direct children
                $current_cat_direct_children = get_terms( 'product_cat',
                    array(
                        'fields'       	=> 'ids',
                        'parent'       	=> $current_cat_id,
                        'hierarchical'	=> true,
                        'hide_empty'   	=> $hide_empty
                    )
                );
                $category_has_children = ( empty( $current_cat_direct_children ) ) ? false : true;
            } else {
                // No current category, set "All" as current (if not product tag archive or search)
                if ( ! is_product_tag() && ! isset( $_REQUEST['s'] ) ) {
                    $all_categories_class = ' class="current-cat"';
                }
            }

            $output_cat = '<li' . $all_categories_class . '><a href="' . esc_url ( $page_url ) . '">' . esc_html__( 'All', 'nm-framework' ) . '</a></li>';
            $output_sub_cat = '';
            $output_current_sub_cat = '';

            // Categories order
            $orderby = 'slug';
            $order = 'asc';
            if ( isset( $nm_theme_options['shop_categories_orderby'] ) ) {
                $orderby = $nm_theme_options['shop_categories_orderby'];
                $order = $nm_theme_options['shop_categories_order'];
            }

            $categories = get_categories( array(
                'type'			=> 'post',
                'orderby'		=> $orderby, // Note: 'name' sorts by product category "menu/sort order"
                'order'			=> $order,
                'hide_empty'	=> $hide_empty,
                'hierarchical'	=> 1,
                'taxonomy'		=> 'product_cat'
            ) );
            
            // Categories menu divider
            $categories_menu_divider = apply_filters( 'nm_shop_categories_divider', '<span>&frasl;</span>' );

            foreach( $categories as $category ) {
                // Is this a sub-category?
                if ( $category->parent != '0' ) {
                    // Should sub-categories be included?
                    if ( $hide_sub ) {
                        continue; // Skip to next loop item
                    } else {
                        if ( 
                            $category->parent == $current_cat_id || // Include current sub-category's children
                            ! $category_has_children && $category->parent == $wp_query->queried_object->parent // Include categories with the same parent (if current sub-category doesn't have children)
                        ) {
                            $output_sub_cat .= nm_category_menu_create_list( $category, $current_cat_id, $categories_menu_divider );
                        } else if ( 
                            $category->term_id == $current_cat_id // Include current sub-category (save in a separate variable so it can be appended to the start of the category list)
                        ) {
                            $output_current_sub_cat = nm_category_menu_create_list( $category, $current_cat_id, $categories_menu_divider );
                        }
                    }
                } else {
                    $output_cat .= nm_category_menu_create_list( $category, $current_cat_id, $categories_menu_divider, $current_top_cat_id );
                }
            }

            if ( strlen( $output_sub_cat ) > 0 ) {
                $output_sub_cat = '<ul class="nm-shop-sub-categories">' . $output_current_sub_cat . $output_sub_cat . '</ul>';
            }

            $output = $output_cat . $output_sub_cat;

            echo $output;
        }
    }
	
	
	
	/*
	 *	Product sub-category menu: Get "Back" link
	 */
	if ( ! function_exists( 'nm_sub_category_menu_back_link' ) ) {
        function nm_sub_category_menu_back_link( $url, $categories_menu_divider, $class = '' ) {
            return '<li class="nm-category-back-button' . esc_attr( $class ) . '"><a href="' . esc_url( $url ) . '"><i class="nm-font nm-font-arrow-left"></i> ' . esc_html__( 'Back', 'nm-framework' ) . '</a>' . $categories_menu_divider . '</li>';
        }
    }
	
	
	
	/*
	 *	Product category menu: Output sub-categories
	 */
	if ( ! function_exists( 'nm_sub_category_menu_output' ) ) {
        function nm_sub_category_menu_output( $current_cat_id, $hide_empty ) {
            global $wp_query, $nm_theme_options;

            // Categories menu divider
            $categories_menu_divider = apply_filters( 'nm_shop_categories_divider', '<span>&frasl;</span>' );

            $output_sub_categories = '';

            // Categories order
            $orderby = 'slug';
            $order = 'asc';
            if ( isset( $nm_theme_options['shop_categories_orderby'] ) ) {
                $orderby = $nm_theme_options['shop_categories_orderby'];
                $order = $nm_theme_options['shop_categories_order'];
            }

            $sub_categories = get_categories( array(
                'type'			=> 'post',
                'parent'       	=> $current_cat_id,
                'orderby'		=> $orderby, // Note: 'name' sorts by product category "menu/sort order"
                'order'			=> $order,
                'hide_empty'	=> $hide_empty,
                'hierarchical'	=> 1,
                'taxonomy'		=> 'product_cat'
            ) );

            $has_sub_categories = ( empty( $sub_categories ) ) ? false : true;

            // Is there any sub-categories available
            if ( $has_sub_categories ) {
                //$current_cat_name = __( 'All', 'nm-framework' );
                $current_cat_name = apply_filters( 'nm_shop_parent_category_title', $wp_query->queried_object->name );

                foreach( $sub_categories as $sub_category ) {
                    $output_sub_categories .= nm_category_menu_create_list( $sub_category, $current_cat_id, $categories_menu_divider );
                }
            } else {
                $current_cat_name = $wp_query->queried_object->name;
            }

            // "Back" link
            $output_back_link = '';
            if ( $nm_theme_options['shop_categories_back_link'] ) {
                $parent_cat_id = $wp_query->queried_object->parent;

                if ( $parent_cat_id ) {
                    // Back to parent-category link
                    $parent_cat_url = get_term_link( (int) $parent_cat_id, 'product_cat' );
                    $output_back_link = nm_sub_category_menu_back_link( $parent_cat_url, $categories_menu_divider );
                } else if ( $nm_theme_options['shop_categories_back_link'] == '1st' ) {
                    // 1st sub-level - Back to top-level (main shop page) link
                    $shop_page_id = wc_get_page_id( 'shop' );
                    $shop_url = get_permalink( $shop_page_id );
                    $output_back_link = nm_sub_category_menu_back_link( $shop_url, $categories_menu_divider, ' 1st-level' );
                }
            }

            // Current category link
            $current_cat_url = get_term_link( (int) $current_cat_id, 'product_cat' );
            $output_current_cat = '<li class="current-cat"><a href="' . esc_url( $current_cat_url ) . '">' . esc_html( $current_cat_name ) . '</a></li>';

            echo $output_back_link . $output_current_cat . $output_sub_categories;
        }
    }
    
    
    
    /*
	 * Shop: Get product thumbnail/image
     * 
     * Note: Modified version of the "woocommerce_get_product_thumbnail()" function in "../wp-content/plugins/woocommerce/includes/wc-template-functions.php"
	 */
    if ( ! function_exists( 'woocommerce_get_product_thumbnail' ) ) {
        function woocommerce_get_product_thumbnail( $size = 'woocommerce_thumbnail', $deprecated1 = 0, $deprecated2 = 0 ) {
            global $product, $nm_theme_options, $nm_globals;
            
            if ( $nm_theme_options['product_image_lazy_loading'] ) {
                $product_thumbnail = '';
                
                $image_id = get_post_thumbnail_id();
                $image_size = apply_filters( 'single_product_archive_thumbnail_size', $size );

                if ( $image_id ) {
                    $props = nm_product_get_thumbnail_props( $image_id, $image_size );

                    if ( strlen( $props['src'] ) > 0 ) { // Make sure the image isn't deleted
                        $product_thumbnail = sprintf( '<img src="%s" data-src="%s" data-srcset="%s" alt="%s" sizes="%s" width="%s" height="%s" class="nm-shop-hover-image attachment-woocommerce_thumbnail size-%s wp-post-image lazyload" />',
                            esc_url( $nm_globals['product_placeholder_image'] ),
                            $props['src'],
                            $props['srcset'],
                            $props['alt'],
                            $props['sizes'],
                            esc_attr( $props['src_w'] ),
                            esc_attr( $props['src_h'] ),
                            $image_size
                        );
                    }
                }

                return $product_thumbnail;
            } else {
                $image_size = apply_filters( 'single_product_archive_thumbnail_size', $size );

                return $product ? $product->get_image( $image_size ) : '';
            }
        }
    }
    
    
    
    /*
	 * Shop (product loop): Get thumbnail/image properties
     *
     * * Note: Modified version of the "wc_get_product_attachment_props()" function in "../wp-content/plugins/woocommerce/includes/wc-product-functions.php"
	 */
    function nm_product_get_thumbnail_props( $attachment_id = null, $thumbnail_size = 'woocommerce_thumbnail' ) {
        $props = array(
            'title'   => '',
            'alt'     => '',
            'src'     => '',
            'srcset'  => false,
            'sizes'   => false,
        );
        if ( $attachment = get_post( $attachment_id ) ) {
            $props['title']   = trim( strip_tags( $attachment->post_title ) );
            $props['alt']     = trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) );
            
            $src             = wp_get_attachment_image_src( $attachment_id, $thumbnail_size );
            $props['src']    = $src[0];
            $props['src_w']  = $src[1];
            $props['src_h']  = $src[2];
            $props['srcset'] = function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $attachment_id, $thumbnail_size ) : false;
            $props['sizes']  = function_exists( 'wp_get_attachment_image_sizes' ) ? wp_get_attachment_image_sizes( $attachment_id, $thumbnail_size ) : false;
        }
        return $props;
    }
    
    
    
    // Note: Can be used if you need to get the alternative/hover thumbnail-id separately
    /*
	 * Shop (product loop): Get alternative/hover image id
	 */
    /*function nm_product_thumbnail_alt_id( $product ) {
        $product_gallery_thumbnail_ids = $product->get_gallery_image_ids();

        if ( $product_gallery_thumbnail_ids ) {
            $product_thumbnail_alt_id = reset( $product_gallery_thumbnail_ids ); // Get first gallery image id

            return $product_thumbnail_alt_id;
        }

        return null;
    }*/
    
    
    
    /*
	 * Shop (product loop): Get alternative/hover image
	 */
    if ( ! function_exists( 'nm_product_thumbnail_alt' ) ) {
        function nm_product_thumbnail_alt( $product ) {
            //$product_thumbnail_alt_id = nm_product_thumbnail_alt_id( $product );
            $product_gallery_thumbnail_ids = $product->get_gallery_image_ids();
            $product_thumbnail_alt_id = ( $product_gallery_thumbnail_ids ) ? reset( $product_gallery_thumbnail_ids ) : null; // Get first gallery image id

            if ( $product_thumbnail_alt_id ) {
                $product_thumbnail_alt_src = wp_get_attachment_image_src( $product_thumbnail_alt_id, 'woocommerce_thumbnail' );

                // Make sure the first image is found (deleted image id's can can still be assigned to the gallery)
                if ( $product_thumbnail_alt_src ) {
                    return '<img src="' . esc_url( NM_THEME_URI . '/img/transparent.gif' ) . '" data-src="' . esc_url( $product_thumbnail_alt_src[0] ) . '" width="' . esc_attr( $product_thumbnail_alt_src[1] ) . '" height="' . esc_attr( $product_thumbnail_alt_src[2] ) . '" class="attachment-woocommerce_thumbnail hover-image" />';
                }
            }

            return '';
        }
    }
    
    
    
    /*
	 *	Output product-variations list
	 */
    function nm_product_variations_list( $product ) {
        // Note: Code from "woocommerce_variable_add_to_cart()" function
        //$get_variations         = sizeof( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
        //$available_variations   = $get_variations ? $product->get_available_variations() : false;
        $available_variations   = $product->get_available_variations();
        $attributes             = $product->get_variation_attributes();
        
        // Note: Code from "../savoy/woocommerce/single-product/add-to-cart/variable.php" template
        if ( ! empty( $available_variations ) ) :
        ?>
        <ul class="nm-variations-list">
            <?php foreach ( $attributes as $attribute_name => $options ) : ?>
                <li>
                    <div class="label"><?php echo wc_attribute_label( $attribute_name ); ?>:</div>
                    <div class="values">
                        <?php
                            // Note: Code from "wc_dropdown_variation_attribute_options()" function
                            if ( ! empty( $options ) ) {
                                if ( taxonomy_exists( $attribute_name ) ) {
                                    $terms = wc_get_product_terms( $product->get_id(), $attribute_name, array( 'fields' => 'all' ) );

                                    foreach ( $terms as $term ) {
                                        if ( in_array( $term->slug, $options ) ) {
                                            echo '<span>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) ) . '</span>';
                                        }
                                    }
                                }
                            }
                        ?>
                    </div>
                </li>
            <?php endforeach;?>
        </ul>
        <?php
        endif;
    }
    