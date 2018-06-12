<?php
extract( shortcode_atts( array(
    'el_id'        		=> '',
    'content_placement' => '',
    'el_class'        	=> '',
    'css' 				=> '',
    // Custom params
    'type' 				=> 'full'
), $atts ) );

$output = $row_class = $row_flex_class = '';
$wrapper_atts = array();

// Custom ID
if ( ! empty( $el_id ) ) {
    $wrapper_atts[] = 'id="' . esc_attr( $el_id ) . '"';
}

// Custom class
$el_class = $this->getExtraClass( $el_class );

// Row class start
$row_class = 'nm-row nm-row-' . $type . ' inner';

// Row flexbox class: Content placement
if ( ! empty( $content_placement ) ) {
    $row_flex_class .= ' nm-row-col-' . $content_placement;
}
// Row flexbox class
if ( ! empty( $row_flex_class ) ) {
    $row_class .= ' nm-row-flex' . $row_flex_class;
}

// Row class end
$row_class .= ' ' . $el_class . vc_shortcode_custom_css_class( $css, ' ' );

// Class attribute
$wrapper_atts[] = 'class="' . apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $row_class, $this->settings['base'], $atts ) . '"';

// Output
$output .= '<div ' . implode( ' ', $wrapper_atts ) . '>';
$output .= wpb_js_remove_wpautop( $content );
$output .= '</div>';

echo $output;