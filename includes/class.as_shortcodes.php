<?php
/**
 * The file that defines the rest api plugin class
 *
 * @since      1.0.0
 * @package    Test_Login
 */

class AS_Shortcodes {

    /**
     * The version of the REST API endpoint.
     *
     * @since 1.0.0
     * @access private
     * @var string
     */
    var $my_version   = '1';

    /**
     * Initialize the class and register the hooks for the shortcode.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * Register the shortcode and connect it with a callback function
     *
     * @since 1.0.0
     */
    function hooks() {
        add_shortcode('as', array( $this, 'shortcode_handler' ) );
    }

    /**
     * The callback function for the shortcode. Here we get the attributes sent with the shortcode
     * and we output the slider's front-end.
     *
     * @since 1.0.0
     * @param $atts
     * @return string
     */

    function shortcode_handler($atts) {
        return "<div class='row slider_container' slider='" . $atts['slider_id'] . "'  >" .
            "<div class='as_image_container' v-bind:style=\"{ background: 'url(' + image.image_url + ')'}\" v-for='image of images' v-if='images.length > 0'>" .
            "<a href='#'>" .
            "<p class='as_caption'>{{image.image_name}}</p>" .
            "</a>" .
            "</div>" .
            "</div>";

//        <img :src="image.image_url" alt="" class="as_image"/>
    }

}