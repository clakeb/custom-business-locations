<?php
/**
 * Frontend functions
 *
 * @package CustomBusinessLocations
 * @category Frontend
 * @author  Anchor Studios
 * @version 1.0.0
 * @since 1.0.0
 */

function cbl_frontend_scripts() {
    global $custom_business_locations;
    $path = $custom_business_locations->plugin_url();
    wp_deregister_script('jquery');
    wp_register_script('jquery', "http" . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . "://ajax.googleapis.com/ajax/libs/jquery/2.0.1/jquery.min.js", false, null);
    wp_enqueue_script('jquery');
    wp_register_script( 'map-api', 'https://maps.googleapis.com/maps/api/js?key='.$custom_business_locations->api_key.'&sensor=true', 'jquery' );
    wp_enqueue_script( 'map-api' );
    wp_register_script( 'foundation', $path . '/library/js/foundation.js', 'jquery' );
    wp_enqueue_script( 'foundation' );
    wp_register_script( 'foundation-section', $path . '/library/js/foundation.section.js', 'foundation' );
    wp_enqueue_script( 'foundation-section' );
    wp_register_script( 'cbl-jquery-frontend', $path . '/library/js/frontend.js', 'foundation-section' );
    wp_enqueue_script( 'cbl-jquery-frontend' );
    wp_register_style( 'cbl-frontend', $path . '/library/css/frontend.css' );
    wp_enqueue_style( 'cbl-frontend' );
}
add_action( 'wp_enqueue_scripts', 'cbl_frontend_scripts' );