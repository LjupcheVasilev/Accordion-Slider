<?php
// TODO : Add comments

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if( ! class_exists( 'AS_Uninstall' ) ) :
    class AS_Uninstall {
        /**
         * Plugin version.
         *
         * @since 1.0.0
         *
         * @var string
         */
        public $version = '1.0.0';

        /**
         * The singleton instance of AS_Uninstall.
         *
         * @since 1.0.0
         *
         * @var AS_Uninstall
         */
        private static $instance = null;

        /**
         * Returns the singleton instance of AS_Uninstall.
         *
         * Ensures only one instance of AS_Uninstall is/can be loaded.
         *
         * @since 1.0.0
         *
         * @return AS_Uninstall
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
            $this->uninstall();

            // Plugin has just loaded.
            do_action( 'as_uninstalled' );
        }

        private function uninstall() {
            if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();
            global $wpdb;
            $tableName = $wpdb->prefix . "as_slider";

            $wpdb->query("DROP TABLE IF EXISTS $tableName");
        }

    }
endif;

/**
 * Main instance of AS_Uninstall.
 *
 * Returns the main instance of AS_Uninstall.
 *
 * @since 1.0.0
 *
 * @return AS_Uninstall
 */
function uninstall_as() {
    return AS_Uninstall::get_instance();
}

uninstall_as();