<?php
/*
Plugin Name: Custom Google Search
Plugin URI: http://premium.wpmudev.org/project/
Description: This plugin integrate the Google Custom Search into your site. It replaces the default WordPress search on the Google Custom Search and adds widget "Google Custom Search".
Version: 1.0.0.1
Author: Andrey Shipilov (Incsub)
Author URI: http://premium.wpmudev.org/project/google-custom-search
WDP ID: 252

Copyright 2009-2011 Incsub (http://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

include_once( dirname( __FILE__ ) . '/widget.php' );

class CustomGoogleSearch {

    var $plugin_dir;
    var $plugin_url;
    var $settings;
    var $text_domain = 'custom-google-search';
    var $option_name = 'cgs';



    function CustomGoogleSearch() {
        __construct();
    }

    /**
     * PHP 5 constructor
     **/
    function __construct() {

        //setup proper directories
        if ( is_multisite() && defined( 'WPMU_PLUGIN_URL' ) && defined( 'WPMU_PLUGIN_DIR' ) && file_exists( WPMU_PLUGIN_DIR . '/' . basename( __FILE__ ) ) ) {
            $this->plugin_dir = WPMU_PLUGIN_DIR . '/custom-google-search/';
            $this->plugin_url = WPMU_PLUGIN_URL . '/custom-google-search/';
        } else if ( defined( 'WP_PLUGIN_URL' ) && defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/custom-google-search/' . basename( __FILE__ ) ) ) {
            $this->plugin_dir = WP_PLUGIN_DIR . '/custom-google-search/';
            $this->plugin_url = WP_PLUGIN_URL . '/custom-google-search/';
        } else if ( defined('WP_PLUGIN_URL' ) && defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/' . basename( __FILE__ ) ) ) {
            $this->plugin_dir = WP_PLUGIN_DIR;
            $this->plugin_url = WP_PLUGIN_URL;
        } else {
            wp_die( __('There was an issue determining where WPMU DEV Update Notifications is installed. Please reinstall.', 'email-newsletter' ) );
        }

        //loading translation file
        load_plugin_textdomain( $this->text_domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

        //get settings values
        $this->get_settings();

        //including JS
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-ui-dialog' );

        //including CSS
        wp_enqueue_style( 'CGSStyle', $this->plugin_url . 'style/cgs-style.css' );
        //theme for popup
        if ( isset( $this->settings['popup_theme'] ) && '' != $this->settings['popup_theme'] ) {
            wp_enqueue_style( 'CGSStyle_popup_theme', $this->plugin_url . 'style/popup_themes/' . $this->settings['popup_theme'] . '/custom.css' );
        } else {
            $popup_themes = $this->get_popup_themes();
            wp_enqueue_style( 'CGSStyle_popup_theme', $this->plugin_url . 'style/popup_themes/' . $popup_themes[0]['name']  . '/custom.css' );
        }
        // CSS for hide Search button
        if ( isset( $this->settings['hide_button'] ) && '1' == $this->settings['hide_button'] ) {
            wp_enqueue_style( 'CGSHideButton', $this->plugin_url . 'style/cgs-hide-button.css' );
        }


        //creating menu of the plugin
        add_action( 'admin_menu', array( &$this, 'admin_menu' ) );

        //save settings
        add_action( 'admin_init', array( &$this, 'save_settings' ) );

        add_action( 'template_redirect', array( &$this, 'display_search_box' ) );
//        add_action( 'get_search_form', array( &$this, 'change_regular_search_box' ) );
        add_filter( 'get_search_form', array( &$this, 'change_regular_search_box' ) );
    }


    /**
     * Adds admin menu for settings page
     **/
    function admin_menu() {
        add_options_page( __( 'Custom Google Search' ), __( 'Custom Google Search' ), 'manage_options', 'custom-google-search-settings', array( &$this, 'page_settings' ) );
    }

    /**
     * Tempalate of the settings page
     **/
    function page_settings() {
        require_once( $this->plugin_dir . 'page-settings.php' );
    }

    /**
     * Get plugin settings
     **/
    function get_settings() {
        $this->settings = get_option( $this->option_name );
        if ( !is_array( $this->settings ) )
            $this->settings = array();
    }

    /**
     * Save plugin settings
     **/
    function save_settings() {
        if ( isset( $_GET['page'] ) && 'custom-google-search-settings' == $_GET['page'] && isset( $_POST['save_cgs'] ) ) {
            if ( wp_verify_nonce( $_POST['_wpnonce'], 'save_cgs' ) ) {
                update_option( $this->option_name, $_POST['settings'] );
                wp_redirect( add_query_arg( array( 'page' => 'custom-google-search-settings', 'dmsg' => urlencode( __( 'Changes are saved.', $this->text_domain ) ) ), 'options-general.php' ) );
            }

        }
    }

    /**
     * Get all themes for popup block
     **/
    function get_popup_themes() {
        $theme_dirs = glob( $this->plugin_dir . 'style/popup_themes/*' );
        $themes = array();
        foreach( $theme_dirs as $theme_dir ){
            $themes[] = array(
                'name' => basename( $theme_dir )
            );
        }
        return $themes;
    }

    /**
     * Gen html/js code of search box
     **/
    function generate_search_box( $args = '' ) {

        $search_box = '';

        if ( isset( $this->settings['style'] ) && '' != $this->settings['style'] && 'DEFAULT' != $this->settings['style'] )
            $theme = 'style : google.loader.themes.' . $this->settings['style'];
        else
            $theme = '';

        //set protocol (http by default)
        $protocol = ( !isset( $this->settings['protocol'] ) ) ? 'http:' : ( ( isset( $this->settings['protocol'] ) && 'relative' != $this->settings['protocol'] ) ? $this->settings['protocol'] : '');

        //run search by query
        if ( isset( $args['search_text'] ) )
            $search_text = '
                ///run search by query
                customSearchControl.execute("' . $_REQUEST['s'] . '");';
        else
            $search_text = '';

        //gen rendom search box ID
        $search_div_id = "cgs-search-form-". rand( 0, 99 ) ;


        $popup_script = '';
        //choose how display result
        if( isset( $args['display_results'] ) && 1 == $args['display_results'] ){
            //display result in Popup
            $CSC_draw = 'customSearchControl.draw( "cgs_popup", options );
                         customSearchControl.setSearchCompleteCallback( this, CallBackDisplayPopup );';
            $popup_script = '

                //callback function
                function CallBackDisplayPopup(result) {
                    jQuery( "#cgs_popup" ).dialog( "open" );
                }

                //code for display popup with results
                jQuery( document ).ready( function() {

                    // popup
                    jQuery( "#cgs_popup" ).dialog({
                        autoOpen: false,
                        width: 600,
                        height: 500,
                        modal: true
                    });

                });
            ';

        } elseif ( isset( $args['display_results'] ) && 2 == $args['display_results'] ) {
            //display result bottom of thw widget
            $CSC_draw = 'customSearchControl.draw( "cgs-widget", options );';
        } elseif ( isset( $args['display_results'] ) && 3 == $args['display_results'] ) {
            //display result bottom of thw widget
            $CSC_draw = '
                customSearchControl.draw( "cgs", options );

                customSearchControl.setSearchStartingCallback( this, function( control, searcher, query ) {
                    window.location.href = "' . get_option( 'siteurl' ) . '/?s=" + query;
                });
            ';
        } else {
            $CSC_draw = 'customSearchControl.draw( "cgs-' . $search_div_id . '", options );';
        }

        //get code of seach box
        $search_box = '
            <script src="' . $protocol . '//www.google.com/jsapi" type="text/javascript"></script>

            <div id="' . $search_div_id . '" style="width: 100%;">Loading</div>
            <script type="text/javascript">
                google.load( "search", "1", {language : "'. get_locale() . '",'
                . $theme . '} );
                google.setOnLoadCallback( function() {
                var customSearchControl = new google.search.CustomSearchControl( "' . $this->settings['engine_id'] . '" );


                var options = new google.search.DrawOptions();
                options.setSearchFormRoot("' . $search_div_id . '");
                options.setAutoComplete(true);

                customSearchControl.setResultSetSize( google.search.Search.FILTERED_CSE_RESULTSET );

                ' . $CSC_draw .'

                ' . $search_text .'

                }, true );

                ' . $popup_script . '

            </script>
         ';

        if ( isset( $args['display_results'] ) && 1 == $args['display_results'] ) {
             //add popup for results
            $search_box .= '<div id="cgs_popup" title="' . __( 'Search Results', $this->text_domain ) . '"><p></p></div>';
        } elseif ( isset( $args['display_results'] ) && 2 == $args['display_results'] ) {
            //add block for shoe results bottom of widget
            $search_box .= '<div id="cgs-widget" style="width:100%;"></div>';
        } else {
            $search_box .= '<div id="cgs-' . $search_div_id . '" style="width:100%;"></div>';
        }

        //include defaul CSS from Google for default theme
        if ( isset( $this->settings['style'] ) && 'DEFAULT' == $this->settings['style'] ) {
            $search_box .= '<link rel="stylesheet" href="' . $protocol . '//www.google.com/cse/style/look/default.css" type="text/css" />';
        }

        return $search_box;
    }


    /**
     * Display search box
     **/
    function display_search_box() {

        // not a search page; don't do anything and return
        if ( stripos( $_SERVER['REQUEST_URI'], '/?s=' ) === FALSE && stripos( $_SERVER['REQUEST_URI'], '/search/') === FALSE ) {
            return;
        }
        if ( !isset( $this->settings['engine_id'] ) || '' == $this->settings['engine_id'] ) {
            return;
        }

        get_header();

        echo '<div id="primary">
                <div id="content" role="main">';

        $arg = array();

        if ( isset( $_REQUEST['s'] ) && '' != $_REQUEST['s'] )
            $args = array(
                'search_text' => $_REQUEST['s']
            );

        $search_box = $this->generate_search_box( $args );

        echo ( '' != $search_box ) ? $search_box : '';

        echo '  </div>
              </div>';

        //show sidebar
        if ( isset( $this->settings['show_sidebar'] ) && '1' == $this->settings['show_sidebar'] ) {
            get_sidebar();
        }

        get_footer();
        exit;

    }

    /**
     * change regular search box only if searchform.php not exist
     **/
    function change_regular_search_box( $form ) {
        $args = array(
            'display_results' => 3
        );
        $form = '<div id="searchform"> ' . $this->generate_search_box( $args ) .'</div>';
        return $form;
    }


}

$custom_google_search = &new CustomGoogleSearch();

?>