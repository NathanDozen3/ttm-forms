<?php

namespace ttm\forms;

/**
 *
 */
class Webhooks {

	public function get_to_ping( $to_ping ) {
		foreach( (array) $to_ping as $key => $link ) {
			if( str_contains( $link, 'hooks.zapier.com' ) ) {
				unset( $to_ping[ $key ] );
			}
		}
		return $to_ping;
	}

	public function pre_ping( &$post_links, &$pung, int $post_ID ) {
		foreach ( $post_links as $key => $link ) {
			if( str_contains( $link, 'hooks.zapier.com' ) ) {
				unset( $post_links[ $key ] );
			}
		}
	}

	public function enclosure_links( $links ){
		foreach ( $links as $key => $link ) {
			if( str_contains( $link, 'hooks.zapier.com' ) ) {
				unset( $links[ $key ] );
			}
		}
		return $links;
	}

	public function process_webhooks( $webhooks ) {
		foreach( (array) $_POST[ 'webhooks' ] as $webhook_name => $webhook_args ) {
			$args = [];
			$url = $webhook_args[ 'url' ];
			$webhook_args = json_decode( stripcslashes( $webhook_args[ 'args' ] ) );
			foreach( $webhook_args as $webhook_arg ) {
				$args[ $webhook_arg->name ] = $webhook_arg->value;
			}
			webhook_trigger( $url, $args );
		}
	}

	public function verify_github_signature() : bool {
		$headers = getallheaders();
		if( ! isset( $headers[ 'X-Hub-Signature-256' ] ) ) {
			return false;
		}

		$body = file_get_contents( "php://input" );
		$secret = get_ttm_forms_options( 'webhooks-api-key' );
		return hash_equals( 'sha256=' . hash_hmac( 'sha256', $body, $secret ), $headers[ 'X-Hub-Signature-256' ] );
	  }
}
