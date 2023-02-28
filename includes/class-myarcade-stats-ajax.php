<?php
/**
 * Ajax Callbacks for game duration tracking
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

class MyArcade_Stats_Ajax {

  protected static $_instance = null;

  /**
   * Main Instance
   *
   * Ensures only one instance is loaded or can be loaded.
   *
   * @static
   * @return Main instance
   */
  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  /**
   * The Constructor
   *
   * @version 5.30.0
   * @since   5.30.0
   * @access public
   * @return void
   */
  public function __construct() {
    add_action( 'wp_ajax_myarcade_stats_duration_do_ajax', array( $this, 'play_duration' ) );
    add_action( 'wp_ajax_nopriv_myarcade_stats_duration_do_ajax', array( $this, 'play_duration' ) );

    add_action( 'wp_ajax_myarcade_stats_get_widget_content', array( $this, 'get_widget_content' ) );
  }

  /**
   * Calculate the play duration and update the database table
   *
   * @version 5.30.0
   * @since   5.30.0
   * @access  public
   * @return  void
   */
  public function play_duration() {
    global $wpdb;

    // Get submitted data
    $action   = filter_input( INPUT_POST, 'action' );
    $token    = filter_input( INPUT_POST, 'token', FILTER_VALIDATE_INT );
    $duration = filter_input( INPUT_POST, 'duration', FILTER_VALIDATE_INT );

    if ( 'myarcade_stats_duration_do_ajax' !== $action ) {
      return;
    }

    check_ajax_referer( 'myarcade_stats_ajax_nonce', 'nonce' );

    $transient_data = get_transient( 'myarcade_stats_' . $token );

    if ( $transient_data ) {

      // Remove transient because we don't need it anymore
      delete_transient( 'myarcade_stats_' . $token );

      if ( $token !== $transient_data['token'] ) {
        // Cheating?!
        return;
      }

      // Do a plausibility check to make sure that the play time isn't greater than the current time - registered play time
      $play_record = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}myarcade_plays WHERE ID = %d LIMIT 1", $transient_data['token'] ) );

      if ( ! $play_record ) {
        return;
      }

      $record_time = strtotime( $play_record->date );
      $current_time = time() + ( get_option( 'gmt_offset' ) * 3600 );

      if ( ( $record_time + $duration ) > $current_time) {
        // Cheating??
        return;
      }

      // Update the database record
      $wpdb->update( "{$wpdb->prefix}myarcade_plays", array( 'duration' => $duration ), array( 'ID' => $play_record->ID ) );
    }

    die();
  }

  /**
   * Get the widget content
   *
   * @version 5.30.0
   * @since   5.30.0
   * @access  public
   * @return  void
   */
  public function get_widget_content() {

    // Get the requested widget
    $widget = filter_input( INPUT_POST, 'widget' );

    $widget_file = MYARCADE_DIR . '/includes/admin/stats-widgets/class-myarcade-admin-widget-' . $widget . '.php';


    if ( file_exists( $widget_file ) ) {
      include_once( $widget_file );
    }

    die();
  }
}

MyArcade_Stats_Ajax::instance();