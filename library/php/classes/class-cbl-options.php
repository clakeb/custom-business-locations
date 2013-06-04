<?php
/**
 * CBL Options Class
 *
 * Builds the plugin's options pages and stores options
 *
 * @class CBL_Options
 * @package CBL_Plugin
 * @category Class
 * @author Anchor Studios
 * @version 1.0.0
 * @since 1.0.0
 */
class CBL_Options {

    /**
     * @var string Options page slug
     **/
    var $slug = 'cbl_options';

    /**
     * @var string Menu text
     **/
    var $menu_text = 'Custom Business Locations';

    /**
     * @var string Database key for stored options
     **/
    var $options_key = 'cbl_options';

    /**
     * @var string Stored options
     **/
    var $get_options = array();

    /**
     * @var array Tabs, Sections, and Fields
     **/
    var $tabs_sections_fields = array(
        array( // Tab Container
            array(
                'name'        => 'Settings',
                'description' => '',
                array( // Section Container
                    array(
                        'name' => '',
                        'description' => 'You must set your Google API Key before you can display your map.',
                        array( // Field Container
                            array(
                                'name' => 'API Key',
                                'description' => '',
                                'id' => 'api_key',
                                'type' => 'text',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    );

    /**
     * CBL_Options Constructor
     *
     * @access public
     * @return void
     */
    public function __construct() {
        $this->tabs_sections_fields = apply_filters(
            'fsf_plugin_options_tabs_sections_fields',
            $this->tabs_sections_fields,
            $this->tabs_sections_fields
        );

        $this->get_options = get_option($this->options_key);

        if ( is_admin() ) {
            add_action( 'admin_menu', array( $this, 'add_options_page' ) );
            add_action( 'admin_init', array( $this, 'register_settings') );
        }
    }

    /**
     * Create options page
     *
     * @access public
     * @return void
     */
    public function add_options_page() {
        global $custom_business_locations;
        $url = $custom_business_locations->plugin_url();
        add_options_page(
            $this->menu_text,
            $this->menu_text,
            'manage_options',
            $this->slug,
            array( $this, 'page_output' )
        );
    }

    /**
     * Output options page html
     *
     * @access public
     * @return
     */
    public function page_output() { ?>
        <div class="wrap"> <?php
            screen_icon(); ?>
            <h2>Custom Business Locations</h2> <?php
            $this->tab_html_output(); ?>
        </div> <?php
    }

    /**
     * Register settings, option sections, and option fields
     *
     * Loops through each tab and registers it's corresponing sections and fields.
     *
     * @access public
     * @return void
     */
    public function register_settings( ) {
        // Register the settings
        register_setting( $this->options_key, $this->options_key );

        // Foreach tab, add sections. Foreach section, add fields
        foreach( $this->tabs_sections_fields[0] as $tab ) {
            if ( is_array($tab[0]) ) foreach( $tab[0] as $section ) {
                add_settings_section(
                    $this->slug
                        . '_tab_'
                        . sanitize_title($tab['name'])
                        . '_section_'
                        . sanitize_title($section['name']),
                    $section['name'],
                    array($this, 'section_html_descriptions'),
                    $this->slug
                        . '_tab_'
                        . sanitize_title($tab['name'])
                );

                if ( is_array($section[0]) ) foreach( $section[0] as $field ) {
                    add_settings_field(
                        sanitize_title($field['id']),
                        $field['name'],
                        array($this, 'field_html_output'),
                        $this->slug
                            . '_tab_'
                            . sanitize_title($tab['name']),
                        $this->slug
                            . '_tab_'
                            . sanitize_title($tab['name'])
                            . '_section_'
                            . sanitize_title($section['name']),
                        array(
                            'type'        => $field['type'],
                            'description' => $field['description'],
                            'id'          => sanitize_title($field['id'])
                        )
                    );
                }
            }
        }
    }

    /**
     * Output tab HTML
     *
     * @access public
     * @return void
     */
    public function tab_html_output() {
        // Set current tab if available, else current tab is first tab
        $current_tab = ( isset ( $_GET['tab'] ) ) ? $_GET['tab'] : sanitize_title($this->tabs_sections_fields[0][0]['name']);

        // Echo tab links
        echo '<h2 class="nav-tab-wrapper">';
        foreach( $this->tabs_sections_fields[0] as $tab ) {
            if( $current_tab == sanitize_title($tab['name']) ) {
                echo '<a class="nav-tab nav-tab-active" href="?page=' . $this->slug . '&tab=' . sanitize_title($tab['name']) . '">' . $tab['name'] . '</a>';
            } else {
                echo '<a class="nav-tab" href="?page=' . $this->slug . '&tab=' . sanitize_title($tab['name']) . '">' . $tab['name'] . '</a>';

            }

        }
        echo '</h2>';

        // Output tabs
        foreach( $this->tabs_sections_fields[0] as $tab ) {

            // If current tab is this tab in the loop...
            if( $current_tab == sanitize_title($tab['name'] ) ) {

                // Echo the tab description
                echo '<h4>' . $tab['description'] . '</h4>';

                // If the tab has a section array, output the tab's settings
                if ( is_array($tab[0]) ) { ?>
                    <form method="post" action="options.php"> <?php
                    settings_fields($this->options_key);
                    do_settings_sections( $this->slug . '_tab_' . sanitize_title($tab['name']) );
                    submit_button(); ?>
                    </form> <?php

                // Else output the tab's output function
                } elseif ( isset( $tab['output_function'] ) ) {
                    if( method_exists( $this, $tab['output_function'] ) ) {
                        $this->$tab['output_function']();
                    } else {
                        $tab['output_function']();
                    }
                }
            }

        }
    }

    /**
     * Output section HTML
     *
     * @access public
     * @return void
     */
    public function section_html_descriptions( $section_passed ) {
        foreach( $this->tabs_sections_fields[0] as $tab ) {

            // If the tab contains a section, loop through the sections
            if ( is_array($tab[0]) ) foreach( $tab[0] as $section ) {

                // Set the section's id
                $id = $this->slug . '_tab_' . sanitize_title($tab['name']) . '_section_' . sanitize_title($section['name']);

                // If the section passed from the register_section function is the current section in the loop...
                if ( $section_passed['id'] == $id ) {

                    // Print the section's description and break the loop.
                    print $section['description'];
                    break;
                }
            }
        }
    }

    /**
     * Output field HTML
     *
     * @access public
     * @return void
     */
    public function field_html_output( $args ) {
        // Filter the html output based on the field's type
        switch ( $args['type'] ) {
            case 'text':
                $html .= '<input type="text"';
                break;
            case 'number':
                $html .= '<input type="number"';
                break;
        }
        $html .= ' id="' . $args['id'] . '" name="' . $this->options_key . '[' . $args['id'] . ']" value="' . $this->get_options[$args['id']] . '">';
        $html .= '</br><i>' . $args['description'] . '</i>';
        echo $html;
    }
}
