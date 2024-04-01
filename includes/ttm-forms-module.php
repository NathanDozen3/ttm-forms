<?php

namespace ttm\forms;

/**
 *
 */
class Module {

	/**
	 *
	 */
	private string $name;

	/**
	 *
	 */
	private array $fields = [];

	/**
	 *
	 *
	 * @param string $slug
	 */
	public function __construct(
        public string $slug,
    ) {}


	/**
	 *
	 *
	 * @param string $name
	 *
	 * @return self
	 */
	public function name( string $name ) : self {
		$this->name = $name;
		return $this;
	}


	/**
	 *
	 *
	 * @param string $slug
	 * @param string $label
	 * @param string $callback
	 *
	 * @return self
	 */
	public function field( string $slug, string $label, $callback = null ) : self {
		$this->fields[ $slug ] = [
			'label' => $label,
			'callback' => $callback,
		];
		return $this;
	}


	/**
	 *
	 *
	 * @param string $name
	 *
	 * @return self
	 */
	public function block( string $name ) : self {
		$module = str_replace( 'ttm-', '', $name );
		if( is_module_active( $module ) ) {
			add_filter( 'ttm\forms\register_blocks', function( array $blocks ) use ($name) {
				$blocks[] = $name;
				return $blocks;
			});
		}
		return $this;
	}


	/**
	 *
	 *
	 * @param string $v
	 */
	public function get( string $v ) {
		return $this->$v ?? null;
	}
}
