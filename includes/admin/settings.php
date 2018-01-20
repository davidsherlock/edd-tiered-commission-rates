<?php
/**
 * Extension settings
 *
 * @package     EDD\Tired_Commission_Rates
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

 // Exit if accessed directly
 if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Registers the new Tiered Commission Rates options in Commissions Settings
 *
 * @access		public
 * @since       1.0.0
 * @param       $settings array the existing plugin settings
 * @return      array The new EDD settings array with commissions added
 */
function edd_tiered_commission_rates_settings( $settings ) {
	$tiered_commission_rates_settings = array(
		array(
			'id'      => 'edd_tiered_commission_rates_header',
			'name'    => '<strong>' . __( 'Tiered Rates', 'edd-tiered-commission-rates' ) . '</strong>',
			'desc'    => '',
			'type'    => 'header',
			'size'    => 'regular',
		),
		array(
			'id'      => 'edd_tiered_commission_rates',
			'name'    => __( 'Rates', 'edd-tiered-commission-rates' ),
			'desc' 		=> __( 'Add tiered rates for specific sales and earnings thresholds. Add rates lowest to highest. Enter rates as percentages, such as 6.5 for 6.5%.', 'edd-tiered-commission-rates' ),
			'type'    => 'tiered_rates',
		),
		array(
			'id'      => 'edd_tiered_commission_rates_expiration',
			'name'    => __( 'Rate Expiration', 'edd-tiered-commission-rates' ),
			'desc'    => __( 'Check this box to reset tiered commission rates on the 1st of every month.', 'edd-tiered-commission-rates' ),
			'type'    => 'checkbox',
		),
		array(
			'id'      => 'edd_tiered_commission_rates_exclude_unpaid',
			'name'    => __( 'Exclude Unpaid Statuses', 'edd-tiered-commission-rates' ),
			'desc'    => __( 'By default, tiered rate conditions include paid and unpaid commission statuses. By checking this box, unpaid commissions will be excluded.', 'edd-tiered-commission-rates' ),
			'type'    => 'checkbox',
		),
        array(
            'id'      => 'edd_tiered_commission_rates_use_download_stats',
            'name'    => __( 'Use Download Stats', 'edd-tiered-commission-rates' ),
            'desc'    => __( 'Check this box to use the download earnings and sales for calculations instead of commissions. This setting ignores the rate expiration setting.', 'edd-tiered-commission-rates' ),
            'type'    => 'checkbox',
        ),
	);

	return array_merge( $settings, $tiered_commission_rates_settings );
}
add_filter( 'eddc_settings', 'edd_tiered_commission_rates_settings' );


/**
 * Registers the new Commission Fees options in Extensions
 *
 * @access		public
 * @since       1.0.0
 * @param       array $args callback args
 * @return      void
 */
function edd_tiered_rates_callback( $args ) {
	$rates = edd_tiered_commission_rates_get_rates();
	$class = edd_sanitize_html_class( $args['field_class'] );

	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {

		// Add Rate row
		$('#edd_tiered_commission_rates_new_rate').on('click', function(e) {

			e.preventDefault();

			var row = $('#edd-tiered-commission-rates tbody tr:last');

			clone = row.clone(true);

			var count = $('#edd-tiered-commission-rates tbody tr').length;

			clone.find( 'td input, td select' ).val( '' );
			clone.find( 'input, select' ).each(function() {
				var name = $( this ).attr( 'name' );

				name = name.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']');

				$( this ).attr( 'name', name ).attr( 'id', name );
			});

			clone.insertAfter( row );

		});

		// Remove Rate Row
		$('.edd_tiered_commission_rates_remove_rate').on('click', function(e) {

			e.preventDefault();
			if($(this).parent().parent().parent().children('tbody tr').length !== 1) {
						$(this).parent().parent().remove();
			}
		});

	});
	</script>
	<style type="text/css">
	#edd-tiered-commission-rates th { padding: 15px 10px; }
	@media screen and (max-width: 782px) {
		#edd-tiered-commission-rates td {
		    padding-left: 8px;
		}
	}
	</style>
	<p><?php echo $args['desc']; ?></p>
	<input type="hidden" name="edd_tiered_commission_rates_nonce" value="<?php echo wp_create_nonce( basename( __FILE__ ) ); ?>" />
	<table id="edd-tiered-commission-rates" class="form-table wp-list-table widefat fixed posts <?php echo esc_attr( $class ); ?>">
		<thead>
			<tr>
				<th scope="col" class="edd_tax_country"><?php _e( 'Type', 'edd-tiered-commission-rates' ); ?><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Tiered commission type: </strong>Select either number of sales or total commission earnings as the tier condition. Number of sales counts the commission records for that specific vendor, whereas total earnings counts the total (sum) value of the commissions. Both paid and unpaid commission records are included by default, however unpaid commission records can be excluded below if required.' ); ?>"></span></th>
				<th scope="col" class="edd_tax_state"><?php _e( 'Threshold', 'edd-tiered-commission-rates' ); ?><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Tiered commission thresholds: </strong>Enter the number of sales or total commission earnings a vendor must reach to achieve the tiered rate. Currency symbols are not required.' ); ?>"></span></th>
				<th scope="col" class="edd_tax_rate"><?php _e( 'Rate', 'edd-tiered-commission-rates' ); ?><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Tiered commission rates: </strong>Enter the percentage rate for each tier from lowest to highest. Percent symbols are not required. Enter rates as percentages, such as 6.5 for 6.5%.' ); ?>"></span></th>
				<th scope="col" class="edd_tax_global"><?php _e( 'Disabled', 'edd-tiered-commission-rates' ); ?></th>
				<th scope="col"><?php _e( 'Remove', 'edd-tiered-commission-rates' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if( $rates ) : ?>
				<?php foreach( $rates as $key => $rate ) :
					$type = ! empty( $rate['type'] ) ? $rate['type'] : 'sales';
					$disabled = isset( $rate['disabled'] );

					if ( $disabled ) :
						$aria_label = __( 'This rate tier is disabled', 'edd-tiered-commission-rates' );
					else :
						$aria_label = __( 'This rate tier is enabled', 'edd-tiered-commission-rates' );
					endif;
					?>
					<tr>
						<td>
							<select name="edd_tiered_commission_rates[rates][<?php echo esc_attr( $key ); ?>][type]">
								<option value="sales"<?php selected( 'sales', esc_attr( $type ) ); ?>><?php _e( 'Number of Sales', 'edd-tiered-commission-rates' ); ?></option>
								<option value="earnings"<?php selected( 'earnings', esc_attr( $type ) ); ?>><?php _e( 'Total Earnings', 'edd-tiered-commission-rates' ); ?></option>
							</select>
						</td>
						<td>
							<input name="edd_tiered_commission_rates[rates][<?php echo esc_attr( $key ); ?>][threshold]" type="text" value="<?php echo esc_attr( $rate['threshold'] ); ?>"/>
						</td>
						<td>
							<input name="edd_tiered_commission_rates[rates][<?php echo esc_attr( $key ); ?>][rate]" type="text" value="<?php echo esc_attr( $rate['rate'] ); ?>"/>
						</td>
						<td>
							<input name="edd_tiered_commission_rates[rates][<?php echo esc_attr( $key ); ?>][disabled]" id="edd_tiered_commission_rates[disabled]" type="checkbox" value="on" <?php checked( esc_attr( $disabled ), true ); ?> aria-label="<?php echo esc_attr( $aria_label ); ?>"/>
						</td>
						<td><span class="edd_tiered_commission_rates_remove_rate button-secondary" name="edd_tiered_commission_rates_remove_rate"><?php _e( 'Remove Rate', 'edd-tiered-commission-rates' ); ?></span></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php if( empty( $rates ) ) : ?>
				<tr>
					<td>
						<select name="edd_tiered_commission_rates[rates][0][type]">
							<option value="sales"><?php _e( 'Number of Sales', 'edd-tiered-commission-rates' ); ?></option>
							<option value="earnings"><?php _e( 'Total Earnings', 'edd-tiered-commission-rates' ); ?></option>
						</select>
					</td>
					<td>
						<input name="edd_tiered_commission_rates[rates][0][threshold]" type="text" value=""/>
					</td>
					<td>
						<input name="edd_tiered_commission_rates[rates][0][rate]" type="text" value=""/>
					</td>
					<td>
						<input name="edd_tiered_commission_rates[rates][0][disabled]" type="checkbox" value=""/>
					</td>
					<td><span class="edd_tiered_commission_rates_remove_rate button-secondary" name="edd_tiered_commission_rates_remove_rate"><?php _e( 'Remove Rate', 'edd-tiered-commission-rates' ); ?></span></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
	<p>
		<span class="button-secondary" id="edd_tiered_commission_rates_new_rate" name="edd_tiered_commission_rates_new_rate"><?php _e( 'Add New Rate', 'edd-tiered-commission-rates' ); ?></span>
	</p>
<?php
}


/**
 * Tiered Commission Rates sanitization and save
 *
 * @access		public
 * @since 		1.0.0
 * @param 		array $input The value inputted in the field
 * @return 		string $input Sanitized value
 */
function edd_settings_sanitize_tiered_commission_rates( $input ) {

	// Verify nonce
	if ( ! isset( $_POST['edd_tiered_commission_rates_nonce'] ) || ! wp_verify_nonce( $_POST['edd_tiered_commission_rates_nonce'], basename( __FILE__ ) ) ) {
		return $input;
	}

	// Verify user can manage shop settings
	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return $input;
	}

	// Verify fields are set
	if( ! isset( $_POST['edd_tiered_commission_rates'] ) ) {
		return $input;
	}

	// Sanitize form fields
	if ( ! empty( $_POST['edd_tiered_commission_rates'] ) && is_array( $_POST['edd_tiered_commission_rates'] ) ) {
		$new_rates = array_values( $_POST['edd_tiered_commission_rates'] );
		array_walk_recursive( $new_rates, 'sanitize_text_field', wp_unslash( $_POST['edd_tiered_commission_rates'] ) );
	} else {
		$new_rates = false;
	}

	$new_rates = $new_rates[0];

	if( ! empty( $new_rates ) ) {

		if( ! is_array( $new_rates ) ) {
			$new_rates = array();
		}

		// Sort rates low to high.
		usort( $new_rates, function( $rate1, $rate2 ) {
			if ( $rate1['rate'] == $rate2['rate'] ) {
				return 0;
			}
			return ( $rate1['rate'] < $rate2['rate'] ) ? -1 : 1;
		} );

		foreach( $new_rates as $key => $rate ) {

			$rate_value = edd_tiered_commission_rates_sanitize_rate( $rate['rate'] );

			if ( empty( $rate['threshold'] ) || null === $rate_value ) {

				unset( $new_rates[ $key ] );

			} else {

				// Remove currency and percentage symbols
				$rate['threshold'] = str_replace( '%', '', $rate['threshold'] );
				$rate['threshold'] = str_replace( '$', '', $rate['threshold'] );

				switch ( $new_rates[ $key ]['type'] ) {
					case 'earnings':
						$rate['threshold'] = $rate['threshold'] < 0 || ! is_numeric( $rate['threshold'] ) ? '' : $rate['threshold'];
						$rate['threshold'] = round( $rate['threshold'], edd_currency_decimal_filter() );
						break;
					case 'sales':
					default:
						if ( $rate['threshold'] < 0 || ! is_numeric( $rate['threshold'] ) ) {
							$rate['threshold'] = '';
						}

						$rate['threshold'] = ( is_numeric( $rate['threshold'] ) && $rate['threshold'] < 1 ) ? round( $rate['threshold'], 0 ) : $rate['threshold'];
						if ( is_numeric( $rate['threshold'] ) ) {
							$rate['threshold'] = round( $rate['threshold'], 0 );
						}

						break;
				}

				$new_rates[ $key ]['threshold'] = $rate['threshold'];
				$new_rates[ $key ]['rate']      = $rate_value;

				// If unchecked, the rate is "enabled".
				if ( ! isset( $rate['disabled'] ) ) {
					$new_rates[ $key ]['disabled'] = null;
				}

			}

		}

	}

	update_option( 'edd_tiered_commission_rates', $new_rates );

	return $input;
}
add_filter( 'edd_settings_extensions_sanitize', 'edd_settings_sanitize_tiered_commission_rates' );
