<?php
/**
 * Softgames - https://publishers.softgames.com/
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @copyright MyArcadePlugin
 * @license https://myarcadeplugin.com
 */

/**
 * Save options function
 *
 * @return  void
 */
function myarcade_save_settings_softgames() {

  myarcade_check_settings_nonce();

  $settings = array();
  $settings['feed'] = esc_sql( filter_input( INPUT_POST, 'softgames_url' ) );
  $settings['category'] = filter_input( INPUT_POST, 'softgames_category' );
  $settings['publisher_id'] = filter_input( INPUT_POST, 'softgames_publisher_id' );
  $settings['thumbnail'] = filter_input( INPUT_POST, 'softgames_thumbnail' );
  $settings['cron_publish'] = filter_input( INPUT_POST, 'softgames_cron_publish', FILTER_VALIDATE_BOOLEAN );
  $settings['cron_publish_limit'] = filter_input( INPUT_POST, 'softgames_cron_publish_limit', FILTER_VALIDATE_INT, array( "options" => array( "default" => 1) ) );

  // Update settings
  update_option( 'myarcade_softgames', $settings );
}

/**
 * Display distributor settings on admin page
 *
 * @return  void
 */
function myarcade_settings_softgames() {
	$softgames = MyArcade()->get_settings( 'softgames' );

  /**
   * since 5.38.0
   * Update distributor URL
   */
  if ( strpos( $softgames['feed'], 'kirk.softgames.de' ) !== FALSE ) {
    $default_settings = myarcade_default_settings_softgames();
    $softgames['feed'] = $default_settings['feed'];
  }

  ?>
  <h2 class="trigger"><?php _e("Softgames", 'myarcadeplugin'); ?></h2>
  <div class="toggle_container">
    <div class="block">
      <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
        <tr>
          <td colspan="2">
            <i>
              <?php printf( __( "%s distributes HTML5 games.", 'myarcadeplugin' ), '<a href="https://softgames.de" target="_blank">Softgames</a>' ); ?>
            </i>
            <br /><br />
          </td>
        </tr>
        <tr><td colspan="2"><h3><?php _e("Feed URL", 'myarcadeplugin'); ?></h3></td></tr>
        <tr>
          <td>
						<input type="text" size="40"  name="softgames_url" value="<?php echo esc_url( $softgames['feed'] ); ?>" />
          </td>
          <td><i><?php _e("Edit this field only if Feed URL has been changed!", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e("Publisher ID", 'myarcadeplugin'); ?></h3></td></tr>
        <tr>
          <td>
						<input type="text" size="40"  name="softgames_publisher_id" value="<?php echo esc_attr( $softgames['publisher_id'] ); ?>" />
          </td>
          <td><i><?php _e("Enter your Publisher ID if available.", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e("Category", 'myarcadeplugin'); ?></h3></td></tr>

        <?php
        $softgames_categories = array(
          'all' => __("All games", 'myarcadeplugin' ),
          "Action" => "Action",
          "Adventure" => "Adventure",
          "Arcade" => "Arcade",
          "Board" => "Board",
          "Card" => "Card",
          "Casino" => "Casino",
          "Dice" => "Dice",
          "Educational" => "Educational",
          "Family" => "Family",
          "Girls" => "Girls",
          "Hidden Object" => "Hidden Object",
          "Jump&Run" => "Jump & Run",
          "Kids" => "Kids",
          "Logic Puzzles" => "Logic Puzzles",
          "Mahjong" => "Mahjong",
          "Match 3" => "Match 3",
          "Music" => "Music",
          "Pairs" => "Pairs",
          "Puzzle" => "Puzzle",
          "Racing" => "Racing",
          "Role Playing" => "Role Playing",
          "Shooting" => "Shooting",
          "Simulation" => "Simulation",
          "Solitaire" => "Solitaire",
          "Sports" => "Sports",
          "Strategy" => "Strategy",
          "Sudoku" => "Sudoku",
          "Trivia" => "Trivia",
          "Word"  => "Word",
        );
        ?>
        <tr>
          <td>
            <select size="1" name="softgames_category" id="softgames_category">
              <?php foreach ( $softgames_categories as $key => $value ) : ?>
							 <option value="<?php echo esc_attr( $key ); ?>" <?php myarcade_selected( $softgames['category'], $key ); ?>><?php echo esc_html( $value ); ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td><i><?php _e("Select which games you would like to fetch.", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e("Thumbnail Size", 'myarcadeplugin'); ?></h3></td></tr>

        <tr>
          <td>
            <select size="1" name="softgames_thumbnail" id="softgames_thumbnail">
              <option value="thumb" <?php myarcade_selected($softgames['thumbnail'], 'thumb'); ?> ><?php _e("60x60", 'myarcadeplugin'); ?></option>
              <option value="thumbBig" <?php myarcade_selected($softgames['thumbnail'], 'thumbBig'); ?> ><?php _e("120x120", 'myarcadeplugin'); ?></option>
              <option value="teaser" <?php myarcade_selected($softgames['thumbnail'], 'teaser'); ?> ><?php _e("300x180", 'myarcadeplugin'); ?></option>
              <option value="image620" <?php myarcade_selected($softgames['thumbnail'], 'image620'); ?> ><?php _e("520x520", 'myarcadeplugin'); ?></option>
            </select>
          </td>
          <td><i><?php _e("Select a thumbnail size.", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", 'myarcadeplugin'); ?></h3></td></tr>

        <tr>
          <td>
            <input type="checkbox" name="softgames_cron_publish" value="true" <?php myarcade_checked($softgames['cron_publish'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?></label>
          </td>
          <td><i><?php _e("Enable this if you want to publish games automatically. Go to 'General Settings' to select a cron interval.", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h4><?php _e("Publish Games", 'myarcadeplugin'); ?></h4></td></tr>

        <tr>
          <td>
						<input type="text" size="40"  name="softgames_cron_publish_limit" value="<?php echo esc_attr( $softgames['cron_publish_limit'] ); ?>" />
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
function myarcade_default_settings_softgames() {
  return array(
    'feed'          => 'https://publishers.softgames.com/categories/new_games.json',
    'publisher_id'  => 'pub-10477-18399',
    'category'      => 'all',
    'thumbnail'     => 'thumbBig',
    'cron_publish'  => false,
    'cron_publish_limit' => '1',
  );
}

/**
 * Retrieve available distributor's categories mapped to MyArcadePlugin categories
 *
 * @return  array Distributor categories
 */
function myarcade_get_categories_softgames() {
  return array(
    "Action"      => "Action,Jump&Run",
    "Adventure"   => true,
    "Arcade"      => "Arcade,Role Playing",
    "Board Game"  => "Board,Mahjong,Pairs,Solitaire,Sudoku,Card",
    "Casino"      => "Casino,Dice",
    "Defense"     => false,
    "Customize"   => false,
    "Dress-Up"    => "Girls",
    "Driving"     => "Racing",
    "Education"   => "Educational,Family,Trivia,Word,Kids",
    "Fighting"    => false,
    "Jigsaw"      => false,
    "Multiplayer" => false,
    "Other"       => "Simulation",
    "Puzzles"     => "Hidden Object,Logic Puzzles,Match 3,Puzzle",
    "Rhythm"      => "Music",
    "Shooting"    => true,
    "Sports"      => true,
    "Strategy"    => true,
  );
}

/**
 * Fetch games
 *
 * @param   array  $args Fetching parameters
 * @return  void
 */
function myarcade_feed_softgames( $args = array() ) {

  $defaults = array(
    'echo'     => false,
    'settings' => array(),
  );

  $args = wp_parse_args( $args, $defaults );
  extract($args);

  $new_games = 0;
  $add_game = false;

	$softgames            = MyArcade()->get_settings( 'softgames' );
  $softgames_categories = myarcade_get_categories_softgames();
  $feedcategories       = get_option( 'myarcade_categories' );

  // Init settings var's
  if ( ! empty($settings) ) {
    $settings = array_merge( $softgames, $settings );
  }
  else {
    $settings = $softgames;
  }

  if ( empty( $settings['publisher_id'] ) ) {
    // Use our default affiliate credentials
    $settings['publisher_id'] = 'pub-10477-18399';
  }

  // Generate Feed URL
  $settings['feed'] = add_query_arg( array( "p" => $settings['publisher_id'] ), trim( $settings['feed'] ) );

  if ( 'all' !== $settings['category'] ) {
    $settings['feed'] = add_query_arg( array( "categories" => urlencode( $settings['category'] ) ), trim( $settings['feed'] ) );
  }

  // Include required fetch functions
  require_once( MYARCADE_CORE_DIR . '/fetch.php' );

  // Fetch games
  $json_games = myarcade_fetch_games( array( 'url' => $settings['feed'], 'service' => 'json', 'echo' => $echo ) );

  //====================================
  if ( ! empty( $json_games ) ) {
    foreach ( $json_games as $game_obj ) {
      if ( ! isset( $game_obj->title ) || ! isset( $game_obj->description ) ) {
        continue;
      }

      $add_game   = false;

      // Create a new game object
      $game           = new stdClass();

      $game->uuid     = crc32( $game_obj->title ) . '_softgames';
      // Generate a game tag for this game
      $game->game_tag = md5( $game_obj->title . 'softgames' );

      // Get game categories into an array
      $categories        = explode( ',', $game_obj->categories );
      // Initialize the category name
      $categories_string = 'Other';

      // Loop trough game categories
      foreach( $categories as $gamecat ) {
					if ( empty( $gamecat ) ) {
						$add_game = true;
						break;
					}

        // Loop trough MyArcade categories
        foreach ( $feedcategories as $feedcat ) {
          if ( 'checked' == $feedcat['Status'] ) {
            if ( ! empty( $softgames_categories[ $feedcat['Name'] ] ) ) {
              // Set category name to check
              if ( $softgames_categories[ $feedcat['Name'] ] === true ) {
                $cat_name = $feedcat['Name'];
              }
              else {
                $cat_name = $softgames_categories[ $feedcat['Name'] ];
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

      // We need valid game dimensions
      // Init dimensions for a portrait game
      $game->width = 588;
      $game->height = 800;

      if ( empty ( $game_obj->portrait ) ) {
        // Landscape
        $game->width = 800;
        $game->height = 588;
      }

      $game->type        = 'softgames';
      $game->name        = esc_sql( $game_obj->title );
      $game->slug        = myarcade_make_slug( $game_obj->title );
      $game->description = esc_sql( $game_obj->description );
      $game->categs      = $categories_string;
      $game->swf_url     = esc_sql( strtok( $game_obj->link, '?' ) );

      if ( isset( $game_obj->screenshots ) && is_array( $game_obj->screenshots ) ) {
        $i = 0;
        foreach( $game_obj->screenshots as $screenshot ) {
          $i++;
          $screenshot_string = "screen{$i}_url";
          $game->{$screenshot_string} = esc_sql( strtok( $screenshot, '?' ) );
        }
      }

      $thumb_size = $settings['thumbnail'];

      if ( ! empty( $game_obj->$thumb_size ) ) {
        $game->thumbnail_url = esc_sql( strtok( $game_obj->$thumb_size, '?' ) );
      }
      else {
        $game->thumbnail_url = esc_sql( strtok( $game_obj->thumbBig, '?' ) );
      }

      // Add game to the database
      if ( myarcade_add_fetched_game( $game, $args ) ) {
        $new_games++;
      }
    }
  }

  // Show, how many games have been fetched
  myarcade_fetched_message( $new_games, $echo );

}

/**
 * Return game embed method
 *
 * @return  string Embed Method
 */
function myarcade_embedtype_softgames() {
  return 'iframe';
}

/**
 * Return if games can be downloaded by this distirbutor
 *
 * @return  bool True if games can be downloaded
 */
function myarcade_can_download_softgames() {
  return false;
}
