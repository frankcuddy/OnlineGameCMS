<?php
/**
 * MyArcadePlugin Session
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @package MyArcadePlugin/Session
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * MyArcade Session handling class
 */
class MyArcade_Session {

	/**
	 * Init required actions and filters.
	 *
	 * @static
	 */
	public static function init() {
		add_action( 'wp_logout', array( __CLASS__, 'end' ) );
		add_action( 'init', array( __CLASS__, 'start' ), 0 );
	}

	/**
	 * Start a new session.
	 *
	 * @static
	 */
	public static function start() {

		$user_id = get_current_user_id();

		// Get the current session ID.
		$id = session_id();

		if ( empty( $id ) ) {

			// No ID available. Start a new session.
			session_start( array( 'read_and_close' => true ) );

			$_SESSION['uuid'] = $user_id;

			if ( ! isset( $_SESSION['plays'] ) ) {
				$_SESSION['plays'] = 0;
			}

			if ( ! isset( $_SESSION['last_play'] ) ) {
				$_SESSION['last_play'] = 0;
			}
		} else {
			$_SESSION['uuid'] = $user_id;
		}
	}

	/**
	 * Retrieve a session variable.
	 *
	 * @static
	 * @param  string $var Variable name that should be retrieved.
	 * @return mixed       Value.
	 */
	public static function get( $var ) {
		return ( isset( $_SESSION[ $var ] ) ) ? $_SESSION[ $var ] : false;
	}

	/**
	 * Set a session variable.
	 *
	 * @static
	 * @param string $var   Name.
	 * @param mixed  $value Value.
	 */
	public static function set( $var, $value ) {
		$_SESSION[ $var ] = $value;
	}

	/**
	 * Destroy a session.
	 *
	 * @static
	 */
	public static function end() {
		if ( session_id() ) {
			session_destroy();
		}
	}
}

MyArcade_Session::init();
