<?php

namespace ttm\forms;

/**
 *
 */
class Turnstile {

	private string $endpoint = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

	public function add_turnstile_site_key( string $html ) : string {
		$key = get_ttm_forms_options( 'turnstile-site-key' );
		return str_replace( 'data-sitekey=""', 'data-sitekey="'.$key.'"', $html );
	}

	public function get( $secret, $response ) {
		$args = [
			'body' => [
				'secret' => $secret,
				'response' => $response,
			],
		];
		return wp_safe_remote_post( $this->endpoint, $args );
	}

}
