<?php
/**
 * Integration functions to make Tiered Commission Rates compatible with EDD Commissions
 *
 * @package     EDD\TieredCommissionRates
 * @subpackage  Functions
 * @copyright   Copyright (c) 2017, Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Integration functions to make Tiered Commission Rates compatible with EDD Commissions
 *
 * @since 1.0.0
 */
class EDD_Tiered_Commission_Rates_Commissions {

	/**
	 * Get things started
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct() {

		if ( ! class_exists( 'EDDC' ) ) {
			return;
		}

		if ( ! defined( 'EDD_COMMISSIONS_VERSION' ) ){
			return;
		}

		// Make sure we are at the minimum version of EDD Commissions - which is 3.3.
		add_action( 'admin_notices', array( $this, 'too_old_notice' ) );

		// Render our "Check to disable tiered commission rates" checkbox on the commissions meta box
		add_action( 'eddc_metabox_after_enable', array( $this, 'edd_tiered_commission_rates_download_meta_render' ), 10, 1 );

		// Save our "Check to disable tiered commission rates" checkbox to post meta
		add_action( 'save_post', array( $this, 'edd_tiered_commission_rates_download_meta_box_save' ), 10, 1 );

		// Filters/Removes the "disabled" rates from the tiered commission rates
		add_filter( 'edd_tiered_commission_rates_filtered_rates', array( $this, 'remove_disabled_rates' ) );

		// Filters the commission rate - the main function for adjusting the commission rate
		add_filter( 'eddc_get_recipient_rate', array( $this, 'get_recipient_rate' ), 10, 3 );

		// If "Exclude Unpaid Statuses" is checked, only return "paid" commission statuses
		if ( edd_tiered_commission_rates_exclude_unpaid() ) {

			add_filter( 'get_commissions_totals_args', array( $this, 'get_commissions_totals_args_filter' ), 10, 1 );

			add_filter( 'get_monthly_commissions_totals_args', array( $this, 'get_monthly_commissions_totals_args_filter' ), 10, 1 );

			add_filter( 'get_commissions_count_args', array( $this, 'get_commissions_count_args_filter' ), 10, 1 );

			add_filter( 'get_monthly_commissions_count_args', array( $this, 'get_monthly_commissions_count_args_filter' ), 10, 1 );

		}

	}


	/**
	 * Make sure we are at the minimum version of EDD Commissions - which is 3.3.
	 *
	 * @since       1.0.0
	 * @access      public
	 * @return      void
	 */
	public function too_old_notice(){

		if ( defined( 'EDD_COMMISSIONS_VERSION' ) && version_compare( EDD_COMMISSIONS_VERSION, '3.4' ) == -1 ){
			?>
			<div class="notice notice-error">
				<p><?php echo __( 'EDD Commission Fees: Your version of EDD Commissions must be updated to version 3.4 or later to use the Commission Fees extension in conjunction with Commissions.', 'edd-commission-fees' ); ?></p>
			</div>
			<?php
		}
	}


	/**
	 * Retrieve the tiered rates
	 *
	 * @access public
	 * @since 1.0.0
	 * @return array
	 */
	public function get_rates() {
		$rates = edd_tiered_commission_rates_get_rates();

		/**
		 * Filters tiered rate values.
		 *
		 * @since 1.0
		 *
		 * @param array $rate_values Rate values.
		 */
		return apply_filters( 'edd_tiered_commission_rates_filtered_rates', array_values( $rates ) );
	}


	/**
	 * Removes disabled rates from consideration.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $rates Rate values.
	 * @return array Filtered rates
	 */
	public function remove_disabled_rates( $rates ) {

		// Bail if on rates edit screen.
		if ( is_admin() ) {
			return $rates;
		}

		foreach ( $rates as $index => $rate ) {
			if ( isset( $rate['disabled'] ) && 'on' === $rate['disabled'] ) {
				unset( $rates[ $index ] );
			}
		}

		return $rates;
	}


	/**
	 * Filter the  user commissions totals to only include "paid" statuses
	 *
	 * @since 1.0.0
	 * @access public
	 * @param       array $args The unfiltered query args
	 * @return      array $args The filtered query args
	 */
	public function get_commissions_totals_args_filter( $args ) {
		$args['status'] = array( 'paid' );

		return $args;
	}


	/**
	 * Get the total paid and unpaid commissions
	 *
	 * @since 1.0.0
	 * @access public
	 * @param       int $user_id The ID of the user to look up
	 * @return      string The total of unpaid commissions
	 */
	public function get_commissions_totals( $user_id = 0 ) {
		$total = edd_commissions()->commissions_db->sum( 'amount', apply_filters( 'get_commissions_totals_args', array( 'status' => array( 'paid', 'unpaid' ), 'user_id' => $user_id, 'number' => -1 ) ) );

		return edd_sanitize_amount( $total );
	}


	/**
	 * Filter the monthly user commissions totals to only include "paid" statuses
	 *
	 * @since 1.0.0
	 * @access public
	 * @param       array $args The unfiltered query args
	 * @return      array $args The filtered query args
	 */
	public function get_monthly_commissions_totals_args_filter( $args ) {
		$args['status'] = array( 'paid' );

		return $args;
	}


	/**
	 * Retrieves the paid and unpaid commissions total for the given commissions recipient.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param string $date         Date period to retrieve the referral count for.
	 * @param int    $user_id 		 Commission recipient user ID.
	 * @return int	 Number of paid and unpaid sales for the time period (based on now).
	 */
	public function get_monthly_commissions_totals( $date = '', $user_id = 0 ) {
		$commissions_args = apply_filters( 'get_monthly_commissions_totals_args', array(
			'user_id' => absint( $user_id ),
			'status'  => array( 'paid', 'unpaid' ),
		) );

		if ( ! empty( $date ) ) {
			switch ( $date ) {
				case 'month' :
					$date = array(
						'start' => date( 'Y-m-d H:i:s', strtotime( 'first day of', current_time( 'timestamp' ) ) ),
						'end'   => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
					);
					break;
			}
			$commissions_args['date'] = $date;
		}

		$commissions_args = apply_filters( 'get_monthly_commissions_totals', $commissions_args, $date, $user_id );

		$total = edd_commissions()->commissions_db->sum( 'amount', $commissions_args );
		return edd_sanitize_amount( $total );
	}


	/**
	 * Filter the user commissions count to only include "paid" statuses
	 *
	 * @since 1.0.0
	 * @access public
	 * @param       array $args The unfiltered query args
	 * @return      array $args The filtered query args
	 */
	public function get_commissions_count_args_filter( $args ) {
		$args['status'] = array( 'paid' );

		return $args;
	}


	/**
	 * Get a count of user commissions
	 *
	 * @since 1.0.0
	 * @access public
	 * @param       int $user_id The ID of the user to look up
	 * @param       array $status The statuses to look up
	 * @return      int The number of commissions for the user
	 */
	public function get_commissions_count( $user_id = false, $status = array( 'paid', 'unpaid' ) ) {
		$args = apply_filters( 'get_commissions_count_args', array(
			'status'  => $status,
			'user_id' => ! empty( $user_id ) ? $user_id : false,
			'number'  => - 1,
		) );

		$count = edd_commissions()->commissions_db->count( $args );

		return edd_sanitize_amount( $count );
	}


	/**
	 * Filter the user monthly commissions count to only include "paid" statuses
	 *
	 * @since 1.0.0
	 * @access public
	 * @param       array $args The unfiltered query args
	 * @return      array $args The filtered query args
	 */
	public function get_monthly_commissions_count_args_filter( $args ) {
		$args['status'] = array( 'paid' );

		return $args;
	}


	/**
	 * Retrieves the paid and unpaid commissions count for the given commissions recipient.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param string $date         Date period to retrieve the referral count for.
	 * @param int    $user_id 		 Commission recipient user ID.
	 * @return int	 Number of paid and unpaid sales for the time period (based on now).
	 */
	public function get_monthly_commissions_count( $date = '', $user_id = 0 ) {
		$commissions_args = apply_filters( 'get_monthly_commissions_count_args', array(
			'user_id' => absint( $user_id ),
			'status'  => array( 'paid', 'unpaid' ),
		) );

		if ( ! empty( $date ) ) {
			switch ( $date ) {
				case 'month' :
					$date = array(
						'start' => date( 'Y-m-d H:i:s', strtotime( 'first day of', current_time( 'timestamp' ) ) ),
						'end'   => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
					);
					break;
			}
			$commissions_args['date'] = $date;
		}

		$commissions_args = apply_filters( 'get_monthly_commissions_count', $commissions_args, $date, $user_id );

		$total = edd_commissions()->commissions_db->count( $commissions_args );
		return edd_sanitize_amount( $total );
	}


	/**
	 * Render the "disable tiered commission rates" checkbox on the commissions metabox
	 *
	 * @since       1.0.0
	 * @access      public
	 * @return      void
	 */
	public function edd_tiered_commission_rates_download_meta_render( $post_id ) {

		$enabled = get_post_meta( $post_id, '_edd_tiered_commision_rates_disabled', true ) ? true : false;

		?>
		<input type="hidden" name="edd_download_commission_meta_box_tiers_nonce" value="<?php echo wp_create_nonce( basename( __FILE__ ) ); ?>" />
		<tr id="edd_tiered_commission_rates_disabled_wrapper">
			<td class="edd_field_type_text" colspan="2">
				<input type="checkbox" name="edd_tiered_commission_rates_disabled" id="edd_tiered_commission_rates_disabled" value="1" <?php checked( true, $enabled, true ); ?>/>&nbsp;
				<label for="edd_tiered_commission_rates_disabled"><?php _e( 'Check to disable tiered commission rates', 'eddc' ); ?></label>
			</td>
		</tr>
		<?php
	}


	/**
	 * Save form data when save_post is called
	 *
	 * @since       1.0.0
	 * @access 			public
	 * @param       int $post_id The ID of the post being saved
	 * @global      object $post The WordPress post object for this download
	 * @return      void
	 */
	public function edd_tiered_commission_rates_download_meta_box_save( $post_id ) {
		global $post;

		// verify nonce
		if ( ! isset( $_POST['edd_download_commission_meta_box_tiers_nonce'] ) || ! wp_verify_nonce( $_POST['edd_download_commission_meta_box_tiers_nonce'], basename( __FILE__ ) ) ) {
			return $post_id;
		}

		// Check for auto save / bulk edit
		if ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
			return $post_id;
		}

		if ( isset( $_POST['post_type'] ) && 'download' != $_POST['post_type'] ) {
			return $post_id;
		}

		if ( ! current_user_can( 'edit_product', $post_id ) ) {
			return $post_id;
		}

		if ( isset( $_POST['edd_tiered_commission_rates_disabled'] ) ) {

			update_post_meta( $post_id, '_edd_tiered_commision_rates_disabled', true );

		} else {

			delete_post_meta( $post_id, '_edd_tiered_commision_rates_disabled' );

		}
	}


	/**
	 * The main function to adjust the commission recipient rate based on the tiered rates
	 *
	 * Note: Flat rate amounts are skipped over and left untouched.
	 *
	 * @since       1.0.0
	 * @param       float $rate the commission recipient rate
	 * @param       int $download_id the download id
	 * @param       int $user_id the user id
	 * @return      float the updated recipient rate
	 */
	public function get_recipient_rate( $rate, $download_id, $user_id ) {

		// Are tiered rates disabled on the download or user profile?
		if ( edd_tiered_commission_rates_user_tiers_disabled( $user_id ) || edd_tiered_commission_rates_download_tiers_disabled ( $download_id ) || edd_tiered_commission_rates_disabled() ) {
			return $rate;
		}

		$base_rate 			= $rate;

		$rates          = $this->get_rates();
		$tiers_expire 	= edd_tiered_commission_rates_expiration_enabled();
		$type 					= eddc_get_commission_type( $download_id );

		// Is the download using flat amounts? If override flat amounts isn't enabled, return default rate
		if ( 'flat' == $type ) {
			return $rate;
		}

		if ( ! empty( $rates ) && ! empty( $rate ) ) {

			// Start with highest tiers
			$rates = array_reverse( $rates );

			if ( $tiers_expire ) {
				$earnings	= $this->get_monthly_commissions_totals( 'month', $user_id );
				$sales		= $this->get_monthly_commissions_count( 'month', $user_id );
			} else {
				$earnings = $this->get_commissions_totals( $user_id );
				$sales 		= $this->get_commissions_count( $user_id );
			}

			// Loop through the rates to see which applies to this commission recipient
			foreach( $rates as $tiered_rate ) {

				if( empty( $tiered_rate['threshold'] ) || empty( $tiered_rate['rate'] ) ) {
					continue;
				}

				if( 'earnings' == $tiered_rate['type'] ) {

					if( $earnings >= edd_sanitize_amount( $tiered_rate['threshold'] ) ) {
						$rate = $tiered_rate['rate'];
						break;

					}

				} else {

					if( $sales >= $tiered_rate['threshold'] ) {

						$rate = $tiered_rate['rate'];
						break;

					}

				}

			}

			do_action( 'edd_tiered_commission_rates_get_recipient_rate', $base_rate, $rate, $download_id, $user_id );
		}

		return $rate;
	}


}
