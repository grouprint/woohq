<?php

function woohq_license_menu() {
	add_submenu_page ( "options-general.php", "Bilahpro WooHQ", "Bilahpro WooHQ", "manage_options", "woohq", "woohq_license_page" );
	//add_submenu_page( string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '', int $position = null )
}

add_action('admin_menu', 'woohq_license_menu');

function woohq_license_page() {
	$license = get_option( 'woohq_license_key' );
	$status  = get_option( 'woohq_license_status' );
	?>
	<div class="wrap">
		<h2><?php _e('WooHQ Plugin License Options'); ?></h2>
		<form method="post" action="options.php">

			<?php settings_fields('woohq_license'); ?>

			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e('License Key'); ?>
						</th>
						<td>
							<input id="woohq_license_key" name="woohq_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
							<label class="description" for="woohq_license_key"><?php _e('Enter your license key'); ?></label>
						</td>
					</tr>
					<?php if( false !== $license ) { ?>
						<tr valign="top">
							<th scope="row" valign="top">
								<?php _e('Activate License'); ?>
							</th>
							<td>
								<?php if( $status !== false && $status == 'valid' ) { ?>
									<span style="color:green;"><?php _e('active'); ?></span>
									<?php wp_nonce_field( 'woohq_nonce', 'woohq_nonce' ); ?>
									<input type="submit" class="button-secondary" name="woohq_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
								<?php } else {
									wp_nonce_field( 'woohq_nonce', 'woohq_nonce' ); ?>
									<input type="submit" class="button-secondary" name="woohq_license_activate" value="<?php _e('Activate License'); ?>"/>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<?php submit_button(); ?>

		</form>
	<?php
}

function woohq_register_option() {
	// creates our settings in the options table
	register_setting('woohq_license', 'woohq_license_key', 'woohq_sanitize_license' );
}
add_action('admin_init', 'woohq_register_option');

function woohq_sanitize_license( $new ) {
	$old = get_option( 'woohq_license_key' );
	if( $old && $old != $new ) {
		delete_option( 'woohq_license_status' ); // new license has been entered, so must reactivate
	}
	return $new;
}

function woohq_activate_license() {

	if( isset( $_POST['woohq_license_deactivate'] ) ) {
		delete_option( 'woohq_license_status' ); // new license has been entered, so must reactivate
	}

	// listen for our activate button to be clicked
	if( isset( $_POST['woohq_license_activate'] ) ) {

		// run a quick security check
	 	if( ! check_admin_referer( 'woohq_nonce', 'woohq_nonce' ) )
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'woohq_license_key' ) );

		$item_name = 'woohq';
		// data to send in our API request
		$api_params = array(
			'woohq_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( $item_name ), // the name of our product in EDD
			'url'        => home_url()
		);

		// Call the custom API.

		//$response = wp_remote_post( 'https://webhook.site/2d6000f9-f186-4464-9847-39c8bab06136', array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
		$response = wp_remote_post( 'https://manage.bilahpro.com/woohq_license' , array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			$message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.' );

		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( false === $license_data->success ) {

				switch( $license_data->error ) {

					case 'expired' :

						$message = sprintf(
							__( 'Your license key expired on %s.' ),
							date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
						);
						break;

					case 'revoked' :
						
						//$message = __( $license_data->data );
						$message = __( 'Your license key has been disabled.' . $license_data->data );
						break;

					case 'missing' :

						$message = __( 'Invalid license.' );
						break;

					case 'invalid' :
					case 'site_inactive' :

						$message = __( 'Your license is not active for this URL.' );
						break;

					case 'item_name_mismatch' :

						$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), $item_name );
						break;

					case 'no_activations_left':

						$message = __( 'Your license key has reached its activation limit.' );
						break;

					default :

						$message = __( 'An error occurred, please try again.' );
						break;
				}

			}

		}

		
		// Check if anything passed on a message constituting a failure
		if ( ! empty( $message ) ) {
			$base_url = admin_url( 'options-general.php?page=woohq' );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ,'res' => urlencode($response) ), $base_url );

			wp_redirect( $redirect );
			exit();
		}

		// $license_data->license will be either "valid" or "invalid"

		update_option( 'woohq_license_status', $license_data->license );
		wp_redirect( admin_url( 'options-general.php?page=woohq' ) );
		exit();
		
	}
}

add_action('admin_init', 'woohq_activate_license');

/**
 * This is a means of catching errors from the activation method above and displaying it to the customer
 */
function woohq_admin_notices() {
	if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

		switch( $_GET['sl_activation'] ) {

			case 'false':
				$message = urldecode( $_GET['message'] );
				?>
				<div class="error">
					<p><?php echo $message; ?></p>
				</div>
				<?php
				break;

			case 'true':
			default:
				// Developers can put a custom success message here for when activation is successful if they way.
				break;

		}
	}
}
add_action( 'admin_notices', 'woohq_admin_notices' );


function woohq_check_license() {
	$item_name = 'woohq';
	$license = trim( get_option( 'woohq_license_key' ) );
	$api_params = array(
		'woohq_action' => 'check_license',
		'license' => $license,
		'item_name' => urlencode( $item_name ),
		'url' => home_url()
	);

	$response = wp_remote_post( 'https://manage.bilahpro.com/woohq_license', array( 'body' => $api_params, 'timeout' => 15, 'sslverify' => false ) );
  	if ( is_wp_error( $response ) ) {
		return false;
  	}

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	if( $license_data->license == 'valid' ) {
		echo 'valid';
		//exit;
		// this license is still valid
	} else {
		echo 'invalid';
		//exit;
		// this license is no longer valid
	}
}

add_action('admin_init', 'woohq_check_license');

