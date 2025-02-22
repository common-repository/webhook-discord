<?php
/**
 * Webhook for Discord Contact Form 7
 *
 * @author      Monster2408
 * @license     GPLv2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class of the compatibility with CF7.
 */
class Discord_Webhook_CF7 {
	/**
	 * Adds the required hooks.
	 */
	public function __construct() {
		add_action( 'wpcf7_before_send_mail', array( $this, 'send' ), 10, 3 );
	}

	/**
	 * Sends the form submission to Discord using the specified webhook URL and Bot token.
	 *
	 * @param int $contact_form The contact form.
	 */
	public function send( $contact_form, $abort, $submission ) {
		$embed = $this->_prepare_embed( $submission );

		$http = new Discord_Webhook_HTTP( 'cf7' );
		return $http->process( '', $embed );
	}

	/**
	 * Prepares the embed for the CF7 form.
	 *
	 * @access protected
	 * @param  array  $submission The form values.
	 * @return array
	 */
	protected function _prepare_embed( $submission ) {
		$data  = $submission->get_posted_data();
		$embed = array();

		if ( ! empty( $data ) ) {
			foreach ( $data as $key => $value ) {
				if ( '_' === substr( $key, 0, 1 ) || empty( $value ) ) {
					continue;
				}

				$embed['fields'][] = array(
					'name'  => $key,
					'value' => $value,
				);
			}
		}

		return $embed;
	}
}

return new Discord_Webhook_CF7();
