<?php
/**
 * Class/File Autoloader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class MyArcade_Autoloader {

  /**
	 * Project prefix.
	 *
   * @var string
   */
  private $slug = 'myarcade_';

  /**
	 * Path to the includes directory.
	 *
   * @var string
   */
  private $include_path = '';

  /**
   * The Constructor
   */
  public function __construct() {

    if ( function_exists( "__autoload" ) ) {
      spl_autoload_register( "__autoload" );
    }

    spl_autoload_register( array( $this, 'autoload' ) );

    $this->include_path = untrailingslashit( MYARCADE_DIR ) . '/includes/';
  }

  /**
	 * Take a class name and turn it into a file name.
   *
   * @access  private
   * @param   string $class
   * @return  string
   */
  private function get_file_name_from_class( $class ) {
    return 'class-' . str_replace( '_', '-', $class ) . '.php';
  }

  /**
	 * Include a class file.
   *
   * @access  private
   * @param   string $path
   * @return  bool successful or not
   */
  private function load_file( $path ) {

    if ( $path && is_readable( $path ) ) {
			include_once $path;
      return true;
		} else {
      return false;
    }
  }

  /**
   * Auto-load classes on demand to reduce memory consumption.
   *
	 * @param   string $class Class name.
   */
  public function autoload( $class ) {

    $class = strtolower( $class );

    if ( strpos( $class, $this->slug ) === false ) {
      return;
    }

    $file  = $this->get_file_name_from_class( $class );
    $path = '';

    if ( strpos( $class, $this->slug . 'admin' ) === 0 ) {
      $path = $this->include_path . 'admin/';
    }

    if ( empty( $path ) || ( ! $this->load_file( $path . $file ) && strpos( $class, $this->slug ) === 0 ) ) {
      $this->load_file( $this->include_path . $file );
    }
  }
}

new MyArcade_Autoloader();
