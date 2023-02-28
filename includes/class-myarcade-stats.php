<?php
/**
 * Methods to calculate the statistics
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

class MyArcade_Stats {

  /**
   * Get the play count for a time period
   *
   * @version 5.31.1
   * @since   5.30.0
   * @static
   * @access  public
   * @param   string  $time_period  Number of days or 'total'
   * @param   integer $min_duration It allows to count games by minimal play duration in seconds
   * @return  integer               Play count
   */
  public static function get_plays( $time_period, $min_duration = 0 ) {
    global $wpdb;

    $result = 0;
    $query  = '';

    if ( $min_duration ) {
      $duration_query = "AND duration >= '".intval( $min_duration )."'";
    }
    else {
      $duration_query = "";
    }

    switch ( $time_period ) {

      case 'today': {
        $query = "SELECT COUNT(1) FROM {$wpdb->prefix}myarcade_plays WHERE DATE_FORMAT( `date`, '%Y-%m-%d' ) = '".self::get_date()."' {$duration_query}";
      } break;

      case 'yesterday': {
        $query = "SELECT COUNT(1) FROM {$wpdb->prefix}myarcade_plays WHERE DATE_FORMAT( `date`, '%Y-%m-%d' ) = '".self::get_date( '-1' )."' {$duration_query}";
      } break;

      case 'week': {
        $query = "SELECT COUNT(1) FROM {$wpdb->prefix}myarcade_plays WHERE DATE_FORMAT( `date`, '%Y-%m-%d' ) BETWEEN '".self::get_date( '-7' )."' AND '".self::get_date()."' {$duration_query}";
      } break;

      case 'month': {
        $query = "SELECT COUNT(1) FROM {$wpdb->prefix}myarcade_plays WHERE DATE_FORMAT( `date`, '%Y-%m-%d' ) BETWEEN '".self::get_date( '-30' )."' AND '".self::get_date()."' {$duration_query}";
      } break;

      case 'year': {
        $query = "SELECT COUNT(1) FROM {$wpdb->prefix}myarcade_plays WHERE DATE_FORMAT( `date`, '%Y-%m-%d' ) BETWEEN '".self::get_date( '-365' )."' AND '".self::get_date()."' {$duration_query}";
      } break;

      case 'total': {
        return intval( get_option( 'myarcade_site_plays' ) );
      } break;

      default: {
        $query = "SELECT COUNT(1) FROM {$wpdb->prefix}myarcade_plays WHERE DATE_FORMAT( `date`, '%Y-%m-%d' ) = '".self::get_date( $time_period )."' {$duration_query}";
      } break;
    }

    if ( $query ) {
      $result = $wpdb->get_var( $query );
    }

    return intval( $result );
  }

  /**
   * Get game playes grouped by hour by day
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
   * @access  public
   * @param   string $date_offset 0 = Today, -1 = Yesterday ...
   * @return  array      Array of hours and plays
   */
  public static function get_houry_plays( $date_offset ) {
    global $wpdb;

    $data = array();

    // Populate initial data
    for( $i = 0; $i <= 23; $i++ ) {
      $data[$i] = 0;
    }

    // Rund the query
    $query = "SELECT HOUR(`date`) as hour, COUNT(*) as plays FROM {$wpdb->prefix}myarcade_plays WHERE DATE_FORMAT( `date`, '%Y-%m-%d' ) = '".self::get_date( $date_offset )."' GROUP BY HOUR(`date`)";

    $results = $wpdb->get_results( $query );

    if ( $results ) {
      foreach ( $results as $result ) {
        $data[ $result->hour ] = intval( $result->plays );
      }
    }

    return $data;
  }

  /**
   * Retrieve top games ordered by game play count
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
   * @access  public
   * @param   integer $number_of_games Game count
   * @return  WP_Query
   */
  public static function get_top_games( $number_of_games = 10 ) {

    $args = array(
      'posts_per_page' => $number_of_games,
      'order'       => 'DESC',
      'orderby'     => 'meta_value_num',
      'meta_key'    => 'myarcade_plays',
      'meta_query'  => array(
        array(
          'key'     => 'mabp_swf_url',
          'compare' => 'EXISTS',
        )
      )
    );

    return new WP_Query( $args );
  }

  /**
   * Retrieve latest game game plays
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
   * @access  public
   * @param   integer $number_of_games Game count
   * @return  Object myarcade_plays row
   */
  public static function get_latest_plays( $number_of_games = 10 ) {
    global $wpdb;

    $number_of_games = intval( $number_of_games );

    $query = "SELECT * FROM {$wpdb->prefix}myarcade_plays ORDER BY ID DESC LIMIT {$number_of_games}";
    return $wpdb->get_results( $query );
  }

  /**
   * Get a list of unpopular games
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
   * @access  public
   * @param   integer $count Number of games
   * @return  WP_Query
   */
  public static function get_unpopular_games( $count = 10 ) {

    $args = array(
      'posts_per_page' => $count,
      'orderby'     => array( 'meta_value_num' => 'ASC', 'date' => 'ASC' ),
      'meta_key'    => 'myarcade_plays',
      'meta_query'  => array(
        array(
          'key'     => 'mabp_swf_url',
          'compare' => 'EXISTS',
        ),
      )
    );

    return new WP_Query( $args );

  }

  /**
   * Get a list of currently unplayed games
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
   * @access  public
   * @param   integer $count Number of games
   * @return  WP_Query
   */
  public static function get_unplayed_games( $count = 10 ) {

    $args = array(
      'posts_per_page' => $count,
      'orderby'     => array( 'date' => 'ASC' ),
      'meta_query'  => array(
        'relation' => 'AND',
        array(
          'key'     => 'mabp_swf_url',
          'compare' => 'EXISTS',
        ),
        array(
          'relation' => 'OR',
          array(
            'key' => 'myarcade_plays',
            'value' => '0',
            'compare' => '=',
          ),
          array(
            'key' => 'myarcade_plays',
            'compare' => 'NOT EXISTS',
          )
        )
      )
    );

    return new WP_Query( $args );
  }

  /**
   * Retrieve a users top list
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
   * @access  public
   * @param   integer $count How many users to retrieve
   * @return  array Array of users IDs and Plays
   */
  public static function get_top_users( $count = 10 ) {
    global $wpdb;

    $count = intval( $count );

    return $wpdb->get_results( $wpdb->prepare( "SELECT user_id, plays FROM {$wpdb->prefix}myarcadeuser ORDER BY plays DESC LIMIT %d", $count ) );
  }

  /**
   * Get a date based on the site offset
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
   * @access  public
   * @param   string $day_offset Offset in days (0, -1,-7,-30,-365)
   * @param   string $format Date format (Y-m-d)
   * @return  string         Date
   */
  public static function get_date( $day_offset = false, $format = 'Y-m-d' ) {

    // Get the site offset
    $offset = get_option( 'gmt_offset' ) * 60 * 60;

    if ( $day_offset ) {
      $date = date( $format, strtotime( "{$day_offset} day" ) + $offset );
    }
    else {
      $date = date( $format, time() + $offset );
    }

    return $date;
  }
}