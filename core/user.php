<?php
/**
 * User functions
 *
 * @package MyArcadePlugin/User
 */

// No direct access.
if( !defined( 'ABSPATH' ) ) {
  die();
}

/**
 * New user profile created. Add the user to the gameplay table.
 *
 * @param int $user_ID User ID.
 */
function myarcade_register_user( $user_ID ) {
  global $wpdb;

  if ( isset($user_ID) && is_int($user_ID) ) {
		// Add the user to the gameplay table.
		$wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}myarcadeuser ( user_id, points, plays ) VALUES ( %d, '0', '1' )", $user_ID ) );
  }
}
add_action('user_reguster','myarcade_register_user');

/**
 * Checks if user is allowed to play the game and it counts game plays by users.
 */
function myarcade_play_check() {
  global $wpdb, $post;

	// Init the show game var.
  $show_game = true;

  $general = get_option('myarcade_general');

	// Get user details.
  $current_user = wp_get_current_user();

	// Set the play delay to 30 seconds if there is no setting value.
  if ( empty($general['play_delay']) ) {
    $general['play_delay'] = 30;
  }

	// Get current time.
  $current_time = time();
	$time_diff    = $current_time - (int) MyArcade_Session::get( 'last_play' );

  if ( $time_diff > $general['play_delay'] ) {

    // This is a valid play try!
		// Set time for the last play.
		MyArcade_Session::set( 'last_play', $current_time );

		// Is this a logged in user or a guest.
    if ( $current_user->ID ) {

			// Init the show game var but make it filterable.
			// Manipulate result before we do anyting else.
			// This can be used to add custom functions which could block game displaying.
      $show_game = apply_filters( 'myarcade_play_check', $show_game );

			// Check if the show_game vas has been manipulated.
      if ( !$show_game ) {
        return false;
      }

			// Add points to user for playing a game.
      do_action('myarcade_update_play_points');
      do_action('myarcade_game_play');

			// Update the play counter.
			$rowID = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}myarcadeuser WHERE user_id = %d", $current_user->ID ) );

      if ( empty ($rowID) ) {
				// Insert the user to the table.
				// @todo - Initial points eintragen.
        myarcade_register_user( $current_user->ID );
			} else {
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}myarcadeuser SET plays = plays+1 WHERE user_id = %d", $current_user->ID ) );
      }
		} else {
			// Increment the play counter.
			MyArcade_Session::set( 'plays', MyArcade_Session::get( 'plays' ) + 1 );
    }
  }

	// When a guest is playing.
  if ( ! $current_user->ID ) {
    if ( intval( $general['limit_plays'] ) > '0' ) {
			$plays = MyArcade_Session::get( 'plays' );

      if ( $plays  >= intval( $general['limit_plays'] ) ) {
				// Don't show the game.
        $show_game = false;

				// Display the message.
        if ( !empty($general['limit_message']) ) {
					echo esc_html( $general['limit_message'] );
        }
      }
    }
  }

  // Do an action if the game should be displayed.
  if ( $show_game && ! empty( $post->ID ) ) {
		// We can count this as a game play.
    $game_plays = intval( get_post_meta( $post->ID, 'myarcade_plays', true ) ) + 1;
    update_post_meta( $post->ID, 'myarcade_plays', $game_plays );

		// Update the global game play count.
    $site_plays = intval( get_option( 'myarcade_site_plays' ) ) +  1;
    update_option( 'myarcade_site_plays', $site_plays );

		// Fire an action on this.
    do_action( 'myarcade_display_game' );
  }

  return $show_game;
}


/**
 * Get Avatar URL.
 *
 * @return  string
 */
function myarcade_get_avatar_url() {

  $current_user = wp_get_current_user();

  if ( ! $current_user->ID ) {
    return false;
  }

  $avatar_image = get_avatar( $current_user->ID, '50' );

  preg_match('/src=[\',"](.*?)[\',"]/i', $avatar_image, $matches);

  if ( !empty( $matches[1] ) ) {
    return $matches[1];
  }

  return false;
}

/**
 * Remove scores and game plays of the user that is deleted.
 *
 * @param  int $user_ID User ID.
 */
function myarcade_delete_user($user_ID) {
  global $wpdb;

  if ( isset($user_ID) && is_int($user_ID) ) {
		// Delete user scores.
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}myarcadescores WHERE user_id = %d", $user_ID ) );
		// Delete user gameplays.
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}myarcadeuser WHERE user_id = %d", $user_ID ) );
		// Delete user from the highscorestable.
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}myarcadehighscores WHERE user_id = %d", $user_ID ) );
		// Delete user from the medal table.
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}myarcademedals WHERE user_id = %d", $user_ID ) );
  }
}
add_action('delete_user',  'myarcade_delete_user');

/**
 * Shows MyArcade menu on the admin bar (Only for WP 3.1 and above).
 */
function myarcade_bar_menu() {
  global $wp_admin_bar;

  if ( function_exists('is_admin_bar_showing') ) {

    if ( !is_super_admin() || !is_admin_bar_showing() ) {
      return;
    }

    $id = 'myarcade-bar';

		// Add the main siteadmin menu item.
		$wp_admin_bar->add_menu(
			array(
				'id'    => $id,
				'title' => 'MyArcade',
				'href'  => admin_url( 'admin.php?page=myarcade_admin.php' ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'fetch-games',
				'parent' => $id,
				'title'  => __( 'Fetch Games', 'myarcadeplugin' ),
				'href'   => admin_url( 'admin.php?page=myarcade-fetch' ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'import-games',
				'parent' => $id,
				'title'  => __( 'Import Games', 'myarcadeplugin' ),
				'href'   => admin_url( 'admin.php?page=myarcade-import-games' ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'publish-games',
				'parent' => $id,
				'title'  => __( 'Publish Games', 'myarcadeplugin' ),
				'href'   => admin_url( 'admin.php?page=myarcade-publish-games' ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'manage-games',
				'parent' => $id,
				'title'  => __( 'Manage Games', 'myarcadeplugin' ),
				'href'   => admin_url( 'admin.php?page=myarcade-manage-games' ),
			)
		);

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'myarcade-settings',
				'parent' => $id,
				'title'  => __( 'Settings', 'myarcadeplugin' ),
				'href'   => admin_url( 'admin.php?page=myarcade-edit-settings' ),
			)
		);
  }
}
add_action( 'admin_bar_menu', 'myarcade_bar_menu', 1000 );
