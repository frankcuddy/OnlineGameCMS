<?php
/**
 * MyArcade stats adds functionality to track game plays, duration and track MyArcadePlugin usage.
 *
 * Determinate important functions to be able to improve MyArcadePlugin
 * and to be able to offer features and games customers really need.
 */

// No direct access
if( ! defined( 'ABSPATH' ) ) {
  die();
}

class MyArcade_Tracker {

  /**
   * Init function
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
   * @access  public
   * @return  void
   */
  public static function init() {
    add_action( 'myarcade_tracker_send_event', array( __CLASS__, 'send_tracking_data' ) );
    add_action( 'myarcade_tracker_send_event', array( __CLASS__, 'clean_stats_data' ) );
  }

  /**
   * Decide whether to send tracking data or not
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
   * @access  public
   * @return  void
   */
  public static function send_tracking_data() {

    // Don't trigger this on AJAX Requests
    if ( defined( 'DOING_AJAX') && DOING_AJAX ) {
      return;
    }

    $params = self::get_tracking_data();

    wp_safe_remote_post( MYARCADE_UPDATE_API . 'stats/', array(
        'method'      => 'POST',
        'timeout'     => 45,
        'blocking'    => false,
        'headers'     => array( 'user-agent' => 'MyArcadeTracker/' . md5( esc_url( home_url( '/' ) ) ) . ';' ),
        'body'        => wp_json_encode( $params ),
      )
    );
  }

  /**
   * Get the last time tracking data was sent
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
   * @access  private
   * @return  int Timestamp
   */
  private static function get_last_send_time() {
    return get_option( 'myarcade_tracker_last_send', false );
  }

  /**
   * Get all the tracking data
   *
   * @version 5.31.0
   * @since   5.30.0
   * @static
   * @access  protected
   * @return  array Array of tracking data
   */
  protected static function get_tracking_data() {

    $data = array();

    // General site info
    $data['url']      = home_url();

    // Send other data only once per week
    if ( self::get_last_send_time() <= strtotime( '-1 week' ) ) {

      // Server Info
      $data['server']   = self::get_server_info();

      // WordPress Info
      $data['wp']       = self::get_wordpress_info();

      // Theme Info
      $data['theme']    = self::get_theme_info();

      // Plugin Info
      $data['plugins']  = self::get_active_plugins();

      // Update time first before sending to ensure it is set
      update_option( 'myarcade_tracker_last_send', time() );
    }

    // Get game plays so we can show a top list of MyArcadePlugin sites
    $data['total_plays'] = self::get_total_plays();

    // All game plays
    $data['plays'] = self::get_plays();

    // Games with minium play duration of 30 seconds
    $data['plays_duration'] = self::get_plays(30);

    return $data;
  }

  /**
   * Get the current theme info, theme name and version
   * to improve MyArcadePlugin compatibility with most used themes.
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
   * @access  private
   * @return  array
   */
  private static function get_theme_info() {

    $theme_data = wp_get_theme();

    return array(
      'name'        => $theme_data->get('Name'),
      'url'         => $theme_data->get('ThemeURI'),
      'version'     => $theme_data->get('Version'),
      'parent_name' => $theme_data->parent() ? $theme_data->parent()->get('Name') : '',
      'parent_url'  => $theme_data->parent() ? $theme_data->parent()->get('ThemeURI') : '',
      'parent_version' => $theme_data->parent() ? $theme_data->parent()->get('Version') : '',
    );
  }

  /**
   * Get WordPress related data be able to optimize
   * MyArcadePlugin memory usage and to know which older WP versions
   * we still need to support.
   *
   * @version 5.31.0
   * @since   5.30.0
   * @static
   * @access  private
   * @return  array
   */
  private static function get_wordpress_info() {

    $wp_data = array(
      'name'      => get_bloginfo( 'name' ),
      'locale'    => get_locale(),
      'version'   => get_bloginfo( 'version' ),
      'multisite' => is_multisite() ? 'Yes' : 'No',
    );

    return $wp_data;
  }

  /**
   * Get Server related info to make sure that
   * our plugin will work properly on all user servers.
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
   * @access  private
   * @return  array
   */
  private static function get_server_info() {
    global $wpdb;

    $server_data = array();

    if ( isset( $_SERVER['SERVER_SOFTWARE'] ) && ! empty( $_SERVER['SERVER_SOFTWARE'] ) ) {
      $server_data['software'] = $_SERVER['SERVER_SOFTWARE'];
    }

    if ( function_exists( 'phpversion' ) ) {
      $server_data['php_version'] = phpversion();
    }

    $memory = self::let_to_num( WP_MEMORY_LIMIT );

    if ( function_exists( 'ini_get' ) ) {

      if ( function_exists( 'memory_get_usage' ) ) {
        $system_memory = self::let_to_num( @ini_get( 'memory_limit' ) );
        $memory        = max( $memory, $system_memory );
      }

      $server_data['php_post_max_size'] = size_format( self::let_to_num( ini_get( 'post_max_size' ) ) );
      $server_data['php_time_limt'] = ini_get( 'max_execution_time' );
      $server_data['php_max_input_vars'] = ini_get( 'max_input_vars' );
    }

    $server_data['memory_limit']        = size_format( $memory );
    $server_data['php_max_upload_size'] = size_format( wp_max_upload_size() );
    $server_data['mysql_version']       = $wpdb->db_version();

    return $server_data;
  }

  /**
   * Get all active plugins to make sure that MyArcadePlugin
   * is working correctly with most used plugins
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
   * @access  private
   * @return  array
   */
  private static function get_active_plugins() {
    // Ensure get_plugins function is loaded
    if( ! function_exists( 'get_plugins' ) ) {
      include ABSPATH . '/wp-admin/includes/plugin.php';
    }

    $plugins  = get_plugins();
    $active_plugins_keys = get_option( 'active_plugins', array() );
    $active_plugins = array();

    foreach ( $plugins as $k => $v ) {
      // Take care of formatting the data how we want it.
      $formatted = array();
      $formatted['name'] = strip_tags( $v['Name'] );
      if ( isset( $v['Version'] ) ) {
        $formatted['version'] = strip_tags( $v['Version'] );
      }
      if ( isset( $v['PluginURI'] ) ) {
        $formatted['plugin_uri'] = strip_tags( $v['PluginURI'] );
      }
      if ( in_array( $k, $active_plugins_keys ) ) {
        // Remove active plugins from list so we can show active and inactive separately
        $active_plugins[$k] = $formatted;
      }
    }

    return $active_plugins;
  }

  /**
   * Get the total play counter
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
   * @access  private
   * @return  array
   */
  private static function get_total_plays() {
    return MyArcade_Stats::get_plays( 'total' );
  }

  /**
   * Get plays by date
   *
   * @version 5.31.0
   * @since   5.30.0
   * @static
   * @access  private
   * @param   int $min_duration Time in seconds (minimal play duration)
   * @return  array Array of dates and play count
   */
  private static function get_plays( $min_duration = 0 ) {

    // We only want to collect plays history to be able to create a top list of myarcadeplugin sites
    $plays = array(
      'date'        => MyArcade_Stats::get_date( '-1' ),
      'impressions' => MyArcade_Stats::get_plays( 'yesterday', $min_duration )
    );

    return $plays;
  }

  /**
   * Transform the php.ini notation for numbers (like '2M') to an integer.
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
   * @access  public
   * @param   string $size php.ini notation for numbers
   * @return  integer
   */
  public static function let_to_num( $size ) {
    $l   = substr( $size, -1 );
    $ret = substr( $size, 0, -1 );

    switch ( strtoupper( $l ) ) {
      case 'P':
        $ret *= 1024;
      case 'T':
        $ret *= 1024;
      case 'G':
        $ret *= 1024;
      case 'M':
        $ret *= 1024;
      case 'K':
        $ret *= 1024;
    }

    return $ret;
  }

  /**
   * Clean statistics older than 31 days
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
	 *
   * @access  public
   * @return  void
   */
  public static function clean_stats_data() {
    global $wpdb;

		$wpdb->query( "DELETE FROM {$wpdb->prefix}myarcade_plays WHERE DATE_FORMAT( `date`, '%Y-%m-%d' ) < " . MyArcade_Stats::get_date( '-31' ) );
  }

} // END Class

MyArcade_Tracker::init();
