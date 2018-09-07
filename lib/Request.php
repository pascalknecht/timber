<?php

namespace Timber;

use Timber\Core;
use Timber\CoreInterface;

/**
 * Class Request
 *
 * Timber\Request exposes $_GET and $_POST to the context
 */
class Request {
	public $post = array();
	public $get = array();

	/**
	 * Constructs a Timber\Request object
	 * @example
	 */
	public function __construct() {
		$this->init();
	}
	/**
	 * @internal
	 */
	protected function init() {
		$this->post = $_POST;
		$this->get = $_GET;
	}

	public function __call( $field, $args ) {}

	public function __get( $field ) {}

	/**
	 * @return boolean|null
	 */
	public function __isset( $field ) {}

	public function meta( $key ) {}
}
