<?php
/**
 * User meta functions
 *
 * @package     EDD\TieredCommissionRates
 * @subpackage  Admin/User
 * @copyright   Copyright (c) 2017, Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add fee rate and disable checkbox to user edit form
 *
 * @since  1.0.0
 * @param  object $user The user object
 * @return void
 */
function edd_tiered_commission_rates_user_meta( $user ) {
	?>
	<h3><?php _e('Easy Digital Downloads Tiered Commission Rates', 'edd-tiered-commission-rates'); ?></h3>
	<table class="form-table">
		<?php if ( current_user_can( 'manage_shop_settings' ) ) : ?>
		<tr>
			<th><label><?php _e('Disable Tiered Rates', 'edd-tiered-commission-rates'); ?></label></th>
			<td>
				<input name="edd_tiered_commission_rates_user_tiers_disabled" type="checkbox" id="edd_tiered_commission_rates_user_tiers_disabled" value="1"<?php checked( get_user_meta( $user->ID, 'edd_tiered_commission_rates_user_tiers_disabled', true ) ); ?> />
				<span class="description"><?php _e( 'Check this box if you wish to prevent tiered commission rates being applied to this user.', 'edd-tiered-commission-rates' ); ?></span>
			</td>
		</tr>
		<?php endif; ?>
	</table>
	<?php
}
add_action( 'show_user_profile', 'edd_tiered_commission_rates_user_meta' );
add_action( 'edit_user_profile', 'edd_tiered_commission_rates_user_meta' );


/**
 * Save the user meta/fields
 *
 * @since  1.0.0
 * @param  int $user_id The user ID
 * @return void
 */
function edd_tiered_commission_rates_save_user_meta( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}

	if ( current_user_can( 'manage_shop_settings' ) ) {

    if ( isset( $_POST['edd_tiered_commission_rates_user_tiers_disabled'] ) ) {
      update_user_meta( $user_id, 'edd_tiered_commission_rates_user_tiers_disabled', true );
    } else {
      delete_user_meta( $user_id, 'edd_tiered_commission_rates_user_tiers_disabled' );
    }

	}
}
add_action( 'personal_options_update', 'edd_tiered_commission_rates_save_user_meta' );
add_action( 'edit_user_profile_update', 'edd_tiered_commission_rates_save_user_meta' );
