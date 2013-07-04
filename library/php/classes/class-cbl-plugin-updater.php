<?php
/**
 * CBL_Plugin_Updater Class
 *
 * Builds the settings pages and store options
 *
 * @class CBL_Plugin_Updater
 * @author Anchor Studios
 * @category Class
 */
class CBL_Plugin_Updater {
    public function __construct( $old, $new ) {
        if ( $old != $new ) {
            switch ( $old ) {
                case false: // 1.0.1
                    $this->one();
                break;
                default:
                    return;
                break;
            }
        }
        return;
    }

    public function one() {
        global $custom_business_locations;

        $days = array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' );
        $old_options = get_option('cbl_options');

        $new_data = array();
        $new_data['api_key'] = $old_options['api_key'];

        $args = array(
            'post_type'   => 'cbl_location',
            'orderby'     => 'menu_order',
            'post_status' => 'publish',
            'order'       => 'ASC'
        );

        $old_locations = get_posts( $args );

        foreach ( $old_locations as $location ) {
            // Create a new id
            $newID = uniqid('location_id_');

            // Grab and save the old meta
            $meta = get_post_meta( $location->ID, '_cbl_location_data' );
            $new_data['locations'][$newID]['title']   = $location->post_title;
            $new_data['locations'][$newID]['address'] = $meta[0]['address'];
            $new_data['locations'][$newID]['notice']  = $meta[0]['location_notice'];
            $new_data['locations'][$newID]['latlng']  = $meta[0]['latlng'];
            $new_data['locations'][$newID]['email']   = $meta[0]['contact']['email'];
            $new_data['locations'][$newID]['phone']   = $meta[0]['contact']['phone'];

            $latlng_arr = explode(',', $meta[0]['latlng'] );
            $new_data['locations'][$newID]['lat']  = $latlng_arr[0];
            $new_data['locations'][$newID]['lng']  = $latlng_arr[1];

            foreach ( $days as $day ) {
                $new_data['locations'][$newID][strtolower($day)]['from'] = $meta[0]['operation-hours'][strtolower($day)]['from'];
                $new_data['locations'][$newID][strtolower($day)]['to'] = $meta[0]['operation-hours'][strtolower($day)]['to'];
            }

            // Delete it
            wp_delete_post( $location->ID, true );
        }

        // Grab and delete junk
        $retracer = get_posts( array( 'post_type' => 'cbl_location', 'post_status' => 'any') );
        foreach( $retracer as $post ) {
            wp_delete_post( $post->ID, true);
        }

        // Update the new data
        delete_option( 'cbl_options' );
        update_option( 'cbl_plugin_options', $new_data );
        update_option( 'cbl_plugin_version', '1.1.0' );

        add_action( 'admin_notices', array( $this, 'messages' ) );

        return new self( '1.1.0', $custom_business_locations->version  );
    }

    public function messages() {
        global $custom_business_locations;
        switch ( $custom_business_locations->version ) {
            case '1.1.0':
                echo '<div class="updated">
                    <p>Custom Business Locations has Updated!</p>
                    <p>I am sorry if you lost any data. I did my best to preserve what I could.</p>
                    <p>All locations are now editable within the plugin settings.</p>
                    <p>This version includes:</p>
                    <ul>
                        <li>Many speed fixes (Javascript was drastically simplified.)</li>
                        <li>No more jQuery problems</li>
                        <li>Removed custom post types</li>
                        <li>New settings UI</li>
                        <li>Google API Key validation</li>
                        <li>CSS fixes for frontend</li>
                    </ul
                    </div>';
            break;
        }
    }
}