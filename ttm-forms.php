<?php
/**
 * Plugin Name: TTM Forms
 * Plugin URI: 
 * Description: Another form plugin.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 7.3
 * Author: Twelve Three Media
 * Author URI: https://www.digitalmarketingcompany.com/
 * Text Domain: ttm-forms
 */

namespace ttm\forms;

/**
 * 
 */
function register_form_post_type() {
   $labels = [
		'name'                     => __( 'Forms', 'ttm-forms' ),
		'singular_name'            => __( 'Form', 'ttm-forms' ),
		'add_new'                  => __( 'Add New', 'ttm-forms' ),
		'add_new_item'             => __( 'Add New Form', 'ttm-forms' ),
		'edit_item'                => __( 'Edit Form', 'ttm-forms' ),
		'new_item'                 => __( 'New Form', 'ttm-forms' ),
		'view_item'                => __( 'View Form', 'ttm-forms' ),
		'view_items'               => __( 'View Forms', 'ttm-forms' ),
		'search_items'             => __( 'Search Forms', 'ttm-forms' ),
		'not_found'                => __( 'No Forms found.', 'ttm-forms' ),
		'not_found_in_trash'       => __( 'No Forms found in Trash.', 'ttm-forms' ),
		'parent_item_colon'        => __( 'Parent Forms:', 'ttm-forms' ),
		'all_items'                => __( 'All Forms', 'ttm-forms' ),
		'archives'                 => __( 'Form Archives', 'ttm-forms' ),
		'attributes'               => __( 'Form Attributes', 'ttm-forms' ),
		'insert_into_item'         => __( 'Insert into Form', 'ttm-forms' ),
		'uploaded_to_this_item'    => __( 'Uploaded to this Form', 'ttm-forms' ),
		'featured_image'           => __( 'Featured Image', 'ttm-forms' ),
		'set_featured_image'       => __( 'Set featured image', 'ttm-forms' ),
		'remove_featured_image'    => __( 'Remove featured image', 'ttm-forms' ),
		'use_featured_image'       => __( 'Use as featured image', 'ttm-forms' ),
		'menu_name'                => __( 'Forms', 'ttm-forms' ),
		'filter_items_list'        => __( 'Filter Form list', 'ttm-forms' ),
		'filter_by_date'           => __( 'Filter by date', 'ttm-forms' ),
		'items_list_navigation'    => __( 'Forms list navigation', 'ttm-forms' ),
		'items_list'               => __( 'Forms list', 'ttm-forms' ),
		'item_published'           => __( 'Form published.', 'ttm-forms' ),
		'item_published_privately' => __( 'Form published privately.', 'ttm-forms' ),
		'item_reverted_to_draft'   => __( 'Form reverted to draft.', 'ttm-forms' ),
		'item_scheduled'           => __( 'Form scheduled.', 'ttm-forms' ),
		'item_updated'             => __( 'Form updated.', 'ttm-forms' ),
		'item_link'                => __( 'Form Link', 'ttm-forms' ),
		'item_link_description'    => __( 'A link to an form.', 'ttm-forms' ),
	];

	$args = [
		'labels'                          => $labels,
		'description'                     => __( 'Organize and manage company forms', 'ttm-forms' ),
		'public'                          => false,
		'hierarchical'                    => false,
		'exclude_from_search'             => true,
		'publicly_queryable'              => false,
		'show_ui'                         => true,
		'show_in_menu'                    => true,
		'show_in_nav_menus'               => true,
		'show_in_admin_bar'               => true,
		'show_in_rest'                    => true,
		'rest_base'                       => null,
		'rest_namespace'                  => null,
		'rest_controller_class'           => null,
		'autosave_rest_controller_class'  => null,
		'revisions_rest_controller_class' => null,
		'late_route_registration'         => null,
		'menu_position'                   => null,
		'menu_icon'                       => 'dashicons-megaphone',
		'capability_type'                 => 'post',
		'capabilities'                    => [],
		'map_meta_cap'                    => null,
		'supports'                        => [ 'title', 'editor', 'revisions' ],
		'register_meta_box_cb'            => null,
		'taxonomies'                      => array(),
		'has_archive'                     => false,
		'rewrite'                         => true,
		'query_var'                       => true,
		'can_export'                      => true,
		'delete_with_user'                => false,
		'template'                        => [],
		'template_lock'                   => false,
   ];
   register_post_type( 'form', $args );
}
add_action( 'init', __NAMESPACE__ . '\register_form_post_type' );


/**
 * 
 */
function form_allowed_block_types( bool|array $allowed_blocks, \WP_Block_Editor_Context $editor_context ) : bool|array {
	if( 'form' === $editor_context->post->post_type ) { 
		$allowed_blocks = [
			'core/image',
			'core/paragraph',
			'core/heading',
			'core/list'
		];
	}
	return $allowed_blocks;
}
add_filter( 'allowed_block_types_all', __NAMESPACE__ . '\form_allowed_block_types', 10, 2 );

/**
 * 
 */
function form_shortcode_callback( bool|array $atts ) : string {
	$atts = shortcode_atts( array(
		'id' => '0',
	), $atts, 'ttm-form' );

	$id = (int) $atts[ 'id' ];
	if( $id < 1 ) {
		return '';
	}
	ob_start();
	?>
	<style>
		form label,
		form input {
			display: block;
		}
	</style>

	<form action="<?php echo $_SERVER[ 'REQUEST_URI' ]; ?>" method="post">
		<input type="hidden" id="ttm_form" name="ttm_form" value="<?php echo $id; ?>">
		
		<label for="fname">First name:</label>
		<input type="text" id="fname" name="fname" value="">
		
		<label for="lname">Last name:</label>
		<input type="text" id="lname" name="lname" value="">
		
		<input type="submit" value="Submit">
	</form>
	<?php
	return ob_get_clean();
}

/**
 * 
 */
function register_shortcodes(){
	add_shortcode( 'ttm-form', __NAMESPACE__ . '\form_shortcode_callback' );
}
add_action( 'init', __NAMESPACE__ . '\register_shortcodes' );

/**
 * 
 */
function process_form() {
	if(
		empty( $_POST[ 'post_id' ] ) ||
		empty( $_POST[ 'ttm_form' ] )
	) {
		return;
	}
	
	$posted = $_POST;
	unset( $posted[ 'ttm_form' ] );
	unset( $posted[ 'post_id' ] );
	unset( $posted[ 'to' ] );
	unset( $posted[ 'subject' ] );

	$keys = array_keys( $posted );
	sort( $keys );

	$post_id = (int) $_POST[ 'post_id' ];
	$post = get_post( $post_id );
	$blocks = parse_blocks( $post->post_content );

	$attrs = [];
	foreach( $blocks as $block ) {
		if( ! str_starts_with( $block[ 'blockName' ], 'ttm/form' ) ) {
			continue;
		}

		$fields = [];
		$innerBlocks = $block[ 'innerBlocks' ];
		foreach( $innerBlocks as $innerBlock ) {
			if( str_starts_with( $innerBlock[ 'blockName' ], 'ttm/input-submit' ) ) {
				continue;
			}

			$label = $innerBlock[ 'attrs' ][ 'label' ] ?? '';
			if( str_starts_with( $innerBlock[ 'blockName' ], 'ttm/input-hidden' ) ) {
				$label = $innerBlock[ 'attrs' ][ 'name' ] ?? '';
			}
			$name = strtolower( str_replace( [ ':', ' ' ], [ '', '' ], $label ) );
			if( $name !== '' ) {
				$fields[] = $name;
			}
		}
		sort( $fields );

		if(
			$fields === $keys &&
			is_array( $block[ 'attrs' ] )
		) {
			$attrs = $block[ 'attrs' ];
			break;
		}
	}

	$to = is_email( $attrs[ 'to' ] );
	$subject = sanitize_text_field( $attrs[ 'subject' ] );

	$message = '';
	$headers = [];

	foreach( $_POST as $key => $value ) {
		if( $key === 'ttm_form' ) {
			continue;
		}
		$value = sanitize_text_field( $value );
		$message .= " $key: $value";
	}
	wp_mail( $to, $subject, $message, $headers );
	header("Location: {$_SERVER[ 'REQUEST_URI' ]}");
	die;
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\process_form' );

/**
 * 
 */
function register_blocks() {
	register_block_type( __DIR__ . '/blocks/ttm-form/build' );
	register_block_type( __DIR__ . '/blocks/ttm-input-date/build' );
	register_block_type( __DIR__ . '/blocks/ttm-input-email/build' );
	register_block_type( __DIR__ . '/blocks/ttm-input-hidden/build' );
	register_block_type( __DIR__ . '/blocks/ttm-input-password/build' );
	register_block_type( __DIR__ . '/blocks/ttm-input-submit/build' );
	register_block_type( __DIR__ . '/blocks/ttm-input-tel/build' );
	register_block_type( __DIR__ . '/blocks/ttm-input-text/build' );
	register_block_type( __DIR__ . '/blocks/ttm-textarea/build' );
}
add_action( 'init', __NAMESPACE__ . '\register_blocks' );
