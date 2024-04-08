<?php

namespace ttm\forms;

/**
 *
 */
class Recaptcha {

	private string $endpoint =  'https://www.google.com/recaptcha/api/siteverify';

	/**
	 *
	 */
	public function __construct(
        public string $secret,
        public string $response,
        public string $remoteip,
    ) {}

	/**
	 *
	 * @return
	 */
	public function get() {
		$args = [
			'body' => [
				'secret' => $this->secret,
				'response' => $this->response,
				'remoteip' => $this->remoteip,
			],
		];
		return wp_safe_remote_post( $this->endpoint, $args );
	}
}
