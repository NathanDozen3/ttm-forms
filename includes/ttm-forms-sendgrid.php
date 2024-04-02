<?php

namespace ttm\forms;

/**
 *
 */
class Sendgrid {

	/**
	 *
	 *
	 * @param null|bool $return
	 * @param array $atts
	 *
	 * @return bool
	 */
	public function pre_wp_mail( null|bool $return, array $atts ) {
		if( ! is_module_active( 'sendgrid' ) ) {
			return null;
		}

		$apiKey = get_ttm_forms_options( 'sendgrid-api-key' );
		$fromEmail = get_ttm_forms_options( 'sendgrid-from-email' );
		$fromName = get_ttm_forms_options( 'sendgrid-from-name' );

		$email = new \SendGrid\Mail\Mail();
		$email->setFrom( $fromEmail, $fromName );
		$email->setSubject( $atts[ 'subject' ] );
		$email->addTo( $atts[ 'to' ] );
		$email->addContent( "text/html", $atts[ 'message' ] );
		$sendgrid = new \SendGrid( $apiKey );
		try {
		    $response = $sendgrid->send($email);
			return true;
		} catch (Exception $e) {
			return false;
		}
	}
}
