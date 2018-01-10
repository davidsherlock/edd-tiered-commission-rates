<?php
/**
 * Plugin Name:     Easy Digital Downloads - Tiered Commission Rates
 * Plugin URI:      https://sellcomet.com/downloads/tiered-commission-rates/
 * Description:     Reward your vendors with higher commission rates the more they sell or earn.
 * Version:         1.0.0
 * Author:          Sell Comet
 * Author URI:      https://sellcomet.com
 * Text Domain:     edd-tiered-commission-rates
 *
 * @package         EDD\TieredCommissionRates
 * @author          Sell Comet
 * @copyright       Copyright (c) 2017, Sell Comet
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'EDD_Tiered_Commission_Rates' ) ) {

    /**
     * Main EDD_Tiered_Commission_Rates class
     *
     * @since       1.0.0
     */
    class EDD_Tiered_Commission_Rates {

        /**
         * @var         EDD_Tiered_Commission_Rates $instance The one true EDD_Tiered_Commission_Rates
         * @since       1.0.0
         */
        private static $instance;

        public static $edd_commissions;

        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true EDD_Tiered_Commission_Rates
         */
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new EDD_Tiered_Commission_Rates();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
                self::$instance->hooks();

                self::$edd_commissions = new EDD_Tiered_Commission_Rates_Commissions();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin version
            define( 'EDD_TIERED_COMMISSION_RATES_VER', '1.0.0' );

            // Plugin path
            define( 'EDD_TIERED_COMMISSION_RATES_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_TIERED_COMMISSION_RATES_URL', plugin_dir_url( __FILE__ ) );
        }


        /**
         * Include necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {

            // Include user meta fields
            require_once EDD_TIERED_COMMISSION_RATES_DIR . 'includes/functions/user-meta.php';

            // Include helper functions
            require_once EDD_TIERED_COMMISSION_RATES_DIR . 'includes/functions/helper-functions.php';

            // Admin only requires
            if ( is_admin() ) {

              // Include admin settings
              require_once EDD_TIERED_COMMISSION_RATES_DIR . 'includes/admin/settings.php';

            }

            // Include the commissions plugin integrations
            require_once EDD_TIERED_COMMISSION_RATES_DIR . 'includes/integrations/plugin-commissions.php';

        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks() {

            // Handle licensing
            if( class_exists( 'EDD_License' ) && is_admin() ) {
                $license = new EDD_License( __FILE__, 'Tiered Commission Rates', EDD_TIERED_COMMISSION_RATES_VER, 'Sell Comet', null, 'https://sellcomet.com/', 222 );
            }
        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = EDD_TIERED_COMMISSION_RATES_DIR . '/languages/';
            $lang_dir = apply_filters( 'edd_commission_fees_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'edd-tiered-commission-rates' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'edd-tiered-commission-rates', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/edd-plugin-name/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/edd-plugin-name/ folder
                load_textdomain( 'edd-tiered-commission-rates', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/edd-plugin-name/languages/ folder
                load_textdomain( 'edd-tiered-commission-rates', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'edd-tiered-commission-rates', false, $lang_dir );
            }
        }

    }
} // End if class_exists check


/**
 * The main function responsible for returning the one true EDD_Tiered_Commission_Rates
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \EDD_Tiered_Commission_Rates The one true EDD_Tiered_Commission_Rates
 */
function EDD_Tiered_Commission_Rates_load() {
    if( ! class_exists( 'Easy_Digital_Downloads' ) ) {
        if( ! class_exists( 'EDD_Extension_Activation' ) ) {
            require_once 'includes/classes/class.extension-activation.php';
        }

        $activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation = $activation->run();
    } else {
        return EDD_Tiered_Commission_Rates::instance();
    }
}
add_action( 'plugins_loaded', 'EDD_Tiered_Commission_Rates_load' );
