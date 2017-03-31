<?php

$settings = $this->settings;

?>

<div class="wrap">
    <h2><?php _e( 'Settings', $this->text_domain ) ?></h2>

    <?php
        //Display status message
        if ( isset( $_GET['dmsg'] ) ) {
            ?><div id="message" class="updated fade"><p><?php echo urldecode( $_GET['dmsg'] ); ?></p></div><?php
        }

    ?>

    <form name="cgs_form" id="cgs_form" method="post">

        <h3><?php _e( 'General', $this->text_domain ) ?></h3>
        <table class="form-table">
            <tbody >
                <tr id="cgs-code">
                    <th>
                        <label for="settings_engine_id"><?php _e( 'Embed Code/Search ID', $this->text_domain ) ?>:</label>
                        <span class="description">
                            <?php _e( '(required)', $this->text_domain ) ?>
                        </span>
                        <br />
                        <span class="description">
                            <?php _e( "Don't have it? Get it ", $this->text_domain ) ?> <a href="http://www.google.com/cse/" target="_blank"><?php _e( 'here', $this->text_domain ) ?></a>
                        </span>
                    </th>
                    <td>
                        <textarea name="settings[embed_code]" id="settings_embed_code" cols="80" rows="8"><?php echo ( isset( $settings['embed_code'] ) ) ? stripslashes( $settings['embed_code'] ) : ''; ?></textarea>
                        <br />
                        <span class="description">
                            <?php _e( 'Paste the Custom Search embed code or Search engine unique ID here. Note that any customized styles will be stripped out.', $this->text_domain ) ?>
                        </span>
                    </td>
                </tr>

                <tr id="cgs-display-results-options">
                    <th>
                        <label for="page_mode"><?php _e( 'Display search results as', $this->text_domain ) ?>:</label>
                    </th>
                    <td>
                        <select name="settings[page_mode]" id="settings_style" >
                            <option value="page" <?php echo ( isset( $settings['page_mode'] ) && 'page' == $settings['page_mode'] ) ? 'selected' : ''; ?> ><?php _e( 'Standard Page', $this->text_domain ) ?></option>
                            <option value="search" <?php echo ( isset( $settings['page_mode'] ) && 'search' == $settings['page_mode'] ) ? 'selected' : ''; ?> ><?php _e( 'Search Page', $this->text_domain ) ?></option>
                            <option value="generated" <?php echo ( isset( $settings['page_mode'] ) && 'generated' == $settings['page_mode'] ) ? 'selected' : ''; ?> ><?php _e( 'Generated Page (risky)', $this->text_domain ) ?></option>
                            <option value="custom_page" <?php selected( isset( $settings['page_mode'] ) && 'custom_page' == $settings['page_mode'] ); ?> ><?php _e( 'Custom Page', $this->text_domain ) ?></option>
                        </select>
                        <br />
                        <span class="description">
                            <?php _e( 'Decide what method would you like to use to display search results. Its best to try them and choose the one that works best with your theme.', $this->text_domain ) ?>
                        </span>
                    </td>
                </tr>

                <tr id="cgs-show-sidebar" class="<?php echo ( ! isset( $settings['page_mode'] ) || 'generated' != $settings['page_mode'] ) ? 'hidden' : ''; ?>">
                    <th>
                        <label for="settings_show_sidebar"><?php _e( 'Show sidebar on search page', $this->text_domain ) ?>:</label>
                    </th>
                    <td>
                        <input name="settings[show_sidebar]" id="settings_show_sidebar" type="checkbox" value="1" <?php echo ( isset( $settings['show_sidebar'] ) && '1' == $settings['show_sidebar'] ) ? 'checked' : '' ; ?> />

                        <span class="description">
                            <?php _e( 'Only works when search results are displayed as generated page.', $this->text_domain ) ?>
                        </span>
                    </td>
                </tr>

                <tr id="cgs-search-results-page" class="<?php echo ( ! isset( $settings['page_mode'] ) || 'custom_page' != $settings['page_mode'] ) ? 'hidden' : ''; ?>">
                    <th><?php _e( 'Search Result Page', $this->text_domain ); ?></th>
                    <td>
                        <?php
                        $option_name    = 'search_result_page';
                        $page_shortcode = '[cgs_search_result]';
                        $pages_args     = array(
                                'selected'          => ( isset( $settings[ $option_name ] ) ? $settings[ $option_name ] : 0 ),
                                'echo'              => 1,
                                //'show_option_none'  => ' ',
                                'option_none_value' => 0,
                                'name'              => 'settings[' . $option_name . ']'
                        );

                        wp_dropdown_pages( $pages_args );
                        ?>
                        <p class='description'><?php printf( __( 'Select the page where you have %s shortcode. Please note that this page will be used if "Display search results as" option is selected as "Custom Page".', $this->text_domain ), '<strong>' . $page_shortcode . '</strong>' ); ?></p>
                    </td>
                </tr>
                
                <tr id="cgs-same-window">
                    <th>
                        <label for="settings_same_window"><?php _e( 'Open clicked search result in same window', $this->text_domain ) ?>:</label>
                    </th>
                    <td>
                        <input name="settings[same_window]" id="settings_same_window" type="checkbox" value="1" <?php echo ( isset( $settings['same_window'] ) && '1' == $settings['same_window'] ) ? 'checked' : '' ; ?> />
                    </td>
                </tr>

                <tr id="cgs-hide-buton">
                    <th>
                        <label for="settings_hide_button"><?php _e( 'Hide search button', $this->text_domain ) ?>:</label>
                    </th>
                    <td>
                        <input name="settings[hide_button]" id="settings_hide_button" type="checkbox" value="1" <?php echo ( isset( $settings['hide_button'] ) && '1' == $settings['hide_button'] ) ? 'checked' : '' ; ?> />
                    </td>
                </tr>                
                
            </tbody>

        </table>

        <p class="submit">
            <?php wp_nonce_field( 'save_cgs' ); ?>
            <input type="submit" name="save_cgs" class="button-primary" value="<?php _e( 'Save Changes', $this->text_domain ) ?>" />
        </p>
    </form>
</div>


