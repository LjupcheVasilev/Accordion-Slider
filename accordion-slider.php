<?php
/**
 * Plugin Name: Accordion slider
 * description: A plugin to show images or posts in accordion-like style
 * Version: 1.0.0
 * Author: Ljupche Vasilev
 * Author URI: http://ljupchevasilev.com/
 * Tested up to: 5.1.1
 * License: GPL2
 *
 * @package Accordion-slider
 * @author  Ljupche-Vasilev
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/*
 * Global variables
 */

define( 'AS_ROOT_PATH',   dirname( __FILE__ ) );
define( 'AS_ROOT_URL',    plugin_dir_url( __FILE__ ) );


if( ! class_exists( 'Accordion_Slider' ) ) :
    /**
     * The main class.
     *
     * @since 1.0.0
     */
    class Accordion_Slider {
        /**
         * Plugin version.
         *
         * @since 1.0.0
         *
         * @var string
         */
        public $version = '1.0.0';

        /**
         * The singleton instance of Accordion_Slider.
         *
         * @since 1.0.0
         *
         * @var Accordion_Slider
         */
        private static $instance = null;

        /**
         * Returns the singleton instance of Accordion_Slider.
         *
         * Ensures only one instance of Accordion_Slider is/can be loaded.
         *
         * @since 1.0.0
         *
         * @return Accordion_Slider
         */
        public static function get_instance() {
            if( null === self::$instance ) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * The constructor.
         *
         * Private constructor to make sure it can not be called directly from outside the class.
         *
         * @since 1.0.0
         */
        private function __construct() {
            $this->includes();
            $this->init_includes();
            $this->hooks();

            // Plugin has just loaded.
            do_action( 'as_loaded' );
        }

        /**
         * Update plugin settings.
         *
         * @since 1.0.0
         */
        private static function settings() {
            $settings = get_option( 'accordion_slider', array() );
        }

        /**
         * Includes the required files.
         *
         * @since 1.0.0
         */
        public function includes() {

            /**
             * REST API includes
             */
            include_once AS_ROOT_PATH . '/includes/class.as_rest_api.php';

            /**
             * Global includes.
             */
            include_once AS_ROOT_PATH . '/includes/class.as_shortcodes.php';

            /**
             * Back-end includes.
             */
            include_once AS_ROOT_PATH . '/includes/admin/class.as_admin.php';
        }

        /**
         * Init the includes
         */
        public function init_includes() {
            $as_rest_api = new AS_REST_API();
            $as_shortcodes = new AS_Shortcodes();
            $as_admin = new AS_Admin();
        }

        /**
         * Plugin hooks.
         *
         * @since 1.0.0
         */
        public function hooks() {
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'front_end_scripts' ) );
        }

        /**
         * Loads plugin styles and scripts for admin dashboard.
         *
         * @since 1.0.0
         */
        public function admin_scripts($hook) {
            if ( is_admin() && ( $hook == 'toplevel_page_accordion-slider' || $hook == 'accordion-slider-settings_page_as_add_slider' ) ) {
                wp_register_style('as_style', AS_ROOT_URL . '/css/admin_style.css');
                wp_enqueue_style('as_style');

                wp_register_style('bootstrap', AS_ROOT_URL . '/css/bootstrap.css');
                wp_enqueue_style('bootstrap');
                wp_enqueue_script('jquery');

                wp_enqueue_script('bootstrap', AS_ROOT_URL . 'js/bootstrap.js', array('jquery'));
                wp_enqueue_script('vue', AS_ROOT_URL . 'js/vue.js', array('jquery'));
                wp_enqueue_script('admin_script', AS_ROOT_URL . 'js/admin_script.js', array('bootstrap'));

//                For future versions
//                wp_enqueue_script('jquery-ui-resizable');
//                wp_enqueue_script('jquery-ui-draggable');

                wp_localize_script('admin_script', 'ACObject', array(
                    'home_url'  => home_url()
                ));
                wp_enqueue_media();
            }
        }

        /**
         * Loads plugin styles and scripts for front end.
         *
         * @since 1.0.0
         */
        public function front_end_scripts() {
            wp_enqueue_style('as_style', AS_ROOT_URL . 'css/front_end_style.css', array());

            wp_enqueue_script( 'jquery');

            wp_enqueue_script('bootstrap', AS_ROOT_URL . 'js/bootstrap.js', array('jquery'));
            wp_enqueue_script('vue', AS_ROOT_URL . 'js/vue.js', array('jquery'));

            wp_enqueue_script('as_script', AS_ROOT_URL . 'js/front_end_script.js', array('vue'), '', true);
            wp_localize_script('as_script', 'ACObject', array(
                'home_url'  => home_url()
            ));


        }

        /**
         * Activation hooks.
         *
         * @since 1.0.0
         */
        public static function activate() {
            self::settings();

            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            $table_name_slider = $wpdb->prefix . 'as_slider';

            $sql = "CREATE TABLE $table_name_slider (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                name varchar(50) NOT NULL,
                slider_id mediumint(9) NOT NULL,
                image_url varchar(300) NULL,
                image_name varchar(200) NULL,
                UNIQUE KEY id (id)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        }

        /**
         * Deactivation hooks.
         *
         * @since 1.0.0
         */
        public static function deactivate() {

        }

        /**
         * Uninstall hooks.
         *
         * @since 1.0.0
         */
        public static function uninstall() {
            include_once AS_ROOT_PATH . 'uninstall.php';
        }


    }

    /**
     * Main instance of Accordion_Slider.
     *
     * Returns the main instance of Accordion_Slider.
     *
     * @since 1.0.0
     *
     * @return Accordion_Slider
     */
    function init_as() {
        return Accordion_Slider::get_instance();
    }

    // Global for backwards compatibility.
    $GLOBALS['as'] = init_as();

    // Plugin hooks.
    register_activation_hook( __FILE__,   array( 'Accordion_Slider', 'activate' ) );
    register_deactivation_hook( __FILE__, array( 'Accordion_Slider', 'deactivate' ) );
    register_uninstall_hook( __FILE__,    array( 'Accordion_Slider', 'uninstall' ) );
endif;

?>