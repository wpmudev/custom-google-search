<?php
/**
* Widget for Custom-Google-Search plugin
*/

add_action( 'widgets_init', create_function( '', 'return register_widget( "CGS_Widget" );' ) );

/**
 * Widget class
 */
class CGS_Widget extends WP_Widget {

    /**
     * PHP 5 constructor
     **/
    function __construct() {
        global $custom_google_search;
        //settings
        $widget_ops = array(
            'description'   => __( 'A Google Custom Search form for your site.',  $custom_google_search->text_domain )
        );

        //create widget
        parent::__construct( 'cgs-widget', __( 'Custom Google Search', $custom_google_search->text_domain ), $widget_ops );
	}

	/**
	 * Display widget
	 */
	function widget( $args, $instance ) {
        global $custom_google_search;

		extract( $args );

        if ( isset( $custom_google_search->settings['engine_id'] ) && '' != $custom_google_search->settings['engine_id'] ) {
            //display widget content
            echo $custom_google_search->generate_search_box( $args );
        }

	}

	/**
	 * Update settings
	 */
	function update( $new_instance, $old_instance ) {
            return $old_instance;
	}

}
?>
