<?php
/**
 * GameDistribution - http://www.gamedistribution.com/games/
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @package MyArcadePlugin/Feeds
 */

/**
 * Save options
 */
function myarcade_save_settings_gamedistribution() {

  myarcade_check_settings_nonce();

  $settings = array();
	$settings['feed']               = esc_url( filter_input( INPUT_POST, 'gamedistribution_url' ) );
  $settings['category'] = filter_input( INPUT_POST, 'gamedistribution_category' );
  $settings['collection'] = filter_input( INPUT_POST, 'gamedistribution_collection' );
  $settings['type'] = filter_input( INPUT_POST, 'gamedistribution_type' );
  $settings['cron_fetch'] = filter_input( INPUT_POST, 'gamedistribution_cron_fetch', FILTER_VALIDATE_BOOLEAN );
  $settings['cron_fetch_limit'] = filter_input( INPUT_POST, 'gamedistribution_cron_fetch_limit', FILTER_VALIDATE_INT, array( "options" => array( "default" => 1) ) );
  $settings['cron_publish'] = filter_input( INPUT_POST, 'gamedistribution_cron_publish', FILTER_VALIDATE_BOOLEAN );
  $settings['cron_publish_limit'] = filter_input( INPUT_POST, 'gamedistribution_cron_publish_limit', FILTER_VALIDATE_INT, array( "options" => array( "default" => 1) ) );

	// Update settings.
  update_option( 'myarcade_gamedistribution', $settings );
}

/**
 * Display distributor settings on admin page.
 */
function myarcade_settings_gamedistribution() {
	$gamedistribution = MyArcade()->get_settings( 'gamedistribution' );

  /**
	 * Since 6.1.0 - Update distributor URL.
   */
	if ( ! strpos( $gamedistribution['feed'], 'api/v2.0/' ) ) {
    $default_settings = myarcade_default_settings_gamedistribution();
    $gamedistribution['feed'] = $default_settings['feed'];
  }
  ?>
  <h2 class="trigger"><?php _e( "GameDistribution", 'myarcadeplugin'); ?></h2>
  <div class="toggle_container">
    <div class="block">
      <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
        <tr>
          <td colspan="2">
            <i>
               <?php printf( __( "%s distributes Flash and HTML5 games. In order to earn money with GameDistribution's games you will need to join the partner program %shere%s", 'myarcadeplugin' ), '<a href="http://www.gamedistribution.com/" target="_blank">GameDistribution</a>', '<a href="http://www.gamedistribution.com/joinus" target="_blank">', '</a>' ); ?>
            </i>
            <br /><br />
          </td>
        </tr>
        <tr><td colspan="2"><h3><?php _e("Feed URL", 'myarcadeplugin'); ?></h3></td></tr>
        <tr>
          <td>
						<input type="text" size="40"  name="gamedistribution_url" value="<?php echo esc_url( $gamedistribution['feed'] ); ?>" />
          </td>
          <td><i><?php _e("Edit this field only if Feed URL has been changed!", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e("Collection", 'myarcadeplugin'); ?></h3></td></tr>

        <tr>
          <td>
            <select size="1" name="gamedistribution_collection" id="gamedistribution_collection">
              <option value="all" <?php myarcade_selected( $gamedistribution['collection'], 'all' ); ?>><?php _e( 'All games', 'myarcadeplugin' ); ?></option>
              <option value="exclusive" <?php myarcade_selected( $gamedistribution['collection'], 'exclusive' ); ?>><?php _e( 'Exclusive games', 'myarcadeplugin' ); ?></option>
              <option value="best" <?php myarcade_selected( $gamedistribution['collection'], 'best' ); ?>><?php _e( 'Best new games', 'myarcadeplugin' ); ?></option>
              <option value="featured" <?php myarcade_selected( $gamedistribution['collection'], 'featured' ); ?>><?php _e( 'Hot Games', 'myarcadeplugin' ); ?></option>
            </select>
          </td>
          <td><i><?php _e("Select game collections.", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e("Category", 'myarcadeplugin'); ?></h3></td></tr>

        <?php
        $gamedistribution_categories = array(
					'All'         => __( 'All games', 'myarcadeplugin' ),
					'2 Player'    => '2 Player',
					'3D'          => '3D',
					'Action'      => 'Action',
					'Adventure'   => 'Adventure',
					'Arcade'      => 'Arcade',
					'Baby'        => 'Baby',
					'Bejeweled'   => 'Bejeweled',
					'Boys'        => 'Boys',
					'Clicker'     => 'Clicker',
					'Cooking'     => 'Cooking',
					'Farming'     => 'Farming',
					'Girls'       => 'Girls',
					'Hypercasual' => 'Hypercasual',
					'Multiplayer' => 'Multiplayer',
					'Puzzle'      => 'Puzzle',
					'Racing'      => 'Racing',
					'Shooting'    => 'Shooting',
					'Soccer'      => 'Soccer',
					'Social'      => 'Social',
					'Sports'      => 'Sports',
					'Stickman'    => 'Stickman',
        );
        ?>
        <tr>
          <td>
            <select size="1" name="gamedistribution_category" id="gamedistribution_category">
              <?php foreach ( $gamedistribution_categories as $key => $value ) : ?>
							 <option value="<?php echo esc_attr( $key ); ?>" <?php myarcade_selected( $gamedistribution['category'], $key ); ?>><?php echo esc_html( $value ); ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td><i><?php _e("Select which games you would like to fetch.", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e("Automated Game Fetching", 'myarcadeplugin'); ?></h3></td></tr>
        <tr>
          <td>
            <input type="checkbox" name="gamedistribution_cron_fetch" value="true" <?php myarcade_checked( $gamedistribution['cron_fetch'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?></label>
          </td>
          <td><i><?php _e("Enable this if you want to fetch games automatically. Go to 'General Settings' to select a cron interval.", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h4><?php _e("Fetch Games", 'myarcadeplugin'); ?></h4></td></tr>

        <tr>
          <td>
						<input type="text" size="40"  name="gamedistribution_cron_fetch_limit" value="<?php echo esc_attr( $gamedistribution['cron_fetch_limit'] ); ?>" />
          </td>
          <td><i><?php _e("How many games should be fetched on every cron trigger?", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", 'myarcadeplugin'); ?></h3></td></tr>

        <tr>
          <td>
            <input type="checkbox" name="gamedistribution_cron_publish" value="true" <?php myarcade_checked($gamedistribution['cron_publish'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?></label>
          </td>
          <td><i><?php _e("Enable this if you want to publish games automatically. Go to 'General Settings' to select a cron interval.", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h4><?php _e("Publish Games", 'myarcadeplugin'); ?></h4></td></tr>

        <tr>
          <td>
						<input type="text" size="40"  name="gamedistribution_cron_publish_limit" value="<?php echo esc_attr( $gamedistribution['cron_publish_limit'] ); ?>" />
          </td>
          <td><i><?php _e("How many games should be published on every cron trigger?", 'myarcadeplugin'); ?></i></td>
        </tr>

      </table>
      <input class="button button-primary" id="submit" type="submit" name="submit" value="<?php _e("Save Settings", 'myarcadeplugin'); ?>" />
    </div>
  </div>
  <?php
}

/**
 * Load default distributor settings
 *
 * @return  array Default settings
 */
function myarcade_default_settings_gamedistribution() {
  return array(
		'feed'          => 'https://catalog.api.gamedistribution.com/api/v2.0/rss/All/',
    'limit'         => '40',
    'type'          => 'all',
    'collection'    => 'all',
    'category'      => 'All',
    'cron_fetch'    => false,
    'cron_fetch_limit' => '1',
    'cron_publish'  => false,
    'cron_publish_limit' => '1',
  );
}

/**
 * Generate an options array with submitted fetching parameters
 *
 * @return  array Fetching options
 */
function myarcade_get_fetch_options_gamedistribution() {

	// Get distributor settings.
	$settings = MyArcade()->get_settings( 'gamedistribution' );
  $defaults = myarcade_default_settings_gamedistribution();
  $settings = wp_parse_args( $settings, $defaults );

  $settings['method'] = 'latest';
  $settings['offset'] = 1;

  if ( 'start' == filter_input( INPUT_POST, 'fetch' ) ) {
    $settings['limit']   = filter_input( INPUT_POST, 'limitgamedistribution', FILTER_VALIDATE_INT, array( "options" => array( "default" => 40 ) ) );
    $settings['method']  = filter_input( INPUT_POST, 'fetchmethodgamedistribution', FILTER_UNSAFE_RAW, array( "options" => array( "default" => 'latest') ) );
    $settings['offset']  = filter_input( INPUT_POST, 'offsetgamedistribution', FILTER_UNSAFE_RAW, array( "options" => array( "default" => '1') ) );
  }

  return $settings;
}

/**
 * Display distributor fetch games options
 *
 * @return  void
 */
function myarcade_fetch_settings_gamedistribution() {

  $gamedistribution = myarcade_get_fetch_options_gamedistribution();
  ?>

  <div class="myarcade_border white hide mabp_680" id="gamedistribution">
    <div style="float:left;width:150px;">
      <input type="radio" name="fetchmethodgamedistribution" value="latest" <?php myarcade_checked($gamedistribution['method'], 'latest');?>>
    <label><?php _e("Latest Games", 'myarcadeplugin'); ?></label>
    <br />
    <input type="radio" name="fetchmethodgamedistribution" value="offset" <?php myarcade_checked($gamedistribution['method'], 'offset');?>>
    <label><?php _e("Use Offset", 'myarcadeplugin'); ?></label>
    </div>
    <div class="myarcade_border" style="float:left;padding-top: 5px;background-color: #F9F9F9">
			<?php printf( esc_html__( 'Fetch %s games %sfrom page %s', 'myarcadeplugin' ), '<input type="number" name="limitgamedistribution" value="' . esc_attr( $gamedistribution['limit'] ) . '" />', '<span id="offsgamedistribution" class="hide">', '<input id="radiooffsgamedistribution" type="number" name="offsetgamedistribution" value="' . esc_attr( $gamedistribution['offset'] ) . '" /> </span>' ); ?>
    </div>
    <div class="clear"></div>
  </div>
  <?php
}

/**
 * Retrieve available distributor's categories mapped to MyArcadePlugin categories
 *
 * @return  array Distributor categories
 */
function myarcade_get_categories_gamedistribution() {
  return array(
    "Action"      => true,
    "Adventure"   => true,
    "Arcade"      => true,
		"Board Game"  => "Clicker",
    "Casino"      => false,
		"Defense"     => false,
		"Customize"   => false,
		"Dress-Up"    => "Girls",
		"Driving"     => "Racing",
		"Education"   => "Boys,Baby",
    "Fighting"    => false,
    "Jigsaw"      => false,
    "Multiplayer" => "2 Player,Multiplayer",
		"Other"       => "3D,Cooking,Farming,Social,Hypercasual,Stickman",
		"Puzzles"     => "Puzzle,Bejeweled",
		"Rhythm"      => false,
		"Shooting"    => "Shooting",
		"Sports"      => "Soccer,Sports",
		"Strategy"    => "Clicker",
  );
}

/**
 * Fetch games.
 *
 * @param array $args Fetching parameters.
 */
function myarcade_feed_gamedistribution( $args = array() ) {

  $defaults = array(
    'echo'     => false,
    'settings' => array(),
  );

  $args = wp_parse_args( $args, $defaults );
  extract($args);

  $new_games = 0;
  $add_game = false;

  $gamedistribution = myarcade_get_fetch_options_gamedistribution();
  $gamedistribution_categories = myarcade_get_categories_gamedistribution();
	$feedcategories              = MyArcade()->get_settings( 'categories' );
	$general                     = MyArcade()->get_settings( 'general' );

	// Init settings var's.
  if ( ! empty($settings) ) {
    $settings = array_merge( $gamedistribution, $settings );
	} else {
    $settings = $gamedistribution;
  }

  if ( ! isset($settings['method']) ) {
    $settings['method'] = 'latest';
  }

	// Generate the feed URL.
	$feed = add_query_arg(
		array(
			'format'     => 'json',
			'collection' => $settings['collection'],
		),
		trim( $settings['feed'] )
	);

	// Check if there is a feed limit. If not, feed all games.
  if ( ! empty( $settings['limit'] ) ) {
		$feed = add_query_arg( array( 'amount' => $settings['limit'] ), $feed );
  }

	$feed = add_query_arg( array( 'page' => $settings['offset'] ), $feed );

	if ( 'all' !== $settings['category'] ) {
		$feed = add_query_arg( array( 'categories' => rawurlencode( $settings['category'] ) ), $feed );
  }

  if ( isset( $general['types'] ) && 'mobile' == $general['types'] ) {
		$feed = add_query_arg( array( 'type'  => 'html5' ), $feed );
	} else {
		$feed = add_query_arg( array( 'type'  => 'all' ), $feed );
  }

	// Include required fetch functions.
	require_once MYARCADE_CORE_DIR . '/fetch.php';

	// Fetch games.
  $json_games = myarcade_fetch_games( array( 'url' => trim( $feed ), 'service' => 'json', 'echo' => $echo ) );

  //====================================
  if ( !empty($json_games ) ) {
    foreach ( $json_games as $game_obj ) {

      $game = new stdClass();
			$game->uuid = crc32( $game_obj->Title ) . '_gamedistribution';

      // Generate a game tag for this game
			$game->game_tag = md5( $game_obj->Title . 'gamedistribution' );

      $add_game   = false;
      $categories_string = 'Other';

			// Map categories.
			if ( is_array( $game_obj->Category ) ) {
				foreach ( $game_obj->Category as $gamecat ) {
        $gamecat = htmlspecialchars_decode( $gamecat );

        foreach ( $feedcategories as $feedcat ) {
						if ( 'checked' === $feedcat['Status'] ) {
            if ( ! empty( $gamedistribution_categories[ $feedcat['Name'] ] ) ) {
								// Set category name to check.
								if ( true === $gamedistribution_categories[ $feedcat['Name'] ] ) {
                $cat_name = $feedcat['Name'];
								} else {
                $cat_name = $gamedistribution_categories[ $feedcat['Name'] ];
              }
            }

							if ( false !== strpos( $cat_name, $gamecat ) ) {
              $add_game = true;
              $categories_string = $feedcat['Name'];
              break 2;
            }
          }
        }
      } // END - Category-Check
			}

      if ( ! $add_game ) {
        continue;
      }

			if ( 'html5' === $game_obj->Type ) {
        $game->type = 'gamedistribution';
			} else {
        $game->type = "custom";
      }

			$game->name         = esc_sql( $game_obj->Title );
			$game->slug         = myarcade_make_slug( $game_obj->Title );
			$game->description  = esc_sql( $game_obj->Description );
			$game->instructions = esc_sql( $game_obj->Instructions );
      $game->categs         = $categories_string;
			$game->width        = intval( $game_obj->Width );
			$game->height       = intval( $game_obj->Height );
			$game->swf_url      = esc_sql( $game_obj->Url );

			// Get the thumbnail.
			if ( is_array( $game_obj->Asset ) ) {
				// Look for the thumbnail 512x512px
				$thumbnail_array = preg_grep( '/.*(-512x512).*/', $game_obj->Asset );

				if ( $thumbnail_array ) {
					$game->thumbnail_url = reset( $thumbnail_array );
				} else {
					$game->thumbnail_url = $game_obj->Asset[0];
      }

				// Look for the screenshot 1280x720.
				$screenshot_array = preg_grep( '/.*(-1280x720).*/', $game_obj->Asset );

				if ( $screenshot_array ) {
					$game->screen1_url = reset( $screenshot_array );
				} else {
					// Not found! Use the last asset.
					$game->screen1_url = end( $game_obj->Asset );
          }
        }

			// Get game tags.
			if ( ! empty( $game_obj->Tag ) ) {
				$game->tags = implode( ',', $game_obj->Tag );
				// Remove # from tags.
				$game->tags = str_replace( '#', '', $game->tags );
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
function myarcade_embedtype_gamedistribution() {
  return 'iframe';
}

/**
 * Return if games can be downloaded by this distirbutor
 *
 * @return  bool True if games can be downloaded
 */
function myarcade_can_download_gamedistribution() {
  return false;
}
