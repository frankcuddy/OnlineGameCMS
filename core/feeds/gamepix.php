<?php
/**
 * GamePix Feed - https://games.gamepix.com/
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @package MyArcadePlugin/Feeds
 */

// No direct Access.
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Display distributor settings on admin page
 */
function myarcade_settings_gamepix() {

	$gamepix = MyArcade()->get_settings( 'gamepix' );

  /**
	 * Since 5.34.1
   * Update distributor URL
   */
	if ( strpos( $gamepix['feed'], 'http://' ) !== false ) {
    $default_settings = myarcade_default_settings_gamepix();
    $gamepix['feed'] = $default_settings['feed'];
  }
  ?>
	<h2 class="trigger"><?php _e( 'GamePix', 'myarcadeplugin' ); ?></h2>
  <div class="toggle_container">
    <div class="block">
      <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
        <tr>
          <td colspan="2">
            <i>
							<?php printf( __( '%s distributes HTML5 games.', 'myarcadeplugin' ), '<a href="http://gamepix.com" target="_blank">GamePix</a>' ); ?>
            </i>
            <br /><br />
          </td>
        </tr>
				<tr><td colspan="2"><h3><?php _e( 'Feed URL', 'myarcadeplugin'); ?></h3></td></tr>
        <tr>
          <td>
						<input type="text" size="40"  name="gamepix_url" value="<?php echo esc_url( $gamepix['feed'] ); ?>" />
          </td>
					<td><i><?php _e( 'Edit this field only if Feed URL has been changed!', 'myarcadeplugin' ); ?></i></td>
				</tr>

				<tr><td colspan="2"><h3><?php _e( 'Site ID', 'myarcadeplugin' ); ?></h3></td></tr>
				<tr>
					<td>
						<input type="text" size="40"  name="gamepix_site_id" value="<?php echo esc_attr( $gamepix['site_id'] ); ?>" />
					</td>
					<td><i><?php _e( 'Enter your Site ID if available.', 'myarcadeplugin'); ?></i></td>
        </tr>

				<tr><td colspan="2"><h3><?php _e( 'Category', 'myarcadeplugin' ); ?></h3></td></tr>

        <tr>
          <td>
            <select size="1" name="gamepix_category" id="gamepix_category">
							<option value="all" <?php myarcade_selected( $gamepix['category'], 'all' ); ?> ><?php _e( 'All Games', 'myarcadeplugin' ); ?></option>
							<option value="2" <?php myarcade_selected( $gamepix['category'], '2' ); ?> ><?php _e( 'Arcade', 'myarcadeplugin' ); ?></option>
							<option value="3" <?php myarcade_selected( $gamepix['category'], '3' ); ?> ><?php _e( 'Adventure', 'myarcadeplugin' ); ?></option>
							<option value="5" <?php myarcade_selected( $gamepix['category'], '5' ); ?> ><?php _e( 'Casino', 'myarcadeplugin' ); ?></option>
							<option value="6" <?php myarcade_selected( $gamepix['category'], '6' ); ?> ><?php _e( 'Classics', 'myarcadeplugin' ); ?></option>
							<option value="7" <?php myarcade_selected( $gamepix['category'], '7' ); ?> ><?php _e( 'Puzzles', 'myarcadeplugin' ); ?></option>
							<option value="8" <?php myarcade_selected( $gamepix['category'], '8' ); ?> ><?php _e( 'Sports', 'myarcadeplugin' ); ?></option>
							<option value="9" <?php myarcade_selected( $gamepix['category'], '9' ); ?> ><?php _e( 'Strategy', 'myarcadeplugin' ); ?></option>
            </select>
          </td>
					<td><i><?php _e( 'Select which games you would like to fetch.', 'myarcadeplugin' ); ?></i></td>
        </tr>

				<tr><td colspan="2"><h3><?php _e( 'Thumbnail Size', 'myarcadeplugin' ); ?></h3></td></tr>

        <tr>
          <td>
            <select size="1" name="gamepix_thumbnail" id="gamepix_thumbnail">
							<option value="thumbnailUrl100" <?php myarcade_selected( $gamepix['thumbnail'], 'thumbnailUrl100' ); ?> ><?php _e( '100x100', 'myarcadeplugin' ); ?></option>
							<option value="thumbnailUrl" <?php myarcade_selected( $gamepix['thumbnail'], 'thumbnailUrl' ); ?> ><?php _e( '250x250', 'myarcadeplugin' ); ?></option>
            </select>
          </td>
					<td><i><?php _e( 'Select a thumbnail size.', 'myarcadeplugin'); ?></i></td>
        </tr>

				<tr><td colspan="2"><h3><?php _e( 'Automated Game Publishing', 'myarcadeplugin' ); ?></h3></td></tr>

        <tr>
          <td>
						<input type="checkbox" name="gamepix_cron_publish" value="true" <?php myarcade_checked( $gamepix['cron_publish'], true ); ?> /><label class="opt">&nbsp;<?php _e( 'Yes', 'myarcadeplugin' ); ?></label>
          </td>
          <td><i><?php _e("Enable this if you want to publish games automatically. Go to 'General Settings' to select a cron interval.", 'myarcadeplugin'); ?></i></td>
        </tr>

				<tr><td colspan="2"><h4><?php _e( 'Publish Games', 'myarcadeplugin'); ?></h4></td></tr>

        <tr>
          <td>
						<input type="text" size="40"  name="gamepix_cron_publish_limit" value="<?php echo esc_attr( $gamepix['cron_publish_limit'] ); ?>" />
          </td>
					<td><i><?php _e( 'How many games should be published on every cron trigger?', 'myarcadeplugin' ); ?></i></td>
        </tr>

      </table>
			<input class="button button-primary" id="submit" type="submit" name="submit" value="<?php _e( 'Save Settings', 'myarcadeplugin' ); ?>" />
    </div>
  </div>
  <?php
}

/**
 * Retrieve distributor's default settings
 *
 * @return  array Default settings
 */
function myarcade_default_settings_gamepix() {
  return array(
    'feed'          => 'https://games.gamepix.com/games',
    'site_id'       => '20015',
    'category'      => 'all',
    'thumbnail'     => 'thumbnailUrl100',
    'cron_publish'  => false,
    'cron_publish_limit' => '1',
  );
}

/**
 * Handle distributor settings update
 */
function myarcade_save_settings_gamepix() {

  myarcade_check_settings_nonce();

  $settings = array();
  $settings['feed'] = (isset($_POST['gamepix_url'])) ? esc_sql($_POST['gamepix_url']) : '';
	$settings['site_id']            = ( isset( $_POST['gamepix_site_id'] ) ) ? $_POST['gamepix_site_id'] : '20015';
  $settings['category'] = (isset($_POST['gamepix_category'])) ? $_POST['gamepix_category'] : 'all';
  $settings['thumbnail'] = filter_input( INPUT_POST, 'gamepix_thumbnail' );
  $settings['cron_publish'] = (isset($_POST['gamepix_cron_publish']) ) ? true : false;
  $settings['cron_publish_limit'] = (isset($_POST['gamepix_cron_publish_limit']) ) ? intval($_POST['gamepix_cron_publish_limit']) : 1;

	// Update settings.
  update_option('myarcade_gamepix', $settings);
}

/**
 * Retrieve available distributor's categories mapped to MyArcadePlugin categories
 *
 * @return  array Distributor categories
 */
function myarcade_get_categories_gamepix() {
  return array(
		'Action'      => false,
		'Adventure'   => true,
		'Arcade'      => true,
		'Board Game'  => 'Board',
		'Casino'      => false,
		'Defense'     => false,
		'Customize'   => false,
		'Dress-Up'    => false,
		'Driving'     => false,
		'Education'   => false,
		'Fighting'    => false,
		'Jigsaw'      => false,
		'Multiplayer' => false,
		'Other'       => 'Classics,Junior',
		'Puzzles'     => true,
		'Rhythm'      => false,
		'Shooting'    => false,
		'Sports'      => true,
		'Strategy'    => true,
  );
}

/**
 * Fetch FlashGameDistribution games
 *
 * @param array $args Fetching parameters.
 */
function myarcade_feed_gamepix( $args = array() ) {

  $defaults = array(
    'echo'     => false,
    'settings' => array(),
  );

  $args = wp_parse_args( $args, $defaults );
  extract($args);

  $new_games = 0;
  $add_game = false;

	$gamepix            = MyArcade()->get_settings( 'gamepix' );
	$gamepix_categories = myarcade_get_categories_gamepix();
  $feedcategories     = get_option('myarcade_categories');

	// Init settings vars.
  if ( !empty($settings) ) {
    $settings = array_merge($gamepix, $settings);
	} else {
    $settings = $gamepix;
  }

  if ( empty( $settings['site_id']) ) {
		// Use our default affiliate credentials.
    $settings['site_id'] = '20015';
  }

	// Generate Feed URL.
	if ( 'all' !== $settings['category'] ) {
		$settings['feed'] = add_query_arg( array( 'category' => $settings['category'] ), trim( $settings['feed'] ) );
	}

	$settings['feed'] = add_query_arg( array( 'sid' => $settings['site_id'] ), trim( $settings['feed'] ) );

	// Include required fetch functions.
	require_once MYARCADE_CORE_DIR . '/fetch.php';

	// Fetch games.
	$json_games = myarcade_fetch_games(
		array(
			'url'     => $settings['feed'],
			'service' => 'json',
			'echo'    => $echo,
		)
	);

  if ( !empty($json_games->data) ) {
    foreach ($json_games->data as $game_obj) {

      $game = new stdClass();
      $game->uuid     = $game_obj->id . '_gamepix';

			// Generate a game tag for this game.
      $game->game_tag = md5( $game_obj->id . 'gamepix' );

      $add_game   = false;

			// Transform some categories.
      $categories = explode( ',', $game_obj->category );
      $categories_string = 'Other';

			// Loop trough game categories.
      foreach( $categories as $gamecat ) {
				// Loop trough MyArcade categories.
        foreach ( $feedcategories as $feedcat ) {
					if ( 'checked' === $feedcat['Status'] ) {
						$cat_name = false;

            if ( ! empty( $gamepix_categories[ $feedcat['Name'] ] ) ) {
							// Set category name to check.
							if ( true === $gamepix_categories[ $feedcat['Name'] ] ) {
                $cat_name = $feedcat['Name'];
							} else {
                $cat_name = $gamepix_categories[ $feedcat['Name'] ];
              }
            }

            if ( strpos( $cat_name, $gamecat ) !== false ) {
              $add_game = true;
              $categories_string = $feedcat['Name'];
              break 2;
            }
          }
        }
      } // END - Category-Check

      if ( ! $add_game ) {
        continue;
      }

      $game->type          = 'gamepix';
      $game->name          = esc_sql($game_obj->title);
      $game->slug          = myarcade_make_slug($game_obj->title);
      $game->description   = esc_sql($game_obj->description);
      $game->categs        = $categories_string;
      $game->swf_url       = esc_sql( strtok( $game_obj->url, '?' ) );
      $game->width         = esc_sql($game_obj->width);
      $game->height        = esc_sql($game_obj->height);

      $thumb_size = $settings['thumbnail'];

      if ( ! empty( $game_obj->$thumb_size ) ) {
        $game->thumbnail_url = esc_sql( $game_obj->$thumb_size );
      }
      else {
        $game->thumbnail_url = esc_sql( $game_obj->thumbnailUrl100 );
      }

			// Add game to the database.
      if ( myarcade_add_fetched_game( $game, $args ) ) {
        $new_games++;
      }
    }
  }

	// Show, how many games have been fetched.
  myarcade_fetched_message( $new_games, $echo );
}

/**
 * Return game embed method
 *
 * @return  string Embed Method
 */
function myarcade_embedtype_gamepix() {
  return 'iframe';
}

/**
 * Return if games can be downloaded by this distirbutor
 *
 * @return  bool True if games can be downloaded
 */
function myarcade_can_download_gamepix() {
  return false;
}
