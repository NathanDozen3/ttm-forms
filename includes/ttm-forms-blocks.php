<?php

namespace ttm\forms;

/**
 *
 */
class Blocks {

	/**
	 *
	 */
	private array $dirs = [
		'ttm-column',
		'ttm-columns',
		'ttm-form',
		'ttm-input-checkbox',
		'ttm-input-checkbox-item',
		'ttm-input-date',
		'ttm-input-email',
		'ttm-input-hidden',
		'ttm-input-password',
		'ttm-input-radio',
		'ttm-input-radio-item',
		'ttm-input-submit',
		'ttm-input-tel',
		'ttm-input-text',
		'ttm-textarea',
		'ttm-webhook',
	];

	/**
	 * Register TTM Forms blocks.
	 *
	 * @return void
	 */
	public function register_blocks() : void {

		/**
		 * Filters the blocks to register.
		 *
		 * @param string[] $dirs An array of block directories to register.
		 */
		$dirs = apply_filters( 'ttm\forms\register_blocks', $this->dirs );

		foreach( $dirs as $dir ) {
			register_block_type( TTM_FORMS_BLOCKS_DIR . $dir . '/build' );
		}
	}


	/**
	 *
	 */
	public function print_ttm_post_id() {
		$ttm_post_id = '0';
		$queried_object = get_queried_object();
		if( ! is_object( $queried_object ) ) {
			return;
		}
		$type = get_class( $queried_object );

		if( $type == 'WP_Post' ) {
			$ttm_post_id = get_the_ID();
		}
		else if( $type == 'WP_Term' ) {
			$ttm_post_id = $queried_object->taxonomy . '_' . $queried_object->term_id;
		}
		?>
		<script>
			var ttm_post_id = '<?php echo $ttm_post_id; ?>';
		</script>
		<?php
	}


	/**
	 *
	 */
	public function enqueue_block_assets() {
		if(
			has_block( 'ttm/form' ) ||
			( has_block( 'core/block' ) && $this->reusable_block_has_ttm_form() )
		)  {
			add_action( 'wp_head', [ $this, 'print_ttm_post_id' ] );
		}
	}


	/**
	 *
	 */
	function add_recaptcha_site_key( $block_content, $block, $instance ) {
		$options = get_option( 'ttm_forms' );
		$site_key = $options[ 'site-key' ];
		return str_replace( "data-sitekey=\"\"", "data-sitekey=\"$site_key\"", $block_content );
	}


	/**
	 *
	 */
	function add_hidden_field_to_ttm_form( $block_content, $block, $instance ) {
		if( ! isset( $block[ 'attrs' ][ 'ref' ] ) ) {
			return $block_content;
		}
		$ref = $block[ 'attrs' ][ 'ref' ];
		$block_content = str_replace(
			'<div class="wp-block-ttm-form"><form method="post">',
			'<div class="wp-block-ttm-form"><form method="post"><input type="hidden" name="ttm_form_ref" value="' . $ref . '"/>',
			$block_content
		);
		return $block_content;
	}


	/**
	 *
	 */
	function add_honeypot_to_ttm_form( $block_content, $block, $instance ) {
		ob_start();
		get_partial( 'honeypot' );
		$honeypot = ob_get_clean();
		$block_content = str_replace(
			'<form method="post">',
			'<form method="post">' . $honeypot,
			$block_content
		);
		return $block_content;
	}


	/**
	 * Append content to the TTM Form
	 */
	function form_footer( $block_content, $block, $instance ) {
		ob_start();

		/**
		 *
		 */
		do_action( 'ttm\forms\form_footer', $instance );
		$content = ob_get_clean();

		$block_content = str_replace( '</form>', $content . '</form>', $block_content );
		return $block_content;
	}

	/**
	 *
	 */
	function reusable_block_has_ttm_form() : bool {

		$p = get_post();
		$blocks = parse_blocks( $p->post_content );
		foreach( $blocks as $block ) {
			$id = $block[ 'attrs' ][ 'ref' ];
			if( has_block( 'ttm/form', $id ) ) {
				return true;
			}
		}
		return false;
	}
}
