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
	private array $enabled = [];

	/**
	 *
	 */
	private array $modules = [];


	public function __construct() {
		$this->modules = [
			'ttm\forms\recaptcha' => ( new module( 'recaptcha' ) )
				->name( __( 'reCAPTCHA', 'ttm-forms' ) )
				->field( 'site-key', __( 'Site Key', 'ttm-forms' ), __NAMESPACE__ . '\render_input_text_field' )
				->field( 'secret-key', __( 'Secret Key', 'ttm-forms' ), __NAMESPACE__ . '\render_input_password_field' )
				->block( 'ttm-recaptcha' ),
		];
	}


	/**
	 *
	 */
	public function register_modules() {

		/**
		 *
		 */
		$modules = apply_filters( 'ttm\forms\modules', $this->modules );
		foreach( $modules as $module ) {
			$this->register( $module );
		}
	}


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


	/**
	 * Return the all enabled modules.
	 *
	 * @return array
	 */
	public function get_enabled() : array {
		return $this->enabled;
	}
}
