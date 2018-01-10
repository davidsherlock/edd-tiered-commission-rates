<?php
/**
 * Metabox Functions
 *
 * @package     EDD\TieredCommissionRates
 * @subpackage  Functions
 * @copyright   Copyright (c) 2017, Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Are tiered commission rates disabled globally?
 *
 * @since 1.0.0
 * @return bool $ret True if disabled, false otherwise
 */
function edd_tiered_commission_rates_disabled() {
	$ret = edd_get_option( 'edd_tiered_commission_rates_disabled', false );
	return (bool) apply_filters( 'edd_tiered_commission_rates_disabled', $ret );
}


/**
 * Is exclude unpaid commissions enabled?
 *
 * @since 1.0.0
 * @return bool $ret True if disabled, false otherwise
 */
function edd_tiered_commission_rates_exclude_unpaid() {
	$ret = edd_get_option( 'edd_tiered_commission_rates_exclude_unpaid', false );
	return (bool) apply_filters( 'edd_tiered_commission_rates_exclude_unpaid', $ret );
}

/**
 * Is rates expiration enabled?
 *
 * @since 1.0.0
 * @return bool $ret True if disabled, false otherwise
 */
function edd_tiered_commission_rates_expiration_enabled() {
	$ret = edd_get_option( 'edd_tiered_commission_rates_expiration', false );
	return (bool) apply_filters( 'edd_tiered_commission_rates_expiration_enabled', $ret );
}


/**
 * Are tiered rates disabled on this download?
 *
 * @since 1.0.0
 * @return bool
 */
function edd_tiered_commission_rates_download_tiers_disabled( $download_id = 0 ) {
	$ret = (bool) get_post_meta( $download_id, '_edd_tiered_commision_rates_disabled', true );
	return apply_filters( 'edd_tiered_commission_rates_download_tiers_disabled', $ret, $download_id );
}


/**
 * Are tiered rates disabled on this user?
 *
 * @since 1.0.0
 * @return bool
 */
function edd_tiered_commission_rates_user_tiers_disabled( $user_id = 0 ) {
	$ret = (bool) get_user_meta( $user_id, 'edd_tiered_commission_rates_user_tiers_disabled', true );
	return apply_filters( 'edd_tiered_commission_rates_user_tiers_disabled', $ret, $user_id );
}


/**
 * Retrieve tiered commission rates
 *
 * @since 1.0.0
 * @return array Defined tiered commission rates
 */
function edd_tiered_commission_rates_get_rates() {
	$rates = get_option( 'edd_tiered_commission_rates', array() );
	return apply_filters( 'edd_tiered_commission_rates_get_rates', $rates );
}


/**
 * Get the paid (and unpaid) commissions totals
 *
 * @since		1.0.0
 * @param		int $user_id The ID of the user to look up
 * @return	string The total of paid and unpaid commissions
 */
function edd_tiered_commission_rates_get_commissions_totals( $user_id = 0 ) {
	$tiered_commission_rates = new EDD_Tiered_Commission_Rates_Commissions();
	$total = $tiered_commission_rates->get_commissions_totals( $user_id );
	return edd_sanitize_amount( $total );
}


/**
 * Get the paid (and unpaid) monthly commissions totals
 *
 * @since		1.0.0
 * @param		string $date Date period to retrieve the referral count for.
 * @param		int $user_id The ID of the user to look up
 * @return	string The total of paid and unpaid monthly commissions
 */
function edd_tiered_commission_rates_get_monthly_commissions_totals( $date = '', $user_id = 0 ) {
	$tiered_commission_rates = new EDD_Tiered_Commission_Rates_Commissions();
	$total = $tiered_commission_rates->get_monthly_commissions_totals( $date, $user_id );
	return edd_sanitize_amount( $total );
}


/**
 * Get the paid (and unpaid) commissions count
 *
 * @since		1.0.0
 * @param		int $user_id The ID of the user to look up
 * @param		array $status The statuses to look up
 * @return	string The paid and unpaid commissions count
 */
function edd_tiered_commission_rates_get_commissions_count( $user_id = false, $statuses = array( 'paid', 'unpaid' ) ) {
	$tiered_commission_rates = new EDD_Tiered_Commission_Rates_Commissions();
	$count = $tiered_commission_rates->get_commissions_count( $user_id, $statuses );
	return $count;
}


/**
 * Get the paid (and unpaid) monthly commissions
 *
 * @since		1.0.0
 * @param		string $date Date period to retrieve the referral count for.
 * @param		int $user_id The ID of the user to look up
 * @return	string The paid and unpaid monthly commissions count
 */
function edd_tiered_commission_rates_get_monthly_commissions_count( $date = '', $user_id = 0 ) {
	$tiered_commission_rates = new EDD_Tiered_Commission_Rates_Commissions();
	$count = $tiered_commission_rates->get_monthly_commissions_count( $date, $user_id );
	return $count;
}

/**
 * Sanitize values to an absolute number, rounded to the required decimal place
 *
 * Allows zero values, but ignores truly empty values.
 *
 * The correct type will be used automatically, depending on its value:
 *
 * - Whole numbers (including numbers with a 0 value decimal) will be return as ints
 * - Decimal numbers will be returned as floats
 * - Decimal numbers ending with 0 will be returned as strings
 *
 * 1     => (int) 1
 * 1.0   => (int) 1
 * 0.00  => (int) 0
 * 1.01  => (float) 1.01
 * 1.019 => (float) 1.02
 * 1.1   => (string) 1.10
 * 1.10  => (string) 1.10
 * 1.199 => (string) 1.20
 *
 * @since		1.0.0
 * @param  	mixed $val
 * @param  	int $precision  Number of required decimal places (optional)
 * @return 	mixed Returns an int, float or string on success, null when empty
 */
function edd_tiered_commission_rates_abs_number_round( $val, $precision = 2 ) {

	// 0 is a valid value so we check only for other empty values
	if ( is_null( $val ) || '' === $val || false === $val ) {

		return;
	}

	$period_decimal_sep   = preg_match( '/\.\d{1,2}$/', $val );
	$comma_decimal_sep    = preg_match( '/\,\d{1,2}$/', $val );
	$period_space_thousands_sep = preg_match( '/\d{1,3}(?:[.|\s]\d{3})+/', $val );
	$comma_thousands_sep        = preg_match( '/\d{1,3}(?:,\d{3})+/', $val );

	// Convert period and space thousand separators.
	if ( $period_space_thousands_sep  && 0 === preg_match( '/\d{4,}$/', $val ) ) {
		$val = str_replace( ' ', '', $val );

		if ( ! $comma_decimal_sep ) {
			if ( ! $period_decimal_sep ) {
				$val = str_replace( '.', '', $val );
			}
		} else {
			$val = str_replace( '.', ':', $val );
		}
	}

	// Convert comma decimal separators.
	if ( $comma_decimal_sep ) {
		$val = str_replace( ',', '.', $val );
	}

	// Clean up temporary replacements.
	if ( $period_space_thousands_sep && $comma_decimal_sep || $comma_thousands_sep ) {
		$val = str_replace( array( ':', ',' ), '', $val );
	}

	// Value cannot be negative
	$val = abs( floatval( $val ) );

	// Decimal precision must be a absolute integer
	$precision = absint( $precision );

	// Enforce the number of decimal places required (precision)
	$val = sprintf( ( round( $val, $precision ) == intval( $val ) ) ? '%d' : "%.{$precision}f", $val );

	// Convert number to the proper type (int, float, or string) depending on its value
	if ( false === strpos( $val, '.' ) ) {

		$val = absint( $val );

	}

	return $val;

}
