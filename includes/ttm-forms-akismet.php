<?php

namespace ttm\forms;

/**
 * @see https://akismet.com/developers/
 */
class Akismet {

	/**
	 *
	 */
	private string $api_key;

	/**
	 *
	 */
	public function __construct() {
		$this->api_key = get_ttm_forms_options( 'akismet-api-key' );
	}


	/**
	 *
	 *
	 * @return bool
	 */
	private function verify_key() : bool {
		$endpoint = 'https://rest.akismet.com/1.1/verify-key';

		$args = [
			'body' => [
				'api_key' => $this->api_key,
				'blog' => get_site_url(),
			],
		];
		$response = wp_safe_remote_post( $endpoint, $args );
		return 'valid' === wp_remote_retrieve_body( $response );
	}


	/**
	 *
	 *
	 * @param array $comment_args
	 *
	 * @return bool Whether the comment is spam (true) or ham (false).
	 */
	private function comment_check( array $comment_args ) {
		$endpoint = 'https://rest.akismet.com/1.1/comment-check';

		$args = [
			'body' => [
				'api_key' => $this->api_key,
				'blog' => get_site_url(),
				'user_ip' => $_SERVER[ 'REMOTE_ADDR' ],
				'comment_type' => 'contact-form',
				'comment_author' => $comment_args[ 'comment_author' ] ?? null,
				'comment_author_email' => $comment_args[ 'comment_author_email' ] ?? null,
				'comment_author_url' => $comment_args[ 'comment_author_url' ] ?? null,
				'comment_content' => $comment_args[ 'comment_content' ] ?? null,
			],
		];
		$response = wp_safe_remote_post( $endpoint, $args );
		return 'true' === wp_remote_retrieve_body( $response );
	}


	/**
	 *
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function pre_insert( array $fields ) {
		if( ! is_module_active( 'akismet' ) ) {
			return $fields;
		}

		$args = [
			'comment_author' => $fields[ 'name' ] ?? null,
			'comment_author_email' => $fields[ 'email' ] ?? null,
			'comment_author_url' => $fields[ 'url' ] ?? null,
			'comment_content' => $fields[ 'content' ] ?? null,
		];

		$fields[ 'akismet' ] = $this->comment_check( $args ) ? 'spam' : 'ham';
		return $fields;
	}
}
