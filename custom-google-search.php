<?php
/*
Plugin Name: Custom Google Search
Plugin URI: http://premium.wpmudev.org/project/custom-google-search
Description: This plugin replaces the default WordPress search with Google Custom Search and adds a Google Custom Search widget.
Version: 1.2.3
Author: WPMU DEV
Author URI: http://premium.wpmudev.org
WDP ID: 252

Copyright 2009-2013 Incsub (http://incsub.com)

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

    /**
     * PHP 5 constructor
     **/
    function __construct() {

        global $wpmudev_notices;
        $wpmudev_notices[] = array( 'id'=> 252,'name'=> 'Custom Google Search', 'screens' => array( 'settings_page_custom-google-search-settings' ) );
        include_once($this->plugin_dir . 'external/dash-notice/wpmudev-dash-notification.php');

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

        add_action( 'wp_enqueue_scripts', array( &$this, 'add_js_css' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'settings_scripts' ) );

        //creating menu of the plugin
        add_action( 'admin_menu', array( &$this, 'admin_menu' ) );

        //save settings
        add_action( 'admin_init', array( &$this, 'save_settings' ) );

        add_action( 'template_redirect', array( &$this, 'display_search_box' ) );
        add_filter( 'get_search_form', array( &$this, 'change_regular_search_box' ) );

        add_shortcode( 'cgs_search_result', array( $this, 'cgs_search_result_shortcode' ) );
    }


    /**
     * including JS/CSS
     **/
    function add_js_css() {
       
        // CSS for hide Search button
        if ( isset( $this->settings['hide_button'] ) && '1' == $this->settings['hide_button'] ) {
            wp_enqueue_style( 'CGSHideButton', $this->plugin_url . 'style/cgs-hide-button.css' );
        }
    }

    /**
     * including scripts for admin
     **/
    public function settings_scripts(){
        wp_register_script( 'cgs-settings-script', plugins_url('js/admin-settings.js', __FILE__) );
        wp_enqueue_script( 'cgs-settings-script' );
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
                $params                 = $_POST['settings'];
                $params['engine_id']    = $this->get_id_from_embed( $params['embed_code'] );
                $params['embed_code']   = trim( $params['embed_code'] );

                update_option( $this->option_name, $params );
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
     * parsed embed code for get engine ID
     **/
    function get_id_from_embed( $embed_code ) {
        $engine_id  = $embed_code;
        $embed_code = preg_replace( "/[ \n\r\t\v\'\"]/m", "", stripslashes( $embed_code ) );

		$start = strpos($embed_code, 'varcx=');
		$end = strpos($embed_code,';',$start);

		if($start && $end)
			$engine_id = substr($embed_code, $start+6, $end - ($start+6));

        return $engine_id;
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

        $attrs['data-enableAutoComplete'] = 'true';
        $gcse_code = 'search';
        $attrs['data-queryParameterName'] = 's';

        if( !empty( $this->settings['page_mode'] ) && 'custom_page' === $this->settings['page_mode'] ) {
            $attrs['data-queryParameterName'] = 'q';
        }

        if( !empty( $args['widget_id'] ) || !empty( $args['regular_search_box'] ) ) {
            if( empty( $this->settings['same_window'] ) ) {
                $attrs['data-newWindow'] = 'true';
            }

            //gen rendom search box ID
            $attrs['data-gname'] = "cgs-search-form-". rand( 0, 99 ) ;

            if( !empty( $this->settings['page_mode'] ) && ( 'search' === $this->settings['page_mode'] || 'generated' === $this->settings['page_mode'] ) ) {
                //display result on search page
                $gcse_code = 'searchbox-only';
                $attrs['data-resultsUrl'] = home_url();
            } elseif( !empty( $this->settings['page_mode'] ) && 'custom_page' === $this->settings['page_mode'] ) {

                global $post;

                $page_id = !empty( $this->settings['search_result_page'] )
                        ? (int)$this->settings['search_result_page']
                        : 0;
                if ( empty( $post->ID ) || $page_id !== $post->ID ) {
                    $gcse_code = 'searchbox-only';
                    $resultsUrl = !empty( $page_id )
                            ? get_permalink( $page_id )
                            : home_url() . '/?s=' . get_search_query();
                    $attrs['data-resultsUrl'] = $resultsUrl;
                }
            }
        }

        $all_attrs = '';
        foreach ( $attrs as $key => $val ) {
            $all_attrs .= ' ' . $key . '="' . $val . '"';
        }
        
        //get code of seach box
        $search_box = "
            <script type='text/javascript'>
                (function() {
                    var cx = '" . $this->settings['engine_id'] . "';
                    var gcse = document.createElement('script');
                    gcse.type = 'text/javascript';
                    gcse.async = true;
                    gcse.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') +
                        '//www.google.com/cse/cse.js?cx=' + cx;
                    var s = document.getElementsByTagName('script')[0];
                    s.parentNode.insertBefore(gcse, s);
                 })();
            </script>";
        $search_box .= '<div class="wgs_wrapper">';
        $search_box .= '<div class="gcse-' . $gcse_code . '" ' . $all_attrs . '></div>';
        $search_box .= '</div>';


        return $search_box;        
    }


    /**
     * Display search box
     **/
    function display_search_box() {
        global $wp_query;

        if(!$wp_query->is_search) {
            return;
        }
        if ( !isset( $this->settings['engine_id'] ) || '' == $this->settings['engine_id'] ) {
            return;
        }

        if(!isset($this->settings['page_mode']))
            $this->settings['page_mode'] = '';
        if($this->settings['page_mode'] == 'page' || $this->settings['page_mode'] == 'search') {
            if($this->settings['page_mode'] == 'page')
                remove_all_filters('the_content');
            else
                remove_all_filters('the_excerpt');

            $arg = array();
            $search_query = get_search_query();
            if ( '' != $search_query )
                $args = array(
                    'results' => true,
                );

            $search_box = $this->generate_search_box( $args );

            //Clear out any posts already stored in the $wp_query->posts array.
            $wp_query->posts = array();
            if($this->settings['page_mode'] == 'page') {
                $wp_query->is_search = false;
                $wp_query->is_page = true;
            }

            //Create a fake post.
            $post = new stdClass;
            $post->post_author = 1;
            $post->post_name = 'unsubscribe';

            if($wp_query->is_search)
                $post->post_title = '';
            else
                $post->post_title = __( 'Search results for: ', $this->text_domain ).$search_query;
            $post->post_content = $search_box;
            $post->post_excerpt = $post->post_content;
            $post->ID = 0;
            $post->post_status = 'publish';
            $post->post_type = 'page';
            $post->comment_status = 'closed';
            $post->ping_status = 'closed';
            $post->comment_count = 0;
            $post->post_date = current_time('mysql');
            $post->post_date_gmt = current_time('mysql', 1);

            $wp_query->posts[] = $post;
            $wp_query->post_count = 1;
        }
        elseif( $this->settings['page_mode'] == 'generated' ) {
            get_header();

            echo '<div id="content" role="main">';

            $arg = array();

            $search_query = get_search_query();
            if ( '' != $search_query )
                $args = array(
                    'search_text' => $search_query
                );

            $search_box = $this->generate_search_box( $args );

            echo ( '' != $search_box ) ? $search_box : '';

            echo '</div>';

            //show sidebar
            if ( isset( $this->settings['show_sidebar'] ) && '1' == $this->settings['show_sidebar'] ) {
                get_sidebar();
            }

            get_footer();
            exit;
        }
    }

    /**
     * change regular search box only if searchform.php not exist
     **/
    function change_regular_search_box( $form ) {
        if ( isset( $this->settings['engine_id'] ) && '' != $this->settings['engine_id'] ) {
            $args = array(
                'regular_search_box' => true,
            );
            $form = '<div id="searchform"> ' . $this->generate_search_box( $args ) .'</div>';
        }
        return $form;
    }

    /**
    * Shortcode callback
    **/
    function cgs_search_result_shortcode( $atts ) {
        $args = array(
            'shortcode' => true,
        );

        return $this->generate_search_box( $args );
    }


}
global $custom_google_search;
$custom_google_search = new CustomGoogleSearch();

function cgs_generate_search_box($args = '') {
	global $custom_google_search;
	echo $custom_google_search->generate_search_box($args);
}

add_action( 'wp_footer', function() {
    ?>
    <style>
        .gsc-control-cse .gsc-table-result {
	font-family : inherit;
}

.gsc-control-cse .gsc-input-box {
	height : inherit;
}

input.gsc-input,
.gsc-input-box,
.gsc-input-box-hover,
.gsc-input-box-focus,
.gsc-search-button, input.gsc-search-button-v2 {
	box-sizing  : content-box;
	line-height : normal;
	margin-top  : 0px;
}
    </style>
    <?php
} );