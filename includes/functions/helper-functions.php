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
 * Is use download stats enabled?
 *
 * @access      public
 * @since 		1.0.0
 * @return 		boolean $ret True if disabled, false otherwise
 */
function edd_tiered_commission_rates_use_download_stats() {
	$ret = edd_get_option( 'edd_tiered_commission_rates_use_download_stats', false );
	return (bool) apply_filters( 'edd_tiered_commission_rates_use_download_states', $ret );
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


/**
 * Returns the total earnings for a specific user.
 *
 * @access      public
 * @since       1.0.0
 * @param       integer $user_id The ID of the user to look up
 * @return      float $earnings Total earnings for a certain user
 */
function edd_tiered_commission_rates_get_user_earnings( $user_id = 0 ) {
    if ( empty( $user_id ) ) {
        return false;
    }

    $download_ids = eddc_get_download_ids_of_user( $user_id );

    $earnings = array();

    if ( ! empty ( $download_ids ) && is_array( $download_ids ) ) {

        foreach( $download_ids as $download_id ) {
            $earnings[] = edd_get_download_earnings_stats( (int) $download_id );
        }

    }

    $total = array_sum( $earnings );

    return apply_filters( 'eddc_calc_commission_amount', $total, $download_ids, $user_id );
}


/**
 * Returns the total earnings for a specific user.
 *
 * @access      public
 * @since       1.0.0
 * @param       integer $user_id The ID of the user to look up
 * @return      float $earnings Total earnings for a certain user
 */
function edd_tiered_commission_rates_get_user_sales( $user_id = 0 ) {
    if ( empty( $user_id ) ) {
        return false;
    }

    $download_ids = eddc_get_download_ids_of_user( $user_id );

    $sales = array();

    if ( ! empty ( $download_ids ) && is_array( $download_ids ) ) {

        foreach( $download_ids as $download_id ) {
            $sales[] = edd_get_download_sales_stats( (int) $download_id );
        }

    }

    $count = array_sum( $sales );

    return apply_filters( 'eddc_calc_commission_amount', $count, $download_ids, $user_id );
}


/**
 * This will take a tier threshold amount and type and format it correctly for output.
 * For example, if the rate is 5 and the tier type is "earnings", it will return "$5.00" as a string.
 *
 * @since       1.0.0
 * @param       integer $unformatted_rate This is the number representing the rate.
 * @param       string $tier_type This is the type of commission.
 * @return      string $formatted_rate This is the rate formatted for output.
 */
function edd_tiered_commission_rates_format_threshold( $unformatted_threshold, $tier_type ){

	// If the tier type is "sales"
	if ( 'sales' == $tier_type ) {

		// Leave untouched
		$formatted_threshold = $unformatted_threshold;

	} else {

		// If the rate is anything else, format it as if it were a flat rate, or "dollar" amount. We add the currency symbol before it. For example, "$5".
		$formatted_threshold = edd_currency_filter( edd_sanitize_amount( $unformatted_threshold ) );

	}

	// Filter the formatted threshold so it can be modified if needed
	return apply_filters( 'edd_tiered_commission_rates_format_rate', $formatted_threshold, $unformatted_threshold, $tier_type );
}
