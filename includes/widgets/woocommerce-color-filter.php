<?php
/**
 *	NM Widget: Color Filter List
 *
 *	Note: This is a modified version of the "WooCommerce Layered Nav" widget - All custom code is placed within "// NM" comments
 */

defined( 'ABSPATH' ) || exit;

class WC_Widget_Color_Filter extends WC_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_cssclass    = 'nm_widget nm_widget_color_filter woocommerce widget_layered_nav';
		$this->widget_description = __( 'Display a list of "color" attributes to filter products in your store.', 'nm-framework-admin' );
		$this->widget_id          = 'nm_woocommerce_color_filter';
		$this->widget_name        = __( 'Filter Products by Color', 'nm-framework-admin' );
		parent::__construct();
	}

	/**
	 * update function.
	 *
	 * @see WP_Widget->update
	 * @access public
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		// NM
		/*$this->init_settings();
		return parent::update( $new_instance, $old_instance );*/
		$instance = $old_instance;

		if ( empty( $new_instance['title'] ) ) {
			$new_instance['title'] = __( 'Color', 'nm-framework-admin' );
		}

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['columns'] = strip_tags( $new_instance['columns'] );
		$instance['attribute'] = stripslashes( $new_instance['attribute'] );
		$instance['query_type'] = stripslashes( $new_instance['query_type'] );
		$instance['colors'] = $new_instance['colors'];

		return $instance;
		// /NM
	}

	/**
	 * form function.
	 *
	 * @see WP_Widget->form
	 * @access public
	 * @param array $instance
	 * @return void
	 */
	public function form( $instance ) {
		// NM
		/*$this->init_settings();
		parent::form( $instance );*/
		$color_attribute_slug = apply_filters( 'nm_color_filter_slug', 'color' );
		
		$defaults = array(
			'title' 		=> '',
			'columns' 		=> '1',
			'attribute' 	=> $color_attribute_slug,
			'query_type'	=> 'and',
			'colors' 		=> ''
		);

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label>
				<?php esc_html_e( 'Title', 'nm-framework-admin' ); ?><br />
				<input class="widefat" type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			</label>
		</p>
        <p>
        	<label for="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>"><?php esc_html_e( 'Columns', 'nm-framework-admin' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'columns' ) ); ?>">
				<option value="1" <?php selected( $instance['columns'], '1' ); ?>><?php echo '1'; ?></option>
				<option value="2" <?php selected( $instance['columns'], '2' ); ?>><?php echo '2'; ?></option>
                <option value="small-2" <?php selected( $instance['columns'], 'small-2' ); ?>><?php echo '2 - On smaller browser sizes'; ?></option>
			</select>
		</p>
        <?php
		/* NM: This can be used to add support for multiple product attributes (it would also require AJAX replacing the terms when the select changes)
		<p>
        	<label for="<?php echo $this->get_field_id( 'attribute' ); ?>"><?php esc_html_e( 'Attribute', 'nm-framework-admin' ); ?></label>
            <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'attribute' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'attribute' ) ); ?>">
                <?php
					$attribute_taxonomies = wc_get_attribute_taxonomies();
					$options = '';
					
					if ( $attribute_taxonomies ) {
						foreach ( $attribute_taxonomies as $tax ) {
							if ( taxonomy_exists( wc_attribute_taxonomy_name( $tax->attribute_name ) ) ) {
								$options .= '<option name="' . $tax->attribute_name . '"' . selected( $tax->attribute_name, $instance['attribute'], false ) . '">' . $tax->attribute_name . '</option>';
							}
						}
					}
				
					echo $options;
				?>
            </select>
        </p>*/
        ?>
        <input type="hidden" id="<?php echo esc_attr( $this->get_field_id( 'attribute' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'attribute' ) ); ?>" value="<?php echo esc_attr( $color_attribute_slug ); ?>">
		<p>
        	<label for="<?php echo esc_attr( $this->get_field_id( 'query_type' ) ); ?>"><?php esc_html_e( 'Query type', 'woocommerce' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'query_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'query_type' ) ); ?>">
				<option value="and" <?php selected( $instance['query_type'], 'and' ); ?>><?php esc_html_e( 'AND', 'woocommerce' ); ?></option>
				<option value="or" <?php selected( $instance['query_type'], 'or' ); ?>><?php esc_html_e( 'OR', 'woocommerce' ); ?></option>
			</select>
		</p>
		<div class="nm-widget-attributes-table">
			<?php
				$terms = get_terms( 'pa_' . $instance['attribute'], array( 'hide_empty' => '0' ) );
							
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$id = 'widget-' . $this->id . '-';
					$name = 'widget-' . $this->id_base . '[' . $this->number . ']';
					$values = $instance['colors'];
					
					$output = sprintf( '<table><tr><th>%s</th><th>%s</th></tr>', esc_html__( 'Term', 'nm-framework-admin' ), esc_html__( 'Color', 'nm-framework-admin' ) );
					
					
					foreach ( $terms as $term ) {
						$id = $id . $term->term_id;
						
						$output .= '<tr>
							<td><label for="' . esc_attr( $id ) . '">' . esc_attr( $term->name ) . ' </label></td>
							<td><input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '[colors][' . esc_attr( $term->term_id ) . ']" value="' . ( isset( $values[$term->term_id] ) ? esc_attr( $values[$term->term_id] ) : '' ) . '" size="3" class="nm-widget-color-picker" /></td>
						</tr>';
					}
		
					$output .= '</table>';
					$output .= '<input type="hidden" name="' . esc_attr( $name ) . '[labels]" value="" />';
				} else {
					$output = '<span>No product attribute saved with the <strong>"' . $color_attribute_slug . '"</strong> slug yet. <br />Click <a href="http://docs.nordicmade.com/savoy/#shop-color-widget" target="_blank">here</a> for more info.</span>';
				}
			
				echo $output;
			?>
		</div>

		<input type="hidden" name="widget_id" value="widget-<?php echo esc_attr( $this->id ); ?>-" />
		<input type="hidden" name="widget_name" value="widget-<?php echo esc_attr( $this->id_base ); ?>[<?php echo esc_attr( $this->number ); ?>]" />
        <?php
		// /NM
	}

	/**
	 * Init settings after post types are registered
	 */
	// NM
	/*public function init_settings() {
		// Removed: Using custom options
	}*/
	// /NM

	/**
	 * widget function.
	 *
	 * @see WP_Widget
	 * @access public
	 * @param array $args
	 * @param array $instance
	 * @return void
	 */
	public function widget( $args, $instance ) {
		if ( ! is_shop() && ! is_product_taxonomy() ) {
			return;
		}

        $_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
		$taxonomy           = isset( $instance['attribute'] ) ? wc_attribute_taxonomy_name( $instance['attribute'] ) : $this->settings['attribute']['std'];
		$query_type         = isset( $instance['query_type'] ) ? $instance['query_type'] : $this->settings['query_type']['std'];
		// NM
        //$display_type       = isset( $instance['display_type'] ) ? $instance['display_type'] : $this->settings['display_type']['std'];
		$nm_args['columns'] = ( isset( $instance['columns'] ) ) ? $instance['columns'] : '1';
        $nm_args['colors'] = $instance['colors'];
        // /NM
        
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		$get_terms_args = array( 'hide_empty' => '1' );

		$orderby = wc_attribute_orderby( $taxonomy );

		switch ( $orderby ) {
			case 'name':
				$get_terms_args['orderby']    = 'name';
				$get_terms_args['menu_order'] = false;
                break;
			case 'id':
				$get_terms_args['orderby']    = 'id';
				$get_terms_args['order']      = 'ASC';
				$get_terms_args['menu_order'] = false;
                break;
			case 'menu_order':
				$get_terms_args['menu_order'] = 'ASC';
                break;
		}

		$terms = get_terms( $taxonomy, $get_terms_args );

		if ( 0 === count( $terms ) ) {
			return;
		}

		switch ( $orderby ) {
			case 'name_num':
				usort( $terms, '_wc_get_product_terms_name_num_usort_callback' );
                break;
			case 'parent':
				usort( $terms, '_wc_get_product_terms_parent_usort_callback' );
                break;
		}

		ob_start();

		$this->widget_start( $args, $instance );

        // NM
		/*if ( 'dropdown' === $display_type ) {
			wp_enqueue_script( 'selectWoo' );
			wp_enqueue_style( 'select2' );
            $found = $this->layered_nav_dropdown( $terms, $taxonomy, $query_type );
		} else {
			$found = $this->layered_nav_list( $terms, $taxonomy, $query_type );
		}*/
        $found = $this->layered_nav_list( $terms, $taxonomy, $query_type, $nm_args );
        // /NM

		$this->widget_end( $args );

		// Force found when option is selected - do not force found on taxonomy attributes
		if ( ! is_tax() && is_array( $_chosen_attributes ) && array_key_exists( $taxonomy, $_chosen_attributes ) ) {
			$found = true;
		}

		if ( ! $found ) {
			ob_end_clean();
		} else {
			echo ob_get_clean();
		}
	}
        
    /**
	 * Return the currently viewed taxonomy name.
	 * @return string
	 */
	// NM (unused function)
    /*protected function get_current_taxonomy() {
		return is_tax() ? get_queried_object()->taxonomy : '';
	}*/
    // /NM

	/**
	 * Return the currently viewed term ID.
	 * @return int
	 */
	protected function get_current_term_id() {
		return absint( is_tax() ? get_queried_object()->term_id : 0 );
	}

	/**
	 * Return the currently viewed term slug.
	 * @return int
	 */
	protected function get_current_term_slug() {
		return absint( is_tax() ? get_queried_object()->slug : 0 );
	}
    
    /**
	 * Show dropdown layered nav.
	 * @param  array $terms
	 * @param  string $taxonomy
	 * @param  string $query_type
	 * @return bool Will nav display?
	 */
    // NM (unused function)
	/*protected function layered_nav_dropdown( $terms, $taxonomy, $query_type ) {
		global $wp;
		$found = false;

		if ( $taxonomy !== $this->get_current_taxonomy() ) {
			$term_counts          = $this->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, $query_type );
			$_chosen_attributes   = WC_Query::get_layered_nav_chosen_attributes();
			$taxonomy_filter_name = str_replace( 'pa_', '', $taxonomy );
			$taxonomy_label       = wc_attribute_label( $taxonomy );
			$any_label            = apply_filters( 'woocommerce_layered_nav_any_label', sprintf( __( 'Any %s', 'woocommerce' ), $taxonomy_label ), $taxonomy_label, $taxonomy );
			$multiple             = 'or' === $query_type;
			$current_values       = isset( $_chosen_attributes[ $taxonomy ]['terms'] ) ? $_chosen_attributes[ $taxonomy ]['terms'] : array();

			if ( '' === get_option( 'permalink_structure' ) ) {
				$form_action = remove_query_arg( array( 'page', 'paged' ), add_query_arg( $wp->query_string, '', home_url( $wp->request ) ) );
			} else {
				$form_action = preg_replace( '%\/page/[0-9]+%', '', home_url( trailingslashit( $wp->request ) ) );
			}

			echo '<form method="get" action="' . esc_url( $form_action ) . '" class="woocommerce-widget-layered-nav-dropdown">';
			echo '<select class="woocommerce-widget-layered-nav-dropdown dropdown_layered_nav_' . esc_attr( $taxonomy_filter_name ) . '"' . ( $multiple ? 'multiple="multiple"' : '' ) . '>';
			echo '<option value="">' . esc_html( $any_label ) . '</option>';

			foreach ( $terms as $term ) {

				// If on a term page, skip that term in widget list
				if ( $term->term_id === $this->get_current_term_id() ) {
					continue;
				}

				// Get count based on current view
				$option_is_set  = in_array( $term->slug, $current_values );
				$count          = isset( $term_counts[ $term->term_id ] ) ? $term_counts[ $term->term_id ] : 0;

				// Only show options with count > 0
				if ( 0 < $count ) {
					$found = true;
				} elseif ( 0 === $count && ! $option_is_set ) {
					continue;
				}

				echo '<option value="' . esc_attr( $term->slug ) . '" ' . selected( $option_is_set, true, false ) . '>' . esc_html( $term->name ) . '</option>';
			}

			echo '</select>';

			if ( $multiple ) {
				echo '<input class="woocommerce-widget-layered-nav-dropdown__submit" type="submit" value="' . esc_attr__( 'Apply', 'woocommerce' ) . '" />';
			}

			if ( 'or' === $query_type ) {
				echo '<input type="hidden" name="query_type_' . esc_attr( $taxonomy_filter_name ) . '" value="or" />';
			}

			echo '<input type="hidden" name="filter_' . esc_attr( $taxonomy_filter_name ) . '" value="' . esc_attr( implode( ',', $current_values ) ) . '" />';
			echo wc_query_string_form_fields( null, array( 'filter_' . $taxonomy_filter_name, 'query_type_' . $taxonomy_filter_name ), '', true );
			echo '</form>';

			wc_enqueue_js( "
				// Update value on change.
				jQuery( '.dropdown_layered_nav_" . esc_js( $taxonomy_filter_name ) . "' ).change( function() {
					var slug = jQuery( this ).val();
					jQuery( ':input[name=\"filter_" . esc_js( $taxonomy_filter_name ) . "\"]' ).val( slug );

					// Submit form on change if standard dropdown.
					if ( ! jQuery( this ).attr( 'multiple' ) ) {
						jQuery( this ).closest( 'form' ).submit();
					}
				});

				// Use Select2 enhancement if possible
				if ( jQuery().selectWoo ) {
					var wc_layered_nav_select = function() {
						jQuery( '.dropdown_layered_nav_" . esc_js( $taxonomy_filter_name ) . "' ).selectWoo( {
							placeholder: '" . esc_html( $any_label ) . "',
							minimumResultsForSearch: 5,
							width: '100%'
						} );
					};
					wc_layered_nav_select();
				}
			" );
		}

		return $found;
	}*/
    // /NM
    
	/**
	 * Count products within certain terms, taking the main WP query into consideration.
	 *
	 * This query allows counts to be generated based on the viewed products, not all products.
	 *
	 * @param  array  $term_ids Term IDs.
	 * @param  string $taxonomy Taxonomy.
	 * @param  string $query_type Query Type.
	 * @return array
	 */
	protected function get_filtered_term_product_counts( $term_ids, $taxonomy, $query_type ) {
		global $wpdb;

		$tax_query  = WC_Query::get_main_tax_query();
		$meta_query = WC_Query::get_main_meta_query();

		if ( 'or' === $query_type ) {
			foreach ( $tax_query as $key => $query ) {
				if ( is_array( $query ) && $taxonomy === $query['taxonomy'] ) {
					unset( $tax_query[ $key ] );
				}
			}
		}

		$meta_query     = new WP_Meta_Query( $meta_query );
		$tax_query      = new WP_Tax_Query( $tax_query );
		$meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
		$tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );

		// Generate query.
		$query           = array();
		$query['select'] = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) as term_count, terms.term_id as term_count_id";
		$query['from']   = "FROM {$wpdb->posts}";
		$query['join']   = "
			INNER JOIN {$wpdb->term_relationships} AS term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id
			INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy USING( term_taxonomy_id )
			INNER JOIN {$wpdb->terms} AS terms USING( term_id )
			" . $tax_query_sql['join'] . $meta_query_sql['join'];

		$query['where'] = "
			WHERE {$wpdb->posts}.post_type IN ( 'product' )
			AND {$wpdb->posts}.post_status = 'publish'"
			. $tax_query_sql['where'] . $meta_query_sql['where'] .
			'AND terms.term_id IN (' . implode( ',', array_map( 'absint', $term_ids ) ) . ')';

		$search = WC_Query::get_main_search_query_sql();
		if ( $search ) {
			$query['where'] .= ' AND ' . $search;
		}

		$query['group_by'] = 'GROUP BY terms.term_id';
		$query             = apply_filters( 'woocommerce_get_filtered_term_product_counts_query', $query );
		$query             = implode( ' ', $query );

		// We have a query - let's see if cached results of this query already exist.
		$query_hash    = md5( $query );

		// Maybe store a transient of the count values.
		$cache = apply_filters( 'woocommerce_layered_nav_count_maybe_cache', true );
		if ( true === $cache ) {
			$cached_counts = (array) get_transient( 'wc_layered_nav_counts_' . $taxonomy );	
		} else {
			$cached_counts = array();
		}

		if ( ! isset( $cached_counts[ $query_hash ] ) ) {
			$results                      = $wpdb->get_results( $query, ARRAY_A ); // @codingStandardsIgnoreLine
			$counts                       = array_map( 'absint', wp_list_pluck( $results, 'term_count', 'term_count_id' ) );
			$cached_counts[ $query_hash ] = $counts;
			if ( true === $cache ) {
				set_transient( 'wc_layered_nav_counts_' . $taxonomy, $cached_counts, DAY_IN_SECONDS );
			}
		}

		return array_map( 'absint', (array) $cached_counts[ $query_hash ] );
	}

	/**
	 * Show list based layered nav.
	 * @param  array $terms
	 * @param  string $taxonomy
	 * @param  string $query_type
	 * @return bool Will nav display?
	 */
    // NM
	//protected function layered_nav_list( $terms, $taxonomy, $query_type ) {
    protected function layered_nav_list( $terms, $taxonomy, $query_type, $nm_args ) {
    // /NM
		// List display
        // NM
		//echo '<ul>';
        $columns_class = 'no-col';
        if ( $nm_args['columns'] !== '1' ) {
            $columns_class = ( $nm_args['columns'] === '2' ) ? 'small-block-grid-2 has-col' : 'small-block-grid-2 medium-block-grid-1 has-col';
        }
        echo '<ul class="' . $columns_class . '">';
        // /NM

		$term_counts        = $this->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, $query_type );
		$_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
		$found              = false;

		foreach ( $terms as $term ) {
			$current_values = isset( $_chosen_attributes[ $taxonomy ]['terms'] ) ? $_chosen_attributes[ $taxonomy ]['terms'] : array();
            $option_is_set  = in_array( $term->slug, $current_values, true );
			$count          = isset( $term_counts[ $term->term_id ] ) ? $term_counts[ $term->term_id ] : 0;

			// Skip the term for the current archive
			if ( $this->get_current_term_id() === $term->term_id ) {
				continue;
			}

			// Only show options with count > 0
			if ( 0 < $count ) {
				$found = true;
			} elseif ( 0 === $count && ! $option_is_set ) {
				continue;
			}

			$filter_name    = 'filter_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) );
            $current_filter = isset( $_GET[ $filter_name ] ) ? explode( ',', wc_clean( wp_unslash( $_GET[ $filter_name ] ) ) ) : array(); // WPCS: input var ok, CSRF ok.
			$current_filter = array_map( 'sanitize_title', $current_filter );
            
            if ( ! in_array( $term->slug, $current_filter, true ) ) {
				$current_filter[] = $term->slug;
			}
			
            $link = remove_query_arg( $filter_name, $this->get_current_page_url() );

			// Add current filters to URL.
			foreach ( $current_filter as $key => $value ) {
				// Exclude query arg for current term archive term
				if ( $value === $this->get_current_term_slug() ) {
					unset( $current_filter[ $key ] );
				}

				// Exclude self so filter can be unset on click.
				if ( $option_is_set && $value === $term->slug ) {
					unset( $current_filter[ $key ] );
				}
			}

			if ( ! empty( $current_filter ) ) {
                asort( $current_filter );
				$link = add_query_arg( $filter_name, implode( ',', $current_filter ), $link );

				// Add Query type Arg to URL.
				if ( 'or' === $query_type && ! ( 1 === count( $current_filter ) && $option_is_set ) ) {
					$link = add_query_arg( 'query_type_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) ), 'or', $link );
				}
				$link = str_replace( '%2C', ',', $link );
			}
            
            // NM
            $nm_color_val = isset( $nm_args['colors'][$term->term_id] ) ? $nm_args['colors'][$term->term_id] : '#e0e0e0';
            $nm_color_el = '<i style="background-color:' . esc_attr( $nm_color_val ) . ';" class="nm-filter-color nm-filter-color-' . esc_attr( strtolower( $term->slug ) ) . '"></i>';
            // /NM
            
			if ( $count > 0 || $option_is_set ) {
				$link      = esc_url( apply_filters( 'woocommerce_layered_nav_link', $link, $term, $taxonomy ) );
				// NM
                //$term_html = '<a href="' . $link . '">' . esc_html( $term->name ) . '</a>';
                $term_html = '<a href="' . $link . '">' . $nm_color_el . esc_html( $term->name ) . '</a>';
                // /NM
			} else {
				$link      = false;
				// NM
                //$term_html = '<span>' . esc_html( $term->name ) . '</span>';
                $term_html = '<span>' . $nm_color_el . esc_html( $term->name ) . '</span>';
                // /NM
			}
            
			$term_html .= ' ' . apply_filters( 'woocommerce_layered_nav_count', '<span class="count">(' . absint( $count ) . ')</span>', $count, $term );

            echo '<li class="woocommerce-widget-layered-nav-list__item wc-layered-nav-term ' . ( $option_is_set ? 'woocommerce-widget-layered-nav-list__item--chosen chosen' : '' ) . '">';
			echo wp_kses_post( apply_filters( 'woocommerce_layered_nav_term_html', $term_html, $term, $link, $count ) );
			echo '</li>';
		}

		echo '</ul>';

		return $found;
	}
}
