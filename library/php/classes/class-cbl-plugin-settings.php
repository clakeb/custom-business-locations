<?php
/**
 * CBL_Plugin_Settings Class
 *
 * Builds the settings pages and store options
 *
 * @class CBL_Plugin_Settings
 * @author Anchor Studios
 * @category Class
 */
class CBL_Plugin_Settings {
    public function __construct() {
        $this->options = get_option( 'cbl_plugin_options' );
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        add_action( 'wp_ajax_cbl_plugin_options', array( $this, 'ajax_callback' ) );
    }

    public function add_menu() {
        add_options_page('Custom Business Locations', 'Custom Business Locations', 'manage_options', 'custom_business_locations', array( $this, 'page_output' ) );
    }

    public function register_settings() {
        register_setting( 'cbl_plugin_options', 'cbl_plugin_options' );
    }

    public function ajax_callback() {
        if ( isset( $_POST['add_location'] ) ) {
            echo $this->location_table_output(
                uniqid( 'location_id_' )
            );
        }

        if ( isset( $_POST['remove_location'] ) ) {
            $options = $this->options;
            unset( $options['locations'][ $_POST['order'] ] );
            update_option( 'cbl_plugin_options', $options );
        }

        echo ''; die();
    }

    public function page_output() {
        $api = ( isset( $this->options['api_key'] ) ) ? $this->options['api_key'] : '';
        $locations = ( isset( $this->options['locations'] ) ) ? $this->options['locations'] : '';
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Custom Business Locations</h2>
            <div class="cbl_plugin_options_wrap">

                <div class="small-12 large-3 push-9 columns">
                    <div class="small-4 large-12 columns panel text-center">
                        <p>Past this shortcode into a post or page:</p>
                        <p><code>[cbl-map]</code></p>
                    </div>
                    <div class="small-4 large-12 columns"></div>
                    <div class="small-4 large-12 columns panel text-center">
                        <p>Buy me some joe?</p>
                        <div>
                            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                                <input type="hidden" name="cmd" value="_s-xclick">
                                <input type="hidden" name="hosted_button_id" value="KLVBR4CMTWW7C">
                                <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                                <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
                            </form>
                        </div>
                    </div>
                </div>

                <form method="post" action="options.php" class="small-12 large-9 pull-3 columns">
                    <?php settings_fields( 'cbl_plugin_options' ); ?>
                    <?php if ( !isset( $this->options['api_key'] ) || empty( $this->options['api_key'] ) ) : ?>
                        <h4 class="alert-box alert small-12 large-9 columns">You must aquire an API key before you can create your custom locations and build your map.</h4>
                    <?php endif; ?>
                    <div class="row clearfix">
                        <div class="small-12 columns">
                            <div class="api-key-panel panel small-12 columns">
                                <fieldset>
                                    <legend>Google Maps API Key</legend>

                                    <div class="row">
                                        <div class="large-12 columns">
                                            <input type="password" name="cbl_plugin_options[api_key]" value="<?php echo $api ?>"></label>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>

                            <?php if ( isset( $this->options['api_key'] ) && !empty( $this->options['api_key'] ) ) : ?>
                                <div>
                            <?php else : ?>
                                <div style="display:none">
                            <?php endif; ?>
                                <div class="locations-panel panel small-12 columns">
                                    <p>Drag locations to customize the order</p>
                                    <div class="locations sortable ui-sortable section-container accordion" data-section="accordion"  data-options="one_up:false">
                                    <?php if ( is_array( $locations ) && !empty( $locations ) ) : ?>
                                        <?php foreach ( $locations as $location_id => $location ) : ?>
                                            <?php echo $this->location_table_output( $location_id ); ?>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php else : ?>
                                        </div>
                                        <p id="no-locations-alert" class="alert-box secondary">You do not have any locations yet. Click the button below to add one.</p>
                                    <?php endif; ?>
                                </div>
                                <div class="row">
                                    <div class="small-12 columns">
                                        <p class="small-6 small-centered columns"><a href="#" id="add-location" class="button expand">Add Location</a></p>
                                    </div>
                                    <div class="small-12 large-3 columns"></div>
                                </div>
                                </div>
                            <?php submit_button(); ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    public function location_table_output( $location_id ) {
        $days = array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' );
        $name = 'cbl_plugin_options[locations][' . $location_id . ']';
        $title   = ( isset( $this->options['locations'][$location_id]['title'] ) )     ? $this->options['locations'][$location_id]['title']     : '';
        $address = ( isset( $this->options['locations'][$location_id]['address'] ) )   ? $this->options['locations'][$location_id]['address']   : '';
        $notice  = ( isset( $this->options['locations'][$location_id]['notice'] ) )    ? $this->options['locations'][$location_id]['notice']    : '';
        $latlng  = ( isset( $this->options['locations'][$location_id]['latlng'] ) )    ? $this->options['locations'][$location_id]['latlng']    : '';
        $lat     = ( isset( $this->options['locations'][$location_id]['lat'] ) )       ? $this->options['locations'][$location_id]['lat']       : '';
        $lng     = ( isset( $this->options['locations'][$location_id]['lng'] ) )       ? $this->options['locations'][$location_id]['lng']       : '';
        $css     = ( isset( $this->options['locations'][$location_id]['css-class'] ) ) ? $this->options['locations'][$location_id]['css-class'] : '';
        $email   = ( isset( $this->options['locations'][$location_id]['email'] ) )     ? $this->options['locations'][$location_id]['email']     : '';
        $phone   = ( isset( $this->options['locations'][$location_id]['phone'] ) )     ? $this->options['locations'][$location_id]['phone']     : '';
        ob_start();
            ?>

            <section class="location active" data-location-id="<?php echo $location_id ?>">
                <p class="title" data-section-title><a href="#"><?php echo ( empty( $title ) ) ? 'Draft Location' : $title; ?></a></p>
                <div class="content" data-section-content>
                    <fieldset>
                        <legend>Title</legend>

                        <div class="row">
                            <div class="large-12 columns">
                                <input type="text" class="location-title" value="<?php echo $title ?>" name="<?php echo $name ?>[title]" placeholder="Location Title">
                                <input type="hidden" class="location-order" value="<?php echo $location_id ?>" name="<?php echo $name ?>[order]">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Address</legend>

                        <div class="row">
                            <div class="large-12 columns address-wrap">
                                <input type="hidden" class="location-latlng" value="<?php echo $latlng ?>" name="<?php echo $name ?>[latlng]">
                                <input type="hidden" class="location-lat" value="<?php echo $lat ?>" name="<?php echo $name ?>[lat]">
                                <input type="hidden" class="location-lng" value="<?php echo $lng ?>" name="<?php echo $name ?>[lng]">
                                <input type="text" class="location-address" value="<?php echo $address ?>" name="<?php echo $name ?>[address]" placeholder="Begin typing to search Google Maps...">
                                <div class="address-search-results" style="display:none"></div>
                                <div class="map-canvas small-12 column"/>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Location Notice</legend>

                        <div class="row">
                            <div class="large-12 columns">
                                <label>Feel free to throw in some HTML if you want.</label>
                                <textarea class="location-notice" name="<?php echo $name ?>[notice]"><?php echo $notice ?></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="large-12 columns">
                                <label>Custom notice CSS classes. Seperate with spaces. Have you tried <a href="http://foundation.zurb.com/docs/components/alert-boxes.html">these</a>?</label>
                                <input type="text" class="location-css-class" value="<?php echo $css ?>" name="<?php echo $name ?>[css-class]" placeholder="success round text-center">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Contact</legend>

                        <div class="row">
                            <div class="large-6 columns">
                                <div class="row collapse">
                                    <div class="small-3 columns">
                                        <span class="prefix">Email</span>
                                    </div>
                                    <div class="small-9 columns">
                                        <input type="text" class="location-email" value="<?php echo $email ?>" name="<?php echo $name ?>[email]" >
                                    </div>
                                </div>
                            </div>
                            <div class="large-6 columns">
                                <div class="row collapse">
                                    <div class="small-3 columns">
                                        <span class="prefix">Phone</span>
                                    </div>
                                    <div class="small-9 columns">
                                        <input type="text" class="location-phone" value="<?php echo $phone ?>" name="<?php echo $name ?>[phone]" >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>Hours of Operation</legend>
                        <table class="small-12 columns">
                            <tbody>
                            <?php foreach ( $days as $day ) : ?>
                                <?php $closed = ( $this->options['locations'][$location_id][ strtolower( $day ) ]['closed'] == 'closed' ) ? 'checked' : ''; ?>
                                <?php $readonly = ( $this->options['locations'][$location_id][ strtolower( $day ) ]['closed'] == 'closed' ) ? 'readonly' : ''; ?>
                                <?php $from   = ( isset( $this->options['locations'][$location_id][ strtolower( $day ) ]['from'] ) ) ? $this->options['locations'][$location_id][ strtolower( $day ) ]['from']   : '12:00'; ?>
                                <?php $to   = ( isset( $this->options['locations'][$location_id][ strtolower( $day ) ]['to'] ) ) ? $this->options['locations'][$location_id][ strtolower( $day ) ]['to']   : '12:00'; ?>
                                <tr class="location-day">
                                    <td><b><?php echo $day ?></b></td>
                                    <td>
                                        <div class="row collapse">
                                            <div class="small-3 columns">
                                                <span class="prefix">From</span>
                                            </div>
                                            <div class="small-9 columns">
                                                <input type="time" class="location-from" value="<?php echo $from ?>" name="<?php echo $name ?>[<?php echo strtolower($day) ?>][from]" <?php echo $readonly ?>>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row collapse">
                                            <div class="small-3 columns">
                                                <span class="prefix">To</span>
                                            </div>
                                            <div class="small-9 columns">
                                                <input type="time" class="location-to" value="<?php echo $to ?>" name="<?php echo $name ?>[<?php echo strtolower($day) ?>][to]" <?php echo $readonly ?>>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row collapse">
                                            <div class="small-12 columns">
                                                <label>Closed <input type="checkbox" class="location-closed" name="<?php echo $name ?>[<?php echo strtolower($day) ?>][closed]" value="closed" <?php echo $closed?>></label>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </fieldset>
                    <div class="row">
                        <p class="small-4 columns text-center"><a class="close-section button expand" href="#">Close Location</a></p>
                        <p class="small-4 columns text-center"><a class="remove-location button alert expand" href="#">Remove Location</a></p>
                    </div>
                </div>
            </section>
            <?php
        return ob_get_clean();
    }
}