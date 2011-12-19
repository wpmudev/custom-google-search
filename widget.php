<?php
/**
* Widget for Custom-Google-Search plugin
*/

add_action( 'widgets_init', create_function( '', 'return register_widget( "CGS_Widget" );' ) );

/**
 * Widget class
 */
class CGS_Widget extends WP_Widget {

    function CGS_Widget() {
        __construct();
    }

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
        $this->WP_Widget( 'cgs-widget', __( 'Google Custom Search', $custom_google_search->text_domain ), $widget_ops );
	}

	/**
	 * Display widget
	 */
	function widget( $args, $instance ) {
        global $custom_google_search;

		extract( $args );

        $args = array(
            'display_results' => $instance['display_results']
        );

        //display widget content
        echo $custom_google_search->generate_search_box( $args );

	}

	/**
	 * Update settings
	 */
	function update( $new_instance, $old_instance ) {
		$instance                       = $old_instance;
		$instance['display_results']    = $new_instance['display_results'];
		return $instance;
	}

	/**
	 * Settings page of the widget
	 */
	function form( $instance ) {
        global $custom_google_search;
        ?>

		<p>
			<label for="<?php echo $this->get_field_name( 'display_results' ); ?>"><?php _e( 'Display Results:', $custom_google_search->text_domain ); ?></label>
			<select id="<?php echo $this->get_field_id( 'display_results' ); ?>" name="<?php echo $this->get_field_name( 'display_results' ); ?>" class="widefat" >
				<option value="1" <?php echo ( isset( $instance['display_results']) && 1 == $instance['display_results'] ) ? 'selected' : ''; ?> ><?php _e( 'in pop-up', $custom_google_search->text_domain ); ?></option>
				<option value="2" <?php echo ( isset( $instance['display_results']) && 2 == $instance['display_results'] ) ? 'selected' : ''; ?> ><?php _e( 'on bottom of widget', $custom_google_search->text_domain ); ?></option>
				<option value="3" <?php echo ( isset( $instance['display_results']) && 3 == $instance['display_results'] ) ? 'selected' : ''; ?> ><?php _e( 'on search page', $custom_google_search->text_domain ); ?></option>
			</select>
		</p>

	<?php
	}
}
?>