<?php

namespace ttm\forms;

/**
 *
 */
class TTM_Forms_List_Table extends \WP_List_Table {

	/**
	 * Construct the TTM Forms List Table object.
	 */
	public function __construct() {
		parent::__construct( [
			'singular' => 'form',
			'plural' => 'forms',
		] );
	}


	/**
	 * Return the default column value.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) : string {
		$fields = json_decode( $item[ 'fields' ] );
		return $fields->$column_name ?? '';
	}

	/**
	 * Return the column date.
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_date( array $item ) : string {
		return $item[ 'date' ];
	}


	/**
	 * Return the column name.
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_name( array $item ) : string {
		$fields = json_decode( $item[ 'fields' ] );
		return $fields->name ?? '';
	}


	/**
	 * Return the column email.
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_email( array $item ) : string {
		$fields = json_decode( $item[ 'fields' ] );
		return $fields->email ?? '';
	}


	/**
	 * Return the column checkbox.
	 *
	 * @param
	 *
	 * @return string
	 */
	public function column_cb( $item ) : string {
		return sprintf(
			'<input type="checkbox" name="id[]" value="%s" />',
			$item[ 'id' ]
		);
	}


	/**
	 * Return array of columns.
	 *
	 * @return array
	 */
	public function get_columns() {

		$user_fields = get_user_option( 'toplevel_page_ttm_forms_fields' ) ?: [];
		if( is_string( $user_fields ) && json_validate( $user_fields ) ) {
			$user_fields = json_decode( $user_fields );
		}
		else {
			$user_fields = [];
		}

		$return = [
			'cb' => '<input type="checkbox" />',
			'date' => __( 'Date', 'ttm-forms' ),
		];

		foreach( $user_fields as $field ) {
			$field_title = strtolower( $field );
			$field_title = str_replace(
				[ '_', '-', 'id', 'url', 'ttm' ],
				[ ' ', ' ', 'ID', 'URL', 'TTM' ],
				$field_title
			);
			$field_title = ucwords( $field_title );
			$return[ $field ] = $field_title;
		}
		return $return;
	}


	/**
	 * Return array of sortable items.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return [
			'date' => [ 'date', false ],
		];
	}


	/**
	 * Prepare the items for displaying in the table.
	 *
	 * @return void
	 */
	public function prepare_items() : void {
		global $wpdb;
		$table_name = TTM_FORMS_TABLE_NAME;
		$per_page = get_user_option( TTM_FORMS_PER_PAGE_OPTIONS_NAME ) ?: 20;

		$columns = $this->get_columns();
		$hidden = [];
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = [ $columns, $hidden, $sortable ];

		$total_items = $wpdb->get_var( "SELECT COUNT(id) FROM $table_name" );

		$paged = isset( $_REQUEST[ 'paged' ] ) ? max( 0, intval( $_REQUEST[ 'paged' ] - 1 ) * $per_page ) : 0;
		$orderby = ( isset( $_REQUEST[ 'orderby' ] ) && in_array( $_REQUEST[ 'orderby' ], array_keys( $this->get_sortable_columns() ) ) ) ? $_REQUEST[ 'orderby' ] : 'date';
		$order = ( isset( $_REQUEST[ 'order' ] ) && in_array( $_REQUEST[ 'order' ], [ 'asc', 'desc' ] ) ) ? $_REQUEST[ 'order' ] : 'desc';

		$this->items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d",
				$per_page,
				$paged
			), ARRAY_A
		);

		$this->set_pagination_args( [
			'total_items' => $total_items,
			'per_page' => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		] );
	}
}
