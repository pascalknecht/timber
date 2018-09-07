<?php

namespace Timber;

/**
 * Class Core
 */
abstract class Core implements CoreInterface {

	private $id;
	private $ID;
	private $object_type;

	/**
	 *
	 * @return boolean
	 */
	public function __isset( $field ) {
		if ( isset($this->$field) ) {
			return $this->$field;
		}
		return false;
	}

	/**
	 * This is helpful for twig to return properties and methods see: https://github.com/fabpot/Twig/issues/2
	 * @return mixed
	 */
	public function __call( $field, $args ) {
		return $this->__get($field);
	}

	/**
	 * This is helpful for twig to return properties and methods see: https://github.com/fabpot/Twig/issues/2
	 *
	 * @return mixed
	 */
	public function __get( $field ) {
		if ( property_exists($this, $field) ) {
			return $this->$field;
		}
		if ( method_exists($this, 'meta') && $meta_value = $this->meta($field) ) {
			return $this->$field = $meta_value;
		}
		if ( method_exists($this, $field) ) {
			return $this->$field = $this->$field();
		}
		return $this->$field = false;
	}

	/**
	 * Sets up post.
	 *
	 * @api
	 * @since 2.0.0
	 *
	 *
	 * @return \Timber\Post $this
	 */
	public function setup() {
		global $post;
		global $wp_query;

		// Overwrite post global.
		$post = $this;

		/**
		 * Mimick WordPress behavior to improve compatibility
		 * with third party plugins.
		 */
		$wp_query->in_the_loop = true;

		// The setup_postdata() function will call the 'the_post' action.
		$wp_query->setup_postdata( $post->ID );

		return $this;
	}

	/**
	 * Resets the variables after post has been used
	 * @api
	 * @since 2.0.0
	 *
	 * @return \Timber\Post $this
	 */
	public function teardown() {
		return $this;
	}

	/**
	 * Takes an array or object and adds the properties to the parent object.
	 *
	 * @example
	 * ```php
	 * $data = array( 'airplane' => '757-200', 'flight' => '5316' );
	 * $post = new Timber\Post();
	 * $post->import(data);
	 *
	 * echo $post->airplane; // 757-200
	 * ```
	 * @param array|object $info an object or array you want to grab data from to attach to the Timber object
	 */
	public function import( $info, $force = false, $only_declared_properties = false ) {
		if ( is_object($info) ) {
			$info = get_object_vars($info);
		}
		if ( is_array($info) ) {
			foreach ( $info as $key => $value ) {
				if ( $key === '' || ord($key[0]) === 0 ) {
					continue;
				}
				if ( !empty($key) && $force ) {
					$this->$key = $value;
				} else if ( !empty($key) && !method_exists($this, $key) ) {
					if ( $only_declared_properties ) {
						if ( property_exists($this, $key) ) {
							$this->$key = $value;
						}
					} else {
						$this->$key = $value;
					}

				}
			}
		}
	}


	/**
	 * @deprecated since 2.0.0
	 * @param string  $key
	 * @param mixed   $value
	 */
	public function update( $key, $value ) {
		update_metadata($this->object_type, $this->ID, $key, $value);
	}

	/**
	 * Can you edit this post/term/user? Well good for you. You're no better than me.
	 * @example
	 * ```twig
	 * {% if post.can_edit %}
	 * <a href="{{ post.edit_link }}">Edit</a>
	 * {% endif %}
	 * ```
	 * ```html
	 * <a href="http://example.org/wp-admin/edit.php?p=242">Edit</a>
	 * ```
	 * @return bool
	 */
	public function can_edit() {
		if ( !function_exists('current_user_can') ) {
			return false;
		}
		if ( current_user_can('edit_post', $this->ID) ) {
			return true;
		}
		return false;
	}

	/**
	 *
	 *
	 * @return array
	 */
	public function get_method_values() {
		$ret = array();
		$ret['can_edit'] = $this->can_edit();
		return $ret;
	}

	/**
	 * Gets a post meta value.
	 *
	 * Returns a meta value for a post that’s saved in the post meta database table.
	 *
	 * @api
	 *
	 * @param string $field_name The field name for which you want to get the value.
	 * @return mixed The meta field value.
	 */
	public function meta( $field_name = null ) {
		/**
		 * Filters the value for a post meta field before it is fetched from the database.
		 *
		 * @todo  Add description, example
		 *
		 * @see   \Timber\Post::meta()
		 * @since 2.0.0
		 *
		 * @param string       $value      The field value. Default null.
		 * @param int          $post_id    The post ID.
		 * @param string       $field_name The name of the meta field to get the value for.
		 * @param \Timber\Post $post       The post object.
		 */
		$value = apply_filters( 'timber/post/pre_meta', null, $this->ID, $field_name, $this );

		if ( null === $field_name ) {
			Helper::warn('You have not set what meta field you want to retrive this can cause strange behavior and is not recommended');
		}

		if ( "meta" === $field_name ) {
			Helper::warn('You are trying to retrive a meta field named "meta" this can cause strange behavior and is not recommended');
		}

		/**
		 * Filters the value for a post meta field before it is fetched from the database.
		 *
		 * @deprecated 2.0.0, use `timber/post/pre_meta`
		 */
		$value = apply_filters_deprecated(
			'timber_post_get_meta_field_pre',
			array( $value, $this->ID, $field_name, $this ),
			'2.0.0',
			'timber/post/pre_meta'
		);

		if ( $value === null ) {
			$value = get_post_meta($this->ID, $field_name);
			if ( is_array($value) && count($value) == 1 ) {
				$value = $value[0];
			}
			if ( is_array($value) && count($value) == 0 ) {
				$value = null;
			}
		}

		/**
		 * Filters the value for a post meta field.
		 *
		 * This filter is used by the ACF Integration.
		 *
		 * @todo  Add description, example
		 *
		 * @see   \Timber\Post::meta()
		 * @since 2.0.0
		 *
		 * @param string       $value      The field value.
		 * @param int          $post_id    The post ID.
		 * @param string       $field_name The name of the meta field to get the value for.
		 * @param \Timber\Post $post       The post object.
		 */
		$value = apply_filters( 'timber/post/meta', $value, $this->ID, $field_name, $this );

		/**
		 * Filters the value for a post meta field.
		 *
		 * @deprecated 2.0.0, use `timber/post/meta`
		 */
		$value = apply_filters_deprecated(
			'timber_post_get_meta_field',
			array( $value, $this->ID, $field_name, $this ),
			'2.0.0',
			'timber/post/meta'
		);

		$value = $this->convert($value, __CLASS__);
		return $value;
	}

	/**
	 * @param string $field_name
	 * @return mixed
	 */
	public function get_field( $field_name ) {
		return $this->get_meta_field($field_name);
	}
}
