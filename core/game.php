<?php
/**
 * Game Functions
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 */

// No direct access
if( !defined( 'ABSPATH' ) ) {
  die();
}

if ( ! function_exists('is_game') ) {
  /**
   * Check if the displayed post is a game post
   *
   * @version 5.15.0
   * @access  public
   * @param   int $post_id Post ID
   * @return  boolean TRUE if the post is a game post
   */
  function is_game( $post_id = false ) {
    global $post;

    if ( ! $post_id ) {
      if ( isset( $post->ID ) ) {
        $post_id = $post->ID;
      }
      else {
        return false;
      }
    }

    if ( get_post_meta( $post_id, "mabp_swf_url", true ) ) {
      return true;
    }
    else {
      return false;
    }
  }
}

if ( ! function_exists( 'is_leaderboard_game' ) ) {
  /**
   * Check if the current game supports scores
   *
   * @version 5.15.0
   * @access  public
   * @param   int $post_id Post ID
   * @return  boolean TRUE if the game supports scores.
   */
  function is_leaderboard_game( $post_id = false ) {
    global $post;

    if ( ! $post_id ) {
      if ( isset( $post->ID ) ) {
        $post_id = $post->ID;
      }
      else {
        return false;
      }
    }

    if ( get_post_meta( $post_id, 'mabp_leaderboard', true ) == '1' ) {
      return true;
    }

    return false;
  }
}

/**
 * Embeds the flash code to the post content if activated
 *
 * @version 5.13.0
 * @access  public
 * @param   string $content Post Content
 * @return  string          Post Content
 */
function myarcade_embed_handler( $content ) {
  global $post;

  // Do this only on single posts ...
  if ( is_single() && !is_feed() ) {

    $general  = get_option('myarcade_general');
    $game_url = get_post_meta($post->ID, "mabp_swf_url", true);

    // Check if this option is enabled and if this is a game
    if ( ($general['embed'] != 'manually') && !empty($game_url) ) {

      // Get the embed code of the game
      $embed_code = get_game($post->ID);

      // Add the embed code to the content
      if ( $general['embed'] == 'top' ) {
        $embed_code = '<div style="margin: 10px 0;text-align:center;">'.$embed_code.'</div>';
        $content = $embed_code.myarcade_get_leaderboard_code().$content;
      }
      else {
        $embed_code = '<div style="clear:both;margin: 10px 0;text-align:center;">'.$embed_code.'</div>';
        $content = $content.myarcade_get_leaderboard_code().$embed_code;
      }
    }
  }

  return $content;
}
add_filter('the_content', 'myarcade_embed_handler', 99);

/**
 * Check if global Mochi scores are enabled
 * This function is deprecated!
 *
 * Don't delete this function. Maybe some old themes are still using it!
 *
 * @version 5.13.0
 * @access  public
 * @return  false
 */
function enabled_global_scores() {
  // Just return false because this function isn't required anymore
  return false;
}

/**
 * Check the game width. If the game is larger than defined max. width return true, otherwise false.
 *
 * @version 5.13.0
 * @access  public
 * @param   int $postid Post ID
 * @return  boolean
 */
function myarcade_check_width( $postid ) {
  $result = false;

  $general = get_option('myarcade_general');

  $maxwidth   = intval($general['max_width']);
  $gamewidth  = intval(get_post_meta($postid, "mabp_width", true));

  if ($gamewidth > $maxwidth) {
    $result = true;
  }

  return $result;
}


/**
 * Replace http with https if ssl is enabled
 *
 * @version 5.27.1
 * @since   5.27.1
 * @param   string $game_url Game URL / Embed Code
 * @return  string Game URL
 */
function myarcade_replace_protocol( $game_url ) {

  $general  = get_option('myarcade_general');

  if ( $general['handle_ssl'] && is_ssl() ) {
    $game_url = str_replace( "http://", "https://", $game_url );
  }

  return $game_url;
}
add_filter( 'myarcade_swf_url', 'myarcade_replace_protocol' );