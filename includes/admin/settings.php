<?php
/**
 * Extension settings
 *
 * @package     EDD\TiredCommissionRates
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2017, Sell Comet
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Registers the subsection for EDD Settings
 *
 * @since       1.0.0
 * @param       array $sections The sections
 * @return      array Sections with tiered commission rates added
 */
function edd_tiered_commission_rates_settings_section_extensions( $sections ) {
	$sections['tiered_commission_rates'] = __( 'Tiered Commission Rates', 'edd-tiered-commission-rates' );
	return $sections;
}
add_filter( 'edd_settings_sections_extensions', 'edd_tiered_commission_rates_settings_section_extensions' );


/**
 * Registers the new Commission Fees options in Extensions
 *
 * @since       1.0.0
 * @param       $settings array the existing plugin settings
 * @return      array The new EDD settings array with commissions added
 */
function edd_tiered_commission_rates_settings_extensions( $settings ) {

	$tiered_commission_rates_settings = apply_filters( 'edd_tiered_commission_rates_fields_settings', array(
		array(
			'id'      => 'edd_tiered_commission_rates_header',
			'name'    => '<strong>' . __( 'Tiered Rates', 'edd-tiered-commission-rates' ) . '</strong>',
			'desc'    => '',
			'type'    => 'header',
			'size'    => 'regular',
		),
		array(
			'id'      => 'edd_tiered_commission_rates_disabled',
			'name'    => __( 'Disable Tiered Rates', 'edd-tiered-commission-rates' ),
			'desc'    => __( 'Check this box to disable tiered commission rates.', 'edd-tiered-commission-rates' ),
			'type'    => 'checkbox',
		),
		array(
			'id'      => 'edd_tiered_commission_rates',
			'name'    => __( 'Tiered Commission Rates', 'edd-tiered-commission-rates' ),
			'desc' 		=> __( 'Add tiered rates for specific sales and earnings thresholds. Add rates lowest to highest. Enter rates as percentages, such as 6.5 for 6.5%.', 'edd-tiered-commission-rates' ),
			'type'    => 'tiered_rates',
		),
		array(
			'id'      => 'edd_tiered_commission_rates_expiration',
			'name'    => __( 'Tiered Rate Expiration', 'edd-tiered-commission-rates' ),
			'desc'    => __( 'Check this box to reset tiered commission rates on the 1st of every month.', 'edd-tiered-commission-rates' ),
			'type'    => 'checkbox',
		),
		array(
			'id'      => 'edd_tiered_commission_rates_exclude_unpaid',
			'name'    => __( 'Exclude Unpaid Statuses', 'edd-tiered-commission-rates' ),
			'desc'    => __( 'By default, tiered rate conditions include paid and unpaid commission statuses. By checking this box, unpaid commission will be excluded.', 'edd-tiered-commission-rates' ),
			'type'    => 'checkbox',
		),
	) );

	$tiered_commission_rates_settings = apply_filters( 'edd_tiered_commission_rates_settings', $tiered_commission_rates_settings );

	if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
		$tiered_commission_rates_settings = array( 'tiered_commission_rates' => $tiered_commission_rates_settings );
	}

	return array_merge( $settings, $tiered_commission_rates_settings );
}
add_filter( 'edd_settings_extensions', 'edd_tiered_commission_rates_settings_extensions' );




function edd_tiered_rates_callback( $args ) {

	$rates = edd_tiered_commission_rates_get_rates();
	$count = count( $rates );

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
	<table id="edd-tiered-commission-rates" class="form-table wp-list-table widefat fixed posts <?php echo $class; ?>">
		<thead>
			<tr>
				<th scope="col" class="edd_tiered_commission_fees_type"><?php _e( 'Type', 'edd-tiered-commission-rates' ); ?><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Tiered commission type: </strong>Select either number of sales or total commission earnings as the tier condition. Number of sales counts the commission records for that specific vendor, whereas total earnings counts the total value of the commissions. Both paid and unpaid commission records are included by default, however unpaid commission records can be excluded below if required.' ); ?>"></span></th>
				<th scope="col" class="edd_tiered_commission_fees_threshold"><?php _e( 'Threshold', 'edd-tiered-commission-rates' ); ?><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Tiered commission thresholds: </strong>Enter the number of sales or total commission earnings a vendor must reach to get the tiered rate. Currency symbols are not required. Enter thresholds as whole amounts, such as 10 for $10.00.' ); ?>"></span></th>
				<th scope="col" class="edd_tiered_commission_fees_rate"><?php _e( 'Rate', 'edd-tiered-commission-rates' ); ?><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Tiered commission rates: </strong>Enter the percentage rate for each tier from lowest to highest. Percent symbols are not required. Enter rates as percentages, such as 6.5 for 6.5%.' ); ?>"></span></th>
				<th scope="col" class="edd_tiered_commission_fees_disabled"><?php _e( 'Disabled', 'edd-tiered-commission-rates' ); ?></th>
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
							<select name="edd_tiered_commission_rates[rates][<?php echo $key; ?>][type]">
								<option value="sales"<?php selected( 'sales', $type ); ?>><?php _e( 'Number of Sales', 'edd-tiered-commission-rates' ); ?></option>
								<option value="earnings"<?php selected( 'earnings', $type ); ?>><?php _e( 'Total Earnings', 'edd-tiered-commission-rates' ); ?></option>
							</select>
						</td>
						<td>
							<input name="edd_tiered_commission_rates[rates][<?php echo $key; ?>][threshold]" type="text" value="<?php echo esc_attr( $rate['threshold'] ); ?>"/>
						</td>
						<td>
							<input name="edd_tiered_commission_rates[rates][<?php echo $key; ?>][rate]" type="text" value="<?php echo esc_attr( $rate['rate'] ); ?>"/>
						</td>
						<td>
							<input name="edd_tiered_commission_rates[rates][<?php echo $key; ?>][disabled]" id="edd_tiered_commission_rates[disabled]" type="checkbox" value="on" <?php checked( $disabled, true ); ?> aria-label="<?php echo esc_attr( $aria_label ); ?>"/>
						</td>
						<td><span class="edd_tiered_commission_rates_remove_rate button-secondary" name="edd_tiered_commission_rates_remove_rate"><?php _e( 'Remove Rate', 'edd-tiered-commission-rates' ); ?></span></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php if( empty( $rates ) ) : ?>
				<tr>
					<td>
						<select name="edd_tiered_commission_rates[rates][<?php echo $count; ?>][type]">
							<option value="sales"><?php _e( 'Number of Sales', 'edd-tiered-commission-rates' ); ?></option>
							<option value="earnings"><?php _e( 'Total Earnings', 'edd-tiered-commission-rates' ); ?></option>
						</select>
					</td>
					<td>
						<input name="edd_tiered_commission_rates[rates][<?php echo $count; ?>][threshold]" type="text" value=""/>
					</td>
					<td>
						<input name="edd_tiered_commission_rates[rates][<?php echo $count; ?>][rate]" type="text" value=""/>
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
 * Taxes Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * This also saves the tax rates table
 *
 * @since 1.6
 * @param array $input The value inputted in the field
 * @return string $input Sanitized value
 */
function edd_settings_sanitize_tiered_commission_rates( $input ) {

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return $input;
	}

	if( ! isset( $_POST['edd_tiered_commission_rates'] ) ) {
		return $input;
	}

	$new_rates = ! empty( $_POST['edd_tiered_commission_rates'] ) ? array_values( $_POST['edd_tiered_commission_rates'] ) : array();

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

			// Allow for 0 values.
			$rate_value = edd_tiered_commission_rates_abs_number_round( $rate['rate'] );

			if ( empty( $rate['threshold'] ) || null === $rate_value ) {

				unset( $new_rates[ $key ] );

			} else {

				$new_rates[ $key ]['threshold'] = absint( $rate['threshold'] );
				$new_rates[ $key ]['rate']      = (float) $rate_value;

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
