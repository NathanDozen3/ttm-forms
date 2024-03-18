<?php

namespace ttm\forms;

/**
 *
 */
class Database {

    /**
     *
     */
    private function process_inner_blocks( array $blocks ) : array {
        $innerBlocks = [];

        foreach( $blocks as $block ) {

            if(
                ! str_starts_with( $block[ 'blockName' ], 'ttm/' ) ||
                str_starts_with( $block[ 'blockName' ], 'ttm/input-submit' )
            ) {
                continue;
            }

            if( ! empty( $block[ 'innerBlocks' ] ) ) {
                $innerBlocks = array_merge( $innerBlocks, $this->process_inner_blocks( $block[ 'innerBlocks' ] ) );
            }
            else {
                $label = $block[ 'attrs' ][ 'label' ] ?? '';
                if( str_starts_with( $block[ 'blockName' ], 'ttm/input-hidden' ) ) {
                    $label = $block[ 'attrs' ][ 'name' ] ?? '';
                }
                $name = strtolower( str_replace( [ ':', ' ' ], [ '', '' ], $label ) );
                $innerBlocks[] = $name;
            }
        }
        return $innerBlocks;
    }

    /**
     *
     */
    public function process_form() {
        if(
            empty( $_POST[ 'post_id' ] ) ||
            empty( $_POST[ 'ttm_form' ] )
        ) {
            return;
        }

        $posted = $_POST;
        unset( $posted[ 'ttm_form' ] );
        unset( $posted[ 'post_id' ] );

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

            $fields = $this->process_inner_blocks( $block[ 'innerBlocks' ] );
            sort( $fields );

            $fields = array_filter( $fields, function( $val ) {
                return ! empty( $val );
            } );

            $insert = true;
            foreach( $fields as $field ) {
                if( ! in_array( $field, $keys ) ) {
                    $insert = false;
                }
            }

            if( $insert ) {
                $block[ 'attrs' ] = array_filter( $block[ 'attrs' ], function( $key ) {
                    return in_array( $key, [ 'post_id', 'to', 'subject' ] );
                }, ARRAY_FILTER_USE_KEY );
                $attrs = $block[ 'attrs' ];
                break;
            }
        }

        $attrs[ 'to' ] = is_email( $attrs[ 'to' ] );
        $to = is_email( $attrs[ 'to' ] );
        $attrs[ 'subject' ] = sanitize_text_field( $attrs[ 'subject' ] );
        $subject = sanitize_text_field( $attrs[ 'subject' ] );

        $message = '';
        $headers = [];
        $fields = $attrs;

        foreach( $_POST as $key => $value ) {
            $key = sanitize_text_field( $key );
            $value = sanitize_text_field( $value );
            $fields[ $key ] = $value;

            if( $key === 'ttm_form' ) {
                continue;
            }
            $message .= " $key: $value";
        }
        $fields = json_encode( json_decode( json_encode( $fields ) ) );

        $date = date( 'Y-m-d' );
        $url = get_the_permalink( $attrs[ 'post_id' ] );

        $this->insert_record_into_table( $date, $url, $fields );

        wp_mail( $to, $subject, $message, $headers );
        header("Location: {$_SERVER[ 'REQUEST_URI' ]}");
        die;
    }

    /**
     *
     */
    private function insert_record_into_table( $date, $url, $fields ) {

        global $wpdb;

        $table_name = TTM_FORMS_TABLE_NAME;

        $wpdb->insert(
            $table_name,
            [
                'date' => $date,
                'url' => $url,
                'fields' => $fields,
            ]
        );
    }

    /**
     *
     */
    public function create_database_table() {
        global $wpdb;

        $table_name = TTM_FORMS_TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            date date DEFAULT '0000-00-00' NOT NULL,
            url varchar(55) DEFAULT '' NOT NULL,
            fields text NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }
}
