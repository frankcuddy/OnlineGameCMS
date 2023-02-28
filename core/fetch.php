<?php
/**
 * Fetch Games
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 */

// No direct Access
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Checks if json functions are available on the server
 *
 * @version 5.15.0
 * @param boolean $echo true show messages, false hide messages
 * @return boolean
 */
function myarcade_check_json($echo) {

  $result = true;

  if ( !function_exists('json_decode') ) {

    if ($echo) {
        echo '<font style="color:red;">'.__("JSON Support is disabeld in your PHP configuration. Please contact your administrator to activate JSON Support.", 'myarcadeplugin').'</font><br /><br />';
      }

    $result = false;
  }

  return $result;
}

/**
 * Fetch and encode games from the given URL
 *
 * @version  5.19.0
 * @param array $args Fetching parameters
 * @return mixed fetched games
 */
function myarcade_fetch_games( $args = array() ) {

  $defaults = array(
    'url'     => '',
    'service' => '',
    'echo'    => true
  );

  $r = wp_parse_args( $args, $defaults );
  extract($r);

  if ( ! $url ) {
    if ( $echo ) {
      ?>
      <p class="mabp_info mabp_680">
				<?php echo esc_html__( 'No Feed URL provided!', 'myarcadeplugin' ); ?>
      </p>
      <?php
    }

    return false;
  }


  $games = false;

  // Allow users to modify or replace the URL
  $url = apply_filters( 'myarcade_fetch_url', $url, $service );

  switch ($service) {
    /** JSON FEEDS **/
    case 'json':
    {
      // Check if json_decode exisits
			if ( !myarcade_check_json($echo) ) {
        // Json not found..
        return false;
      }

      if ($echo) {
        ?>
        <p class="mabp_info mabp_680">
					<?php

					echo esc_html( 'Your Feed URL', 'myarcadeplugin' ).": <a href='" . esc_url( $url ) . "'>" . esc_url( $url ) . "</a>"; ?>
        </p>

        <p class="mabp_info mabp_680">
          <?php
					echo esc_html( 'Downloading feed', 'myarcadeplugin' ) . ': ';
      }

      //====================================
      // DOWNLOAD FEED
      $feed = myarcade_get_file($url);

      if ( !empty($feed['error']) ) {
        if ($echo) {
         echo '<font style="color:red;">'.__("ERROR", 'myarcadeplugin').': '.$feed['error'].'</font></p>';
        }
        return false;
      }

      // Check if have downloaded a file that can be decoded...
      if ($feed['response']) {
        if ($echo) { echo '<font style="color:green;">'.__("OK", 'myarcadeplugin').'</font></p>'; }
      }
      else {
        if ($echo) {
          echo '<font style="color:red;">'.__("Can't download feed!", 'myarcadeplugin').'</font></p>';
          myarcade_footer();
        }

        return false;
      }

      //====================================
      // DECODE DOWNLOADED FEED
      if ($echo) {
        ?><p class="mabp_info mabp_680"><?php
				echo esc_html( 'Decode feed', 'myarcadeplugin' ) . ": ";
      }

      // Decode the downloaded json feed
      // Clean unvalid characters (included for example in Scirra feed)
      $feed['response'] = str_replace( "[\k]","", $feed['response'] );
      $games = json_decode(  $feed['response'] );

      // Check if the decode was successfull
      if ($games) {
        if ($echo) {
          echo ' <font style="color:green;">'.__("OK", 'myarcadeplugin').'</font></p>';
        }
      }
      else {
        if ($echo) {
          echo ' <font style="color:red;">'.__("Failed to decode the downloaded feed!", 'myarcadeplugin').'</font></p>';
          myarcade_footer();
        }

        return false;
      }
    } break;

    /** XML FEEDS **/
    case 'xml':
    {
      if ($echo) {
        ?>
        <p class="mabp_info mabp_680">
					<?php echo esc_html( 'Your Feed URL', 'myarcadeplugin' ) . ": <a href='" . esc_url( $url ) . "'>" . esc_url( $url ) . "</a>"; ?>
        </p>

        <p class="mabp_info mabp_680">
          <?php
					echo esc_html( 'Downloading feed', 'myarcadeplugin' ) . ': ';
      }

      //====================================
      // DOWNLOAD FEED
      $feed = myarcade_get_file($url);

      if ( !empty($feed['error']) ) {
        if ($echo) {
         echo '<font style="color:red;">'.__("ERROR", 'myarcadeplugin').': '.$feed['error'].'</font></p>';
        }
        return false;
      }

      // Check if have downloaded a file that can be decoded...
      if ($feed['response']) {
        if ($echo) {
          echo '<font style="color:green;">'.__("OK", 'myarcadeplugin').'</font></p>';
        }
      }
      else {
        if ($echo) {
          echo '<font style="color:red;">'.__("Can't download feed!", 'myarcadeplugin').'</font></p>';
          myarcade_footer();
        }

        return false;
      }

			// DECODE DOWNLOADED FEED.
      if ($echo) {
        ?><p class="mabp_info mabp_680"><?php
				echo esc_html( 'Decode feed', 'myarcadeplugin' ) . ": ";
      }

			// Decode the downloaded xml feed.
      $games = simplexml_load_string($feed['response']);

      // Check if the decode was successfull
      if ($games) {
        if ($echo) {
          echo ' <font style="color:green;">'.__("OK", 'myarcadeplugin').'</font></p>';
        }
      }
      else {
        if ($echo) {
          echo ' <font style="color:red;">'.__("Failed to decode the downloaded feed!", 'myarcadeplugin').'</font></p>';
          myarcade_footer();
        }

        return false;
      }
    } break;

    default:
    {
      // ERROR
    } break;

  } // end switch

  return $games;
}
?>