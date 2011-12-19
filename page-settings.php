<?php

$settings = $this->settings;

?>

<div class="wrap">
    <h2><?php _e( 'Custom Google Search - Settings', $this->text_domain ) ?></h2>

    <?php
        //Display status message
        if ( isset( $_GET['dmsg'] ) ) {
            ?><div id="message" class="updated fade"><p><?php echo urldecode( $_GET['dmsg'] ); ?></p></div><?php
        }

    ?>

    <form name="cgs_form" method="post">

        <h3><?php _e( 'General', $this->text_domain ) ?></h3>
        <table class="form-table">
            <tbody >
                <tr>
                    <th>
                        <label for="settings_engine_id"><?php _e( 'Search Engine ID', $this->text_domain ) ?>:</label>
                    </th>
                    <td>
                        <input type="text" name="settings[engine_id]" id="settings_engine_id" value="<?php echo ( isset( $settings['engine_id'] ) ) ? $settings['engine_id'] : ''; ?>" size=40/>
                        <?php _e( 'You do not have it? Then get it', $this->text_domain ) ?> <a href="http://www.google.com/cse/" target="_blank"><?php _e( 'here', $this->text_domain ) ?></a>
                        <br />
                        <span class="description">
                            <?php _e( 'look on Google CSE menu "Control Panel->Basics" field "Unique identifier of a search engine". example: 0000000000000000000000:e6-h2-xhinm', $this->text_domain ) ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="settings_style"><?php _e( 'Style', $this->text_domain ) ?>:</label>
                    </th>
                    <td>
                        <select name="settings[style]" id="settings_style" >
                            <option value="DEFAULT" <?php echo ( isset( $settings['style'] ) && 'DEFAULT' == $settings['style'] ) ? 'selected' : ''; ?> ><?php _e( 'Default', $this->text_domain ) ?></option>
                            <option value="BUBBLEGUM" <?php echo ( isset( $settings['style'] ) && 'BUBBLEGUM' == $settings['style'] ) ? 'selected' : ''; ?> ><?php _e( 'Purple', $this->text_domain ) ?></option>
                            <option value="GREENSKY" <?php echo ( isset( $settings['style'] ) && 'GREENSKY' == $settings['style'] ) ? 'selected' : ''; ?> ><?php _e( 'Green', $this->text_domain ) ?></option>
                            <option value="ESPRESSO" <?php echo ( isset( $settings['style'] ) && 'ESPRESSO' == $settings['style'] ) ? 'selected' : ''; ?> ><?php _e( 'Coffee', $this->text_domain ) ?></option>
                            <option value="SHINY" <?php echo ( isset( $settings['style'] ) && 'SHINY' == $settings['style'] ) ? 'selected' : ''; ?> ><?php _e( 'Silver', $this->text_domain ) ?></option>
                            <option value="MINIMALIST" <?php echo ( isset( $settings['style'] ) && 'MINIMALIST' == $settings['style'] ) ? 'selected' : ''; ?> ><?php _e( 'Minimalistic', $this->text_domain ) ?></option>
                        </select>
                        <br />
                        <span class="description">
                            <?php _e( 'Color scheme for the search box.', $this->text_domain ) ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="settings_hide_button"><?php _e( 'Hide search button', $this->text_domain ) ?>:</label>
                    </th>
                    <td>
                        <input name="settings[hide_button]" id="settings_hide_button" type="checkbox" value="1" <?php echo ( isset( $settings['hide_button'] ) && '1' == $settings['hide_button'] ) ? 'checked' : '' ; ?> />
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="settings_show_sidebar"><?php _e( 'Show sidebar on search page', $this->text_domain ) ?>:</label>
                    </th>
                    <td>
                        <input name="settings[show_sidebar]" id="settings_show_sidebar" type="checkbox" value="1" <?php echo ( isset( $settings['show_sidebar'] ) && '1' == $settings['show_sidebar'] ) ? 'checked' : '' ; ?> />
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php _e( "Specify your website's protocol", $this->text_domain ) ?>:
                    </th>
                    <td>
                        <input type="radio" name="settings[protocol]" id="protocol_http" value="http:" <?php echo ( !isset( $settings['protocol'] ) || 'http:' == $settings['protocol'] ) ? 'checked' : '' ; ?> />
                        <label for="protocol_http"><?php _e( 'HTTP', $this->text_domain ) ?>&nbsp;&nbsp;</label>

                        <input type="radio" name="settings[protocol]" id="protocol_https" value="https:" <?php echo ( isset( $settings['protocol'] ) && 'https:' == $settings['protocol'] ) ? 'checked' : '' ; ?> />
                        <label for="protocol_https"><?php _e( 'HTTPS', $this->text_domain ) ?>&nbsp;&nbsp;</label>

                        <input type="radio" name="settings[protocol]" id="protocol_relative" value="relative" <?php echo ( isset( $settings['protocol'] ) && 'relative' == $settings['protocol'] ) ? 'checked' : '' ; ?> />
                        <label for="protocol_relative"><?php _e( 'Protocol-relative URLs', $this->text_domain ) ?>&nbsp;&nbsp;</label>

                        <br />
                        <span class="description">
                            <?php _e( "Websites that use SSL (HTTPS) should use the HTTPS version of the code. Protocol relative URLs allow the browser to use the same protocol as your website's protocol.", $this->text_domain ) ?>
                        </span>
                    </td>
                </tr>
            </tbody>

        </table>

        <h3><?php _e( 'Widget', $this->text_domain ) ?></h3>
        <table class="form-table">
            <tbody >
                <tr>
                    <th>
                        <label for="settings_popup_theme"><?php _e( 'Theme for Popup', $this->text_domain ) ?>:</label>
                    </th>
                    <td>
                        <select name="settings[popup_theme]" id="settings_popup_theme" >
                            <?php
                            //display all themes of popup for select
                            $popup_themes = $this->get_popup_themes();
                            if ( is_array( $popup_themes) ) {
                                foreach( $popup_themes as $popup_theme ) {

                            ?>
                                <option value="<?php echo $popup_theme['name'] ?>" <?php echo ( isset( $settings['popup_theme'] ) && $popup_theme['name'] == $settings['popup_theme'] ) ? 'selected' : ''; ?> ><?php echo $popup_theme['name']; ?></option>
                            <?php
                                }
                            }
                            ?>
                        </select>
                        <span class="description">
                            <?php _e( "(It's for pop-up window with results from search box in the widget)", $this->text_domain ) ?>
                        </span>
                        <br />
                        <span class="description">
                            <?php _e( 'Note: you can use any jQuery UI theme - only upload the theme folder to /custom-google-search/style/popup_themes/. And rename the .css file from "jquery-ui-x.x.x.custom.css" to "custom.css". And you will can choose theme in this selectbox.', $this->text_domain ) ?>
                        </span>
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

