<?php

/**
 * Class Forminator_Addon_Container
 * Container that holds addons
 *
 * @since 1.1
 */
class Forminator_Addon_Container implements ArrayAccess, Countable, Iterator {

	/**
	 * @since 1.1
	 * @var Forminator_Addon_Abstract[]
	 */
	private $addons = array();

	/**
	 * @since 1.1
	 *
	 * @param mixed $offset
	 *
	 * @return bool
	 */
	public function offsetExists( $offset ) {
		return isset( $this->addons[ $offset ] );
	}

	/**
	 * @since 1.1
	 *
	 * @param mixed $offset
	 *
	 * @return Forminator_Addon_Abstract|mixed|null
	 */
	public function offsetGet( $offset ) {
		if ( isset( $this->addons[ $offset ] ) ) {
			return $this->addons[ $offset ];
		}

		return null;
	}

	/**
	 * @since 1.1
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet( $offset, $value ) {
		$this->addons[ $offset ] = $value;
	}

	/**
	 * @param mixed $offset
	 */
	public function offsetUnset( $offset ) {
		unset( $this->addons[ $offset ] );
	}

	/**
	 * Count elements of an object
	 *
	 * @link  http://php.net/manual/en/countable.count.php
	 * @return int The custom count as an integer.
	 * </p>
	 * <p>
	 * The return value is cast to an integer.
	 * @since 1.1
	 */
	public function count() {
		return count( $this->addons );
	}

	/**
	 * Get All registers slug of addons
	 *
	 * @since 1.1
	 * @return array
	 */
	public function get_slugs() {
		return array_keys( $this->addons );
	}

	public function to_grouped_array() {
		$addons = array();

		foreach ( $this->addons as $slug => $addon_members ) {
			// force to offsetGet
			// in case will added hook
			$addon = $this[ $slug ];
			// enable later when implemented
			//  if ( ! $addon ) {
			//	continue;
			// }
			$addons[ $addon->get_slug() ] = $addon->to_array();
		}

		return $addons;
	}

	/**
	 * Return add-ons list as array
	 *
	 * @since 1.1
	 *
	 * @return array
	 */
	public function to_array() {
		$addons = array();

		foreach ( $this->addons as $slug => $addon_members ) {
			// force to offsetGet: enable when needed
			// in case will added hook
			$addon = $this[ $slug ];
			// if ( ! $addon ) {
			// continue;
			// }

			$addons[ $addon->get_slug() ] = $addon->to_array();
		}

		return $addons;
	}

	/**
	 * Return the current element
	 *
	 * @link  http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 * @since 1.1
	 */
	public function current() {
		return current( $this->addons );
	}

	/**
	 * Move forward to next element
	 *
	 * @link  http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 * @since 1.1
	 */
	public function next() {
		next( $this->addons );
	}

	/**
	 * Return the key of the current element
	 *
	 * @link  http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 * @since 1.1
	 */
	public function key() {
		return key( $this->addons );
	}

	/**
	 * Checks if current position is valid
	 *
	 * @link  http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 * @since 1.1
	 */
	public function valid() {
		return key( $this->addons ) !== null;
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link  http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 * @since 1.1
	 */
	public function rewind() {
		reset( $this->addons );
	}
}
