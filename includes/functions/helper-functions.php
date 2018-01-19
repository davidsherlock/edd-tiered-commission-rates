<?php
/**
 * Helper Functions
 *
 * @package     EDD\Tiered_Commission_Rates
 * @subpackage  Functions
 * @copyright   Copyright (c) Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

 // Exit if accessed directly
 if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Is exclude unpaid commissions enabled?
 *
 * @access      public
 * @since 		1.0.0
 * @return 		boolean $ret True if disabled, false otherwise
 */
function edd_tiered_commission_rates_exclude_unpaid() {
	$ret = edd_get_option( 'edd_tiered_commission_rates_exclude_unpaid', false );
	return (bool) apply_filters( 'edd_tiered_commission_rates_exclude_unpaid', $ret );
}


/**
 * Is rates expiration enabled?
 *
 * @access      public
 * @since 		1.0.0
 * @return 		boolean $ret True if disabled, false otherwise
 */
function edd_tiered_commission_rates_expiration_enabled() {
	$ret = edd_get_option( 'edd_tiered_commission_rates_expiration', false );
	return (bool) apply_filters( 'edd_tiered_commission_rates_expiration_enabled', $ret );
}


/**
 * Are tiered rates disabled on this download?
 *
 * @access      public
 * @since 		1.0.0
 * @return		boolean
 */
function edd_tiered_commission_rates_download_tiers_disabled( $download_id = 0 ) {
	$ret = (bool) get_post_meta( $download_id, '_edd_tiered_commision_rates_disabled', true );
	return apply_filters( 'edd_tiered_commission_rates_download_tiers_disabled', $ret, $download_id );
}


/**
 * Are tiered rates disabled on this user?
 *
 * @access      public
 * @since 		1.0.0
 * @return 		boolean
 */
function edd_tiered_commission_rates_user_tiers_disabled( $user_id = 0 ) {
	$ret = (bool) get_user_meta( $user_id, 'edd_tiered_commission_rates_user_tiers_disabled', true );
	return apply_filters( 'edd_tiered_commission_rates_user_tiers_disabled', $ret, $user_id );
}


/**
 * Retrieve tiered commission rates
 *
 * @access      public
 * @since 		1.0.0
 * @return 		array Defined tiered commission rates
 */
function edd_tiered_commission_rates_get_rates() {
	$rates = get_option( 'edd_tiered_commission_rates', array() );
	return apply_filters( 'edd_tiered_commission_rates_get_rates', $rates );
}


/**
 * Sanitizes the tier commission rate value
 *
 * @access      public
 * @since		1.0.0
 * @param		float $rate the original value
 * @return 		float $rate sanitized rate
 */
function edd_tiered_commission_rates_sanitize_rate( $rate ) {
    // Remove currency and percentage symbols
    $rate = str_replace( '%', '', $rate );
    $rate = str_replace( '$', '', $rate );

	if ( $rate < 0 || ! is_numeric( $rate ) ) {
		$rate = '';
	}

	$rate = ( is_numeric( $rate ) && $rate < 1 ) ? $rate * 100 : $rate;

	if ( is_numeric( $rate ) ) {
		$rate = round( $rate, 2 );
	}

	return $rate;
}
