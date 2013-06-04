<?php
/**
 * Admin functions
 *
 * @package  CustomBusinessLocations
 * @category Admin
 * @author  Anchor Studios
 * @version 1.0.0
 * @since 1.0.0
 */


// Admin init
function cbl_admin_init() {

    // Add location address metabox
    add_meta_box(
        'cbl-location-address',
        'Location Address',
        'cbl_location_address_box',
        'cbl_location',
        'normal',
        'core'
    );

    // Add location hours
    add_meta_box(
        'cbl-location-hours',
        'Hours of Operation',
        'cbl_location_hours_box',
        'cbl_location',
        'normal',
        'core'
    );

    // Add location notice
    add_meta_box(
        'cbl-location-notice',
        'Location Notice',
        'cbl_location_notice_box',
        'cbl_location',
        'normal',
        'core'
    );

    // Do admin action
    do_action( 'cbl_admin_init' );
}
add_action( 'admin_init', 'cbl_admin_init' );

function cbl_admin_scripts() {
    global $custom_business_locations;
    $path = $custom_business_locations->plugin_url();

    wp_register_script( 'map-api', 'https://maps.googleapis.com/maps/api/js?key='.$custom_business_locations->api_key.'&sensor=true' );
    wp_enqueue_script( 'map-api' );
    wp_register_style( 'cbl-admin', $path . '/library/css/admin.css' );
    wp_enqueue_style( 'cbl-admin' );
}
add_action( 'admin_enqueue_scripts', 'cbl_admin_scripts' );

function cbl_save_meta_boxes( $post_id ) {
    global $cbl_theme;
    $_POST += array("cbl_location_edit_nonce" => '');
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return;
    // if ( !wp_verify_nonce( $_POST["cbl_location_edit_nonce"], wp_basename( __FILE__ ) ) )
    //  return;
    if ( !current_user_can( 'edit_post', $post_id ) )
        return;

    if ( 'cbl_location' == $_POST['post_type'] ) {
        // Location Data
        $meta = $_POST['cbl_location_data'];
        update_post_meta( $post_id, '_cbl_location_data', $meta );
    }
}
add_action( 'save_post', 'cbl_save_meta_boxes' );


function cbl_location_address_box() {
    global $post;
    if ( metadata_exists( 'post', $post->ID, '_cbl_location_data' ) ) {
        $post_meta = get_post_meta( $post->ID, '_cbl_location_data' );
        $address = $post_meta[0]['address'];
        $latlng = $post_meta[0]['latlng'];
    } ?>
    <div class="row collapse">
        <div class="small-3 large-2 columns">
            <span class="prefix">Address</span>
        </div>
        <div class="small-9 large-10 columns">
            <input type="text" id="address_search" placeholder="Enter an address" name="cbl_location_data[address]" value="<?php echo $address ?>">
        </div>
        <div class="small-9 large-10 columns">
            <div class="section-container accordion small-12 columns" id="address_search_results" style="display:none;"></div>
        </div>
    </div>
    <div class="row collapse">
        <div id="map-canvas" class="small-12 small-centered columns"></div>
    </div>
    <input type="hidden" id="cbl_location_latlng" name="cbl_location_data[latlng]" value="<?php echo $latlng ?>"> <?php
    if ( $address ) { ?>
        <script type="text/javascript">
        jQuery(document).ready(function() {
            var latlng = new google.maps.LatLng(<?php echo $latlng ?>);
            var mapOptions = {
                zoom: 9,
                center: latlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                disableDefaultUI: true,
                draggable: false,
                zoomControl: false,
                scrollwheel: false,
                disableDoubleClickZoom: true
            }
            var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
            jQuery("#map-canvas").css({'height':'400px'});
            map.setCenter(latlng);
            var marker = new google.maps.Marker({
                map: map,
                position: latlng
            });
        });
        </script> <?php
    } ?>
    <script type="text/javascript">
    jQuery(document).ready(function() {
        var geocoder = new google.maps.Geocoder();
        var timer;
        jQuery("#address_search").bind("keyup", function(args){
            if (jQuery('#address_search').val().length < 3 == 0) {
                window.clearTimeout(timer);
                timer = window.setTimeout(function() {
                    jQuery('#address_search_results').show();
                    var address = jQuery('#address_search').val();
                    geocoder.geocode( { 'address': address}, function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            var suggested_results = '';
                            var count = 0;
                            console.log( results );
                            jQuery.each(results, function(key, value) {
                                if( count < 5 ) {
                                    suggested_results += '<section class="section"><p class="title"><a class="suggested_result" href="#" data-address="' + value['formatted_address'] + '" data-lng="' + value['geometry']['location'].lng() + '" data-lat="' + value['geometry']['location'].lat() + '">' + value['formatted_address'] + '</a></p></section>';
                                    jQuery('#address_search_results').html(suggested_results);
                                    count++;
                                }
                            });
                            jQuery(".suggested_result").click(function() {
                                var lat = jQuery(this).data("lat");
                                var lng = jQuery(this).data("lng");
                                var formatted_address = jQuery(this).data("address");
                                jQuery("#cbl_location_latlng").val( lat + ', ' + lng );
                                jQuery("#address_search").val( formatted_address );
                                jQuery("#address_search_results").html('');
                                jQuery("#map-canvas").css({'height':'400px'});
                                var latlng = new google.maps.LatLng(lat, lng);
                                var mapOptions = {
                                    zoom: 9,
                                    center: latlng,
                                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                                    disableDefaultUI: true,
                                    draggable: false,
                                    zoomControl: false,
                                    scrollwheel: false,
                                    disableDoubleClickZoom: true
                                }
                                var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
                                map.setCenter(latlng);
                                var marker_<?php echo $post->ID ?> = new google.maps.Marker({
                                    map: map,
                                    position: latlng
                                });
                                return false;
                            });
                        } else {
                            jQuery('#address_search_results').html('');
                        }
                    });
                }, 400);
            }
        });
    });
    </script> <?php
}

function cbl_location_hours_box() {
    global $post;
    if ( metadata_exists( 'post', $post->ID, '_cbl_location_data' ) ) {
        $post_meta = get_post_meta( $post->ID, '_cbl_location_data' );
    }
    $days = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ); ?>

    <fieldset>
        <legend>Contact</legend>
        <div class="row collapse">
            <div class="small-6 columns">
                <label for="cbl_location_contact_email">Email</label>
                <input type="text" id="cbl_location_contact_email" name="cbl_location_data[contact][email]" value="<?php echo $post_meta[0]['contact']['email'] ?>">
            </div>
            <div class="small-4 columns">
                <label for="cbl_location_contact_phone">Phone</label>
                <input type="text" id="cbl_location_contact_phone" name="cbl_location_data[contact][phone]" value="<?php echo $post_meta[0]['contact']['phone'] ?>">
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend>Hours of Operation</legend> <?php
        foreach ($days as $day) { ?>
            <div class="row collapse operation-day-fieldset">
                <div class="small-3 columns">
                    <p class="left inline"><?php echo $day ?></p>
                </div>
                <div class="small-9 columns">
                    <div class="row">
                        <div class="small-4 columns">
                            <label for="cbl_location_operation_hours-<?php echo strtolower($day) ?>">From: </label>
                            <input type="time" id="cbl_location_operation_hours-<?php echo strtolower($day) ?>" name="cbl_location_data[operation-hours][<?php echo strtolower($day) ?>][from]" value="<?php echo $post_meta[0]['operation-hours'][strtolower($day)]['from'] ?>">
                        </div>
                        <div class="small-4 pull-2 columns">
                            <label for="cbl_location_operation_hours-<?php echo strtolower($day) ?>">To: </label>
                            <input type="time" id="cbl_location_operation_hours-<?php echo strtolower($day) ?>" name="cbl_location_data[operation-hours][<?php echo strtolower($day) ?>][to]" value="<?php echo $post_meta[0]['operation-hours'][strtolower($day)]['to'] ?>">
                        </div>
                    </div>
                </div>
            </div> <?php
        } ?>
    </fieldset> <?php
}

function cbl_location_notice_box() {
    global $post;
    if ( metadata_exists( 'post', $post->ID, '_cbl_location_data' ) ) {
        $post_meta = get_post_meta( $post->ID, '_cbl_location_data' );
    }
    ?>
    <fieldset>
        <div class="row collapse">
            <div class="small-12 columns">
                <textarea id="cbl_location_notice" name="cbl_location_data[location_notice]"><?php echo $post_meta[0]['location_notice'] ?></textarea>
            </div>
        </div>
    </fieldset>
    <?php
}