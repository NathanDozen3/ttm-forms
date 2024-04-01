<?php

namespace ttm\forms;

/**
 *
 */
class Modules {

	/**
	 *
	 */
	private array $registered = [];


	/**
	 *
	 */
	public function register( Module $module ) : void {
		$this->registered[ $module->get( 'slug' ) ] = $module;
	}


	/**
	 * Return the all registered modules.
	 *
	 * @return array
	 */
	public function get() : array {
		return $this->registered;
	}
}
