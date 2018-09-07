<?php

namespace Timber;

/**
 * Interface CoreInterface
 */
interface CoreInterface {

	/**
	 * Magic call for methods
	 *
	 * @param $field
	 * @param $args
	 * @return mixed
	 */
	public function __call( $field, $args );

	/**
	 * Magic getter for properties
	 *
	 * @param $field
	 * @return mixed
	 */
	public function __get( $field );

	/**
	 * @return boolean
	 */
	public function __isset( $field );

	/**
	 * Setup function for the class
	 *
	 * @return mixed
	 */
	public function setup();

	/**
	 * Reset state after usage
	 *
	 * @return mixed
	 */
	public function teardown();

	/**
	 * Get the metadata
	 *
	 * @param $key
	 * @return mixed
	 */
	public function meta( $key = null );

}
