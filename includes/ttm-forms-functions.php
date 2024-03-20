<?php

namespace ttm\forms;

/**
 * Include the partial file.
 *
 * @param string $partial
 * @param array $args
 *
 * @return void
 */
function get_partial( string $partial, array $args = [] ) : void {
	$file = TTM_FORMS_DIR . '/partials/' . $partial . '.php';
	if( ! file_exists( $file ) ) {
		wp_die( "File does not exist. <br>\n $file");
	}
	require $file;
}
