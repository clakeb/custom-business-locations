<?php
/*
Plugin Name: Custom Business Locations
Plugin URI: http://anchor.is/plugins/custom-business-locations
Description: Add your business's locations and display a custom Google map with a shortcode.
Version: 1.1.1
Author: Anchor Studios
Author URI: http://anchor.is/

Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : PLUGIN AUTHOR EMAIL)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'CBL_Plugin' ) ) {

class CBL_Plugin {

    /**
     * @var string
     **/
    public $version = '1.1.1';

    /**
     * @var string
     **/
    public $plugin_path;

    /**
     * @var string
     **/
    public $plugin_url;

    /**
     * @var CBL_PLugin_Settings
     **/
    public $settings;

    /**
     * CBL_Plugin Constructor
     *
     * @access public
     * @return void
     */
    public function __construct() {

        // Auto-load classes on demand
        if ( function_exists( "__autoload" ) ) {
            spl_autoload_register( "__autoload" );
        }
        spl_autoload_register( array( $this, 'autoload' ) );

        // // Define version constant
        define( 'CBL_PLUGIN_VERSION', $this->version );

        // Updates
        add_action( 'admin_init', array( $this, 'update' ), 5 );

        // Installation
        register_activation_hook( __FILE__, array( $this, 'activate' ) );

        // Uninstall
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        // Load plugin
        add_action( 'init', array( $this, 'init' ) );
    }

    /**
     * Init plugin when WordPress initialized
     *
     * @access public
     * @return void
     */
    public function init() {

        // Do pre-init action
        do_action( 'cbl_plugin_before_init' );

        // Register the settings
        $this->settings = new CBL_Plugin_Settings;

        // Dependencies
        $this->dependencies();

        // Create shortcode
        add_shortcode( 'cbl-map', array( $this, 'shortcode_callback' ) );

        // Do init action
        do_action( 'cbl_plugin_init' );
    }

    /**
     * Activate function
     *
     * @access public
     *
     * @return void
     */
    public function activate() {
    }

    /**
     * Deactivate function
     *
     * @access public
     * @return void
     */
    public function deactivate() {
    }

    /**
     * update function.
     *
     * @access public
     * @return void
     */
    public function update() {
        $version = get_option( 'cbl_plugin_version' );
        new CBL_Plugin_Updater( $version, $this->version );
        return;
    }

    /**
     * Auto-load CBL classes on demand to reduce memory consumption
     *
     * @access public
     * @param mixed $class
     * @return void
     */
    public function autoload( $class ) {
        $class = strtolower( $class );
        if ( strpos( $class, 'cbl_plugin_' ) === 0 ) {
            $path = $this->plugin_path() . '/library/php/classes/';
            $file = 'class-' . str_replace( '_', '-', $class ) . '.php';
            if ( is_readable( $path . $file ) ) {
                include_once( $path . $file );
                return;
            }
        }
    }

    /**
     * Get the plugin url
     *
     * @access public
     * @return string
     */
    public function plugin_url() {
        if ( $this->plugin_url ) return $this->plugin_url;

        return $this->plugin_url = untrailingslashit( plugins_url( '/', __FILE__ ) );
    }

    /**
     * Get the plugin path
     *
     * @access public
     * @return string
     */
    public function plugin_path() {
        if ( $this->plugin_path ) return $this->plugin_path;

        return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
     * Include dependencies
     *
     * Example: Scripts, Styles, return value functions, libraries, etc.
     *
     * @access public
     * @return string
     */
    public function dependencies() {
        $path = $this->plugin_path();
        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
        } else {
            add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
        }
    }

    /**
     * Enqueue admin scripts
     *
     * @access public
     * @return string
     */
    public function admin_scripts() {
        $path = $this->plugin_url();
        $api_key = $this->settings->options['api_key'];

        if ( !$_GET['page'] == 'custom_business_locations')
            return

        // Javascript
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_register_script( 'cbl-google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&sensor=true', array( 'jquery' ), false, true );
        wp_enqueue_script( 'cbl-google-maps' );
        wp_register_script( 'foundation', $path . '/library/js/foundation.min.js', array( 'jquery' ), false, true );
        wp_enqueue_script( 'foundation' );
        wp_register_script( 'foundation-section', $path . '/library/js/foundation.section.min.js', array( 'jquery', 'foundation' ), false, true );
        wp_enqueue_script( 'foundation-section' );
        wp_register_script( 'cbl-plugin-admin', $path . '/library/js/admin.min.js', array( 'jquery', 'foundation', 'foundation-section' ), false, true );
        wp_enqueue_script( 'cbl-plugin-admin' );

        // CSS
        wp_register_style( 'cbl-plugin-admin', $path . '/library/css/admin.css' );
        wp_enqueue_style( 'cbl-plugin-admin' );
    }

    /**
     * Enqueue frontend scripts
     *
     * @access public
     * @return string
     */
    public function frontend_scripts() {
        $path = $this->plugin_url();
        $api_key = $this->settings->options['api_key'];

        // Javascript
        wp_enqueue_script( 'jquery' );
        wp_register_script( 'cbl-google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&sensor=true', array( 'jquery' ), false, true );
        wp_enqueue_script( 'cbl-google-maps' );
        wp_register_script( 'foundation', $path . '/library/js/foundation.min.js', array( 'jquery' ), false, true );
        wp_enqueue_script( 'foundation' );
        wp_register_script( 'foundation-section', $path . '/library/js/foundation.section.min.js', array( 'jquery', 'foundation' ), false, true );
        wp_enqueue_script( 'foundation-section' );
        wp_register_script( 'cbl-plugin-frontend', $path . '/library/js/frontend.min.js', array( 'jquery', 'foundation', 'foundation-section' ), false, true );
        wp_enqueue_script( 'cbl-plugin-frontend' );

        // CSS
        wp_register_style( 'cbl-plugin-frontend', $path . '/library/css/frontend.css' );
        wp_enqueue_style( 'cbl-plugin-frontend' );
    }

    /**
     * Shortcode output
     *
     * @access public
     * @return string
     */
    public function shortcode_callback( $atts ) {
        $locations = $this->settings->options['locations'];
        $days = array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' );
        $count = 0;
        if( empty( $locations ) || is_null( $locations ) || !is_array( $locations ) ) {
            return '<div class="cbl-shortcode-wrap">
                    <div class="row collapse alert-box alert">
                        <div class="small-12 columns">
                            <p style="display:inline-block">You have not created any locations to display!</p>
                            <p style="display:inline-block;margin-left:25px"><a class="button" href="' . admin_url( 'options-general.php?page=custom_business_locations' ) . '" style="margin-bottom: 0;">Get Started!</a></p>
                        </div>
                    </div>
                </div>';

        }
        ob_start();
        ?>
        <div class="cbl-shortcode-wrap">
            <div class="row collapse">
                <div class="small-12 large-5 columns">
                    <div class="cbl-locations section-container accordion" data-section="accordion">
                        <?php foreach ( $locations as $id => $location ) : ?>
                            <?php $title   = ( isset( $location['title'] ) )     ? $location['title']     : ''; ?>
                            <?php $address = ( isset( $location['address'] ) )   ? $location['address']   : ''; ?>
                            <?php $notice  = ( isset( $location['notice'] ) )    ? $location['notice']    : ''; ?>
                            <?php $latlng  = ( isset( $location['latlng'] ) )    ? $location['latlng']    : ''; ?>
                            <?php $lat     = ( isset( $location['lat'] ) )       ? $location['lat']       : ''; ?>
                            <?php $lng     = ( isset( $location['lng'] ) )       ? $location['lng']       : ''; ?>
                            <?php $css     = ( isset( $location['css-class'] ) ) ? $location['css-class'] : ''; ?>
                            <?php $email   = ( isset( $location['email'] ) )     ? $location['email']     : ''; ?>
                            <?php $phone   = ( isset( $location['phone'] ) )     ? $location['phone']     : ''; ?>
                            <?php $active  = ( $count < 1 ) ? 'active' : ''; ?>
                            <section class="cbl-location cbl-location-<?php echo $id ?> <?php echo $active ?>" data-location-id="<?php echo $id?>" data-title="<?php echo $title ?>" data-phone="<?php echo $phone ?>" data-lat="<?php echo $lat ?>" data-lng="<?php echo $lng ?>" data-address="<?php echo $address ?>">
                                <p class="title" data-section-title><a href="#"><?php echo $title ?></a></p>
                                <div class="content" data-section-content>

                                    <?php if ( !empty( $notice) ) : ?>
                                        <div class="row collapse">
                                            <p class="alert-box <?php echo $css ?>">
                                                <?php echo $notice ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                    <div class="row collapse">
                                        <table class="small-12 columns">
                                            <thead><th>Contact</th><th></th></thead>
                                            <tbody>
                                                <tr>
                                                    <td>Phone:</td>
                                                    <td><a href="tel:<?php echo $phone ?>"><?php echo $phone ?></a></td>
                                                </tr>
                                                <tr>
                                                    <td>Email:</td>
                                                    <td><a href="mailto:<?php echo $email ?>"><?php echo $email ?></a></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row collapse">
                                        <table class="small-12 columns">
                                            <thead><tr><th>Hours:</th><th></th></tr></thead>
                                            <tbody>
                                                <?php foreach ( $days as $day ) : ?>
                                                    <?php $closed = ( $this->options['locations'][$location_id][ strtolower( $day ) ]['closed'] == 'closed' ) ? true : false; ?>
                                                    <?php $from = ( isset( $this->options['locations'][$location_id][ strtolower( $day ) ]['from'] ) ) ? $this->options['locations'][$location_id][ strtolower( $day ) ]['from']   : '12:00'; ?>
                                                    <?php $from = date( 'g:i a', strtotime( $from ) ) ?>
                                                    <?php $to = ( isset( $this->options['locations'][$location_id][ strtolower( $day ) ]['to'] ) ) ? $this->options['locations'][$location_id][ strtolower( $day ) ]['to']   : '12:00'; ?>
                                                    <?php $to = date( 'g:i a', strtotime( $to ) ) ?>
                                                    <tr>
                                                        <td><?php echo substr( $day, 0, 3 ) ?>.</td>
                                                        <?php if( $closed ) : ?>
                                                            <td><i>Closed</i></td>
                                                        <?php else : ?>
                                                            <td><i><?php echo $from ?> - <?php echo $to ?></i></td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row collapse">
                                        <p class="small-12 columns"><a class="button expand" href="http://maps.google.com/maps?saddr=&daddr=<?php echo str_replace( ' ', '+', $address ) ?>">Get Directions</a></p>
                                    </div>

                                </div>
                            </section>
                            <?php $count++; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="small-12 hide-for-touch large-7 columns">
                    <div id="cbl-map-canvas" style="height:400px;"/>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

/**
 * Initialize theme in global variable
 *
 * @global MR_JOBS_Theme $GLOBALS['mr_jobs_child']
 * @name $mr_jobs_child
 */
$GLOBALS['custom_business_locations'] = new CBL_Plugin();

}