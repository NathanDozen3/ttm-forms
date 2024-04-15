<?php

namespace ttm\forms;

/**
 *
 */
class Postmark {

	private string $url = 'https://api.postmarkapp.com/email';

	/**
	 *
	 *
	 * @param null|bool $return
	 * @param array $atts
	 *
	 * @return null|bool
	 */
	public function pre_wp_mail( null|bool $return, array $atts ) {
		if( ! is_module_active( 'postmark' ) ) {
			return $return;
		}

		$apiKey = get_ttm_forms_options( 'postmark-api-key' );
		$fromEmail = get_ttm_forms_options( 'postmark-from-email' );

		$body = (object) [
			'From' => $fromEmail,
			'To' => $atts[ 'to' ],
			'Subject' => $atts[ 'subject' ],
			'HtmlBody' => $atts[ 'message' ],
		];
		$body = json_encode( $body );

		$args = [
			'body' => $body,
			'headers' => [
				'Accept' => 'application/json',
				'Content-Type' => 'application/json',
				'X-Postmark-Server-Token' => $apiKey,
			],
		];
		wp_safe_remote_post( $this->url, $args );
		return true;
	}
}
