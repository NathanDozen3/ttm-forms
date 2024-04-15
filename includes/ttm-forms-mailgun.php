<?php

namespace ttm\forms;
use Mailgun\Mailgun;

/**
 *
 */
class Mailgun_SMTP {

	/**
	 *
	 *
	 * @param null|bool $return
	 * @param array $atts
	 *
	 * @return null|bool
	 */
	public function pre_wp_mail( null|bool $return, array $atts ) {
		if( ! is_module_active( 'mailgun' ) ) {
			return $return;
		}

		$apiKey = get_ttm_forms_options( 'mailgun-api-key' );
		$domain = get_ttm_forms_options( 'mailgun-domain' );
		$fromEmail = get_ttm_forms_options( 'mailgun-from-email' );

		# Instantiate the client.
		$mg = Mailgun::create( $apiKey );

		# Make the call to the client.
		try {
			$mg->messages()->send( $domain, [
				'from'	  => $fromEmail,
				'to'	  => $atts[ 'to' ],
				'subject' => $atts[ 'subject' ],
				'text'    => "You're mail client does not support HTML.",
				'html'	  => $atts[ 'message' ],
			]);
			return true;
		}
		catch (Exception $e) {
			return false;
		}
	}
}
