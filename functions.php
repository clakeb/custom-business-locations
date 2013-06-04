<?php
/*
Plugin Name: Custom Business Locations
Plugin URI: http://anchor.is/plugins/custom-business-locations
Description: Add your business's locations and display a custom Google map with a shortcode.
Version: 1.0.0
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

/**
 * Main Plugin Class
 *
 * @package CustomBusinessLocations
 * @author Anchor Studios
 * @version 1.0.0
 * @since 1.0.0
 */
class CBL_Plugin {

	/**
	 * @var string
	 **/
	public $version = '1.0.0';

	/**
	 * @var string
	 **/
	public $plugin_path;

	/**
	 * @var string
	 **/
	public $plugin_url;

	/**
	 * @var string
	 **/
	public $api_key;

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

		// Load plugin
		add_action( 'init', array( $this, 'init' ) );

		// Installation
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		// Uninstall
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}

	/**
	 * Init plugin when WordPress initialized
	 *
	 * @access public
	 * @return void
	 */
	public function init() {

        // Do pre-init action
        do_action( 'cbl_before_init' );

        // Dependencies
        $this->dependencies();

        // Register taxonomies
        $this->register_taxonomies();

        // Register post types
        $this->register_post_types();

        // Add the options page
        $this->options = new CBL_Options;

        // Grab the API key
        $this->api_key = $this->options->get_options['api_key'];

        // Add map shortcode
		add_shortcode( 'cbl-map', array( $this, 'cbl_map_output' ) );

        // Do init action
        do_action( 'cbl_init' );
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
	 * Auto-load CBL classes on demand to reduce memory consumption
	 *
	 * @access public
	 * @param mixed $class
	 * @return void
	 */
	public function autoload( $class ) {
		$class = strtolower( $class );
		if ( strpos( $class, 'cbl_' ) === 0 ) {
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
            include_once( $path . '/library/php/admin/admin-init.php' );
        } else {
            include_once( $path . '/library/php/frontend/frontend-init.php' );
        }
    }

	/**
	 * Register Post Types
	 *
	 * @access public
	 * @return void
	 */
	public function register_post_types() {
		// Locations
		register_post_type(
			'cbl_location',
			array(
				'labels'			  => array(
					'name' 					=> 'Locations',
					'singular_name' 		=> 'Location',
					'menu_name'				=> 'Locations',
					'add_new' 				=> 'Add Location',
					'add_new_item' 			=> 'Add New Location',
					'edit' 					=> 'Edit',
					'edit_item' 			=> 'Edit Location',
					'new_item' 				=> 'New Location',
					'view' 					=> 'View Location',
					'view_item' 			=> 'View Location',
					'search_items' 			=> 'Search Locations',
					'not_found' 			=> 'No Locations found',
					'not_found_in_trash' 	=> 'No Locations found in trash',
				),
				'description'         => 'This is where you can add locations to your map.',
				'public'              => false,
				'show_ui'             => true,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => false,
				'hierarchical'        => false,
				'supports'            => array( 'title', 'page-attributes' ),
				'has_archive' => false,
				'rewrite' => false,
				'can_export' => true,
			)
		);
	}

    /**
     * Register taxonomies
     *
     * @access public
     * @return string
     */
    public function register_taxonomies() {

    }

    /**
     * Output map shortcode
     *
     * @access public
     * @return string
     */
    public function cbl_map_output() {
    	global $post;
    	ob_start();
    		$args = array(
				'post_type' => 'cbl_location',
				'orderby'   => 'menu_order',
				'order'     => 'ASC',
			);

			$cbl_query = new WP_Query( $args );

			$days = array(
				'Sun' => 'Sunday',
				'Mon' => 'Monday',
				'Tue' => 'Tuesday',
				'Wed' => 'Wednesday',
				'Thu' => 'Thursday',
				'Fri' => 'Friday',
				'Sat' => 'Saturday'
			);

			$counter = 0;

			if ( $cbl_query->have_posts() ) : ?>
				<script type="text/javascript">
					var posts = new Array();
					var locations = new Array();
				    var contentString= new Array();
				</script>
				<div class="cbl-map row collapse">
					<div class="small-12 large-4 columns">
						<div class="section-container accordion" data-section="accordion">
						<?php while ( $cbl_query->have_posts() ) : $cbl_query->the_post(); ?>
							<?php
							$meta = get_post_meta( get_the_ID(), '_cbl_location_data' );
							$latlng = $meta[0]['latlng'];

							$default_infowindow = '<div class="cbl-infowindow"><h4>' . get_the_title() . '</h4><p>' . $meta[0]['address'] . '</br>' . $meta[0]['contact']['phone'] . '</p></div>';
							$default_infowindow = preg_replace('/, /', ",</br>", $default_infowindow, 1);
							if( preg_match_all( '/\d/', $meta[0]['contact']['phone'],  $matches ) )
								foreach ($matches[0] as $match) {
									$string_phone .= $match;
								}
							?>
							<section class="cbl-section <?php echo ($counter == 0 ) ? 'active' : ''; ?> cbl-section-tab-<?php echo get_the_ID() ?>" data-cbl-id=<?php echo get_the_ID(); ?>>
								<p class="title" data-section-title><a href="#"><?php echo get_the_title(); ?></a></p>
								<div class="content" data-section-content>
									<?php if( $meta[0]['location_notice'] ) { ?>
										<div class="row collapse">
											<p class="alert-box alert">
												<?php echo $meta[0]['location_notice']; ?>
											</p>
										</div>
									<?php } ?>
									<div class="row collapse">
										<table class="small-12 columns">
											<thead><th>Contact</th><th></th></thead>
											<tbody>
												<tr>
													<td>Phone:</td>
													<td><a href="tel:<?php echo $string_phone ?>"><?php echo $meta[0]['contact']['phone'] ?></a></td>
												</tr>
												<tr>
													<td>Email:</td>
													<td><a href="mailto:<?php echo $meta[0]['contact']['email'] ?>"><?php echo $meta[0]['contact']['email'] ?></a></td>
												</tr>
											</tbody>
										</table>
									</div>
									<div class="row collapse">
										<table class="small-12 columns">
											<thead><tr><th>Hours:</th><th></th></tr></thead>
											<tbody> <?php
												foreach ($days as $abbr => $day) {
													$from = date( 'g:i a', strtotime($meta[0]['operation-hours'][strtolower($day)]['from']) );
													$to = date( 'g:i a', strtotime($meta[0]['operation-hours'][strtolower($day)]['to']) ); ?>

													<tr>
														<td><?php echo $abbr ?>.</td>
														<?php if( $meta[0]['operation-hours'][strtolower($day)]['from'] != 0 || $meta[0]['operation-hours'][strtolower($day)]['to'] != 0 ) { ?>
															<td><i><?php echo $from ?> - <?php echo $to ?></i></td>
														<?php } else { ?>
															<td><i>Closed</i></td>
														<?php } ?>
													</tr> <?php
												} ?>
											</tbody>
										</table>
									</div>
									<div class="row collapse">
										<p class="small-12 columns"><a class="button expand" href="http://maps.google.com/maps?saddr=&daddr=<?php echo str_replace( ' ', '+', $meta[0]['address'] ) ?>">Get Directions</a></p>
									</div>
								</div>
								<?php if ($latlng) { ?>
									<script type="text/javascript">
										posts.push(<?php echo $post->ID ?>);
										locations[<?php echo $post->ID ?>] =  new google.maps.LatLng(<?php echo $latlng ?>);
									    contentString[<?php echo $post->ID ?>] = '<?php echo ( $meta[0]['custom_infowindow'] ) ? $meta[0]['custom_infowindow'] : $default_infowindow; ?>';
									</script>
								<?php } ?>
							</section>
							<?php $counter++; ?>
						<?php endwhile; ?>
						</div>
					</div>
					<div id="map-canvas" class="small-12 hide-for-small large-8 columns">
					</div>
				</div>
			<?php else: endif; ?>

			<script type="text/javascript">
				(function($) {
				var latlngbounds = new google.maps.LatLngBounds();
				var markers = new Array();
				var infowindows = new Array();
				var mapOptions = {
					zoom: 6,
					center: new google.maps.LatLng(41.850033, -87.6500523),
					mapTypeId: google.maps.MapTypeId.ROADMAP,
					scrollwheel: false,
				}
				map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
				for( i = 0; i < posts.length; i++ ) {
					latlngbounds.extend( locations[ posts[i] ] );
				    markers[ posts[i] ] = new google.maps.Marker({
				        position: locations[ posts[i] ],
				        map: map,
				    });

				    markers[ posts[i] ].postID = posts[ i ];
				    infowindows[ posts[i] ] = new google.maps.InfoWindow({
						content: contentString[ posts[i] ]
					});
					google.maps.event.addListener( markers[ posts[i] ], 'click', function() {
						infowindows[ this.postID ].open( map, markers[ this.postID ] );
						$('.section-container.accordion .cbl-section').removeClass('active');
						$( '.cbl-section-tab-' + this.postID ).addClass( 'active ' );
						map.panTo( this.getPosition() );
						$('#map-canvas').height($('.section-container.accordion').height());
					});
					google.maps.event.addDomListener( $( '.cbl-section-tab-' + posts[i] )[0], 'click', function() {
						var id = $(this).data('cblId');
						infowindows[ id ].open( map, markers[ id ] );
						map.panTo( markers[ id ].getPosition() );
						$('#map-canvas').height($('.section-container.accordion').height());
					});

				}
				google.maps.event.addListener(map, 'zoom_changed', function() {
					zoomChangeBoundsListener =
						google.maps.event.addListener(map, 'bounds_changed', function(event) {
							if (this.getZoom() > 15 && this.initialZoom == true) {
								// Change max/min zoom here
								this.setZoom(11);
								this.initialZoom = false;
							}
							google.maps.event.removeListener(zoomChangeBoundsListener);
						});
				});
				map.initialZoom = true;
				map.fitBounds( latlngbounds );

				$(document).ready(function() {
					$('#map-canvas').height($('.section-container.accordion').height());
					$('.section-container.accordion').click(function() {
						setTimeout(function() {
							$('#map-canvas').height($('.section-container.accordion').height());
						}, 10 );
					});
				});
				})( jQuery );
			</script>

		<?php
    	return ob_get_clean();
	}
}

$GLOBALS['custom_business_locations'] = new CBL_Plugin();

}