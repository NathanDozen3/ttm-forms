<?php

namespace ttm\forms;

/**
 *
 */
class Blocks {

	/**
	 * Register all blocks in the /blocks/ directory.
	 */
	public function register_blocks() {
		$dirs = array_filter( glob( TTM_FORMS_DIR . '/blocks/*' ), 'is_dir' );
		foreach( $dirs as $dir ) {
			register_block_type( $dir . '/build' );
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
