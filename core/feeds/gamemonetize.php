<?php
/**
 * GameMonetize Feed - https://gamemonetize.com/rss-builder
 *
 */

// No direct Access
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Display distributor settings on admin page
 *
 * @return  void
 */
function myarcade_settings_gamemonetize() {

  $gamemonetize = MyArcade()->get_settings( 'gamemonetize' );

  ?>
  <h2 class="trigger"><?php _e("GameMonetize", 'myarcadeplugin'); ?></h2>
  <div class="toggle_container">
    <div class="block">
      <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
        <tr>
          <td colspan="2">
            <i>
              <?php printf( __( "%s distributes HTML5 and mobile games.", 'myarcadeplugin' ), '<a href="https://gamemonetize.com" target="_blank">GameMonetize</a>' ); ?>
            </i>
            <br /><br />
          </td>
        </tr>
        <tr><td colspan="2"><h3><?php _e("Feed URL", 'myarcadeplugin'); ?></h3></td></tr>
        <tr>
          <td>
            <input type="text" size="40"  name="gamemonetize_url" value="<?php echo esc_url( $gamemonetize['feed'] ); ?>" />
          </td>
          <td><i><?php _e("Edit this field only if Feed URL has been changed!", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e("Category", 'myarcadeplugin'); ?></h3></td></tr>

        <tr>
          <td>
            <select size="1" name="gamemonetize_category" id="gamemonetize_category">
              <option value="All" <?php myarcade_selected( $gamemonetize['category'], 'all'); ?> ><?php _e("All Games", 'myarcadeplugin'); ?></option>
              <option value="1" <?php myarcade_selected( $gamemonetize['category'], '1'); ?> ><?php _e(".IO", 'myarcadeplugin'); ?></option>
              <option value="2" <?php myarcade_selected( $gamemonetize['category'], '2'); ?> ><?php _e("2 Player", 'myarcadeplugin'); ?></option>
              <option value="3" <?php myarcade_selected( $gamemonetize['category'], '3'); ?> ><?php _e("3D", 'myarcadeplugin'); ?></option>
              <option value="0" <?php myarcade_selected( $gamemonetize['category'], '0'); ?> ><?php _e("Action", 'myarcadeplugin'); ?></option>
              <option value="4" <?php myarcade_selected( $gamemonetize['category'], '4'); ?> ><?php _e("Adventure", 'myarcadeplugin'); ?></option>
              <option value="5" <?php myarcade_selected( $gamemonetize['category'], '5'); ?> ><?php _e("Arcade", 'myarcadeplugin'); ?></option>
              <option value="6" <?php myarcade_selected( $gamemonetize['category'], '6'); ?> ><?php _e("Bejeweled", 'myarcadeplugin'); ?></option>
              <option value="7" <?php myarcade_selected( $gamemonetize['category'], '7'); ?> ><?php _e("Boys", 'myarcadeplugin'); ?></option>
              <option value="8" <?php myarcade_selected( $gamemonetize['category'], '8'); ?> ><?php _e("Clicker", 'myarcadeplugin'); ?></option>
              <option value="9" <?php myarcade_selected( $gamemonetize['category'], '9'); ?> ><?php _e("Cooking", 'myarcadeplugin'); ?></option>
              <option value="10" <?php myarcade_selected( $gamemonetize['category'], '10'); ?> ><?php _e("Girls", 'myarcadeplugin'); ?></option>
              <option value="11" <?php myarcade_selected( $gamemonetize['category'], '11'); ?> ><?php _e("Hypercasual", 'myarcadeplugin'); ?></option>
              <option value="12" <?php myarcade_selected( $gamemonetize['category'], '12'); ?> ><?php _e("Multiplayer", 'myarcadeplugin'); ?></option>
              <option value="13" <?php myarcade_selected( $gamemonetize['category'], '13'); ?> ><?php _e("Puzzle", 'myarcadeplugin'); ?></option>
              <option value="14" <?php myarcade_selected( $gamemonetize['category'], '14'); ?> ><?php _e("Racing", 'myarcadeplugin'); ?></option>
              <option value="15" <?php myarcade_selected( $gamemonetize['category'], '15'); ?> ><?php _e("Shooting", 'myarcadeplugin'); ?></option>
              <option value="16" <?php myarcade_selected( $gamemonetize['category'], '16'); ?> ><?php _e("Soccer", 'myarcadeplugin'); ?></option>
              <option value="17" <?php myarcade_selected( $gamemonetize['category'], '17'); ?> ><?php _e("Sports", 'myarcadeplugin'); ?></option>
              <option value="18" <?php myarcade_selected( $gamemonetize['category'], '18'); ?> ><?php _e("Stickman", 'myarcadeplugin'); ?></option>
            </select>
          </td>
          <td><i><?php _e("Select which games you would like to fetch.", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e("Automated Game Fetching", 'myarcadeplugin'); ?></h3></td></tr>
        <tr>
          <td>
            <input type="checkbox" name="gamemonetize_cron_fetch" value="true" <?php myarcade_checked( $gamemonetize['cron_fetch'], true ); ?> /><label class="opt">&nbsp;<?php _e( "Yes", 'myarcadeplugin' ); ?></label>
          </td>
          <td><i><?php _e( "Enable this if you want to fetch games automatically. Go to 'General Settings' to select a cron interval.", 'myarcadeplugin' ); ?></i></td>
        </tr>

        <tr><td colspan="2"><h4><?php _e("Fetch Games", 'myarcadeplugin'); ?></h4></td></tr>

        <tr>
          <td>
            <input type="text" size="40"  name="gamemonetize_cron_fetch_limit" value="<?php echo esc_attr( $gamemonetize['cron_fetch_limit'] ); ?>" />
          </td>
          <td><i><?php _e( "How many games should be fetched on every cron trigger?", 'myarcadeplugin' ); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e( "Automated Game Publishing", 'myarcadeplugin' ); ?></h3></td></tr>

        <tr>
          <td>
            <input type="checkbox" name="gamemonetize_cron_publish" value="true" <?php myarcade_checked( $gamemonetize['cron_publish'], true ); ?> /><label class="opt">&nbsp;<?php _e( "Yes", 'myarcadeplugin' ); ?></label>
          </td>
          <td><i><?php _e("Enable this if you want to publish games automatically. Go to 'General Settings' to select a cron interval.", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h4><?php _e("Publish Games", 'myarcadeplugin'); ?></h4></td></tr>

        <tr>
          <td>
            <input type="text" size="40"  name="gamemonetize_cron_publish_limit" value="<?php echo esc_attr( $gamemonetize['cron_publish_limit'] ); ?>" />
          </td>
          <td><i><?php _e( "How many games should be published on every cron trigger?", 'myarcadeplugin' ); ?></i></td>
        </tr>

      </table>
      <input class="button button-primary" id="submit" type="submit" name="submit" value="<?php _e("Save Settings", 'myarcadeplugin'); ?>" />
    </div>
  </div>
  <?php
}

/**
 * Retrieve distributor's default settings
 *
 * @return  array Default settings
 */
function myarcade_default_settings_gamemonetize() {
  return array(
    'feed'          => 'https://gamemonetize.com/rss.php',
    'category'      => 'All',
    'cron_fetch'    => false,
    'cron_fetch_limit' => '1',
    'cron_publish'  => false,
    'cron_publish_limit' => '1',
  );
}

/**
 * Handle distributor settings update
 *
 * @return  void
 */
function myarcade_save_settings_gamemonetize() {

  myarcade_check_settings_nonce();

  $defaults = myarcade_default_settings_gamemonetize();

  $settings = array();
  $settings['feed']               = esc_sql( filter_input( INPUT_POST, 'gamemonetize_url' ) );
  $settings['category']           = filter_input( INPUT_POST, 'gamemonetize_category' );
  $settings['cron_fetch']         = filter_input( INPUT_POST, 'gamemonetize_cron_fetch', FILTER_VALIDATE_BOOLEAN );
  $settings['cron_fetch_limit']   = filter_input( INPUT_POST, 'gamemonetize_cron_fetch_limit', FILTER_VALIDATE_INT, array( "options" => array( "default" => $defaults['cron_fetch_limit'] ) ) );
  $settings['cron_publish']       = filter_input( INPUT_POST, 'gamemonetize_cron_publish', FILTER_VALIDATE_BOOLEAN );
  $settings['cron_publish_limit'] = filter_input( INPUT_POST, 'gamemonetize_cron_publish_limit', FILTER_VALIDATE_INT, array( "options" => array( "default" => $defaults['cron_publish_limit'] ) ) );

  // Update settings
  update_option( 'myarcade_gamemonetize', $settings );
}

/**
 * Retrieve available distributor's categories mapped to MyArcadePlugin categories
 *
 * @return  array Distributor categories
 */
function myarcade_get_categories_gamemonetize() {
  return array(
    "Action"      => true,
    "Adventure"   => true,
    "Arcade"      => true,
    "Board Game"  => "Bejeweled, Clicker",
    "Casino"      => false,
    "Defense"     => false,
    "Customize"   => false,
    "Dress-Up"    => "Girls",
    "Driving"     => true,
    "Education"   => false,
    "Fighting"    => true,
    "Jigsaw"      => false,
    "Multiplayer" => "2 Player,Multiplayer",
    "Other"       => ".IO,3D,Boys,Cooking,Stickman,Hypercasual,Other",
    "Puzzles"     => true,
    "Rhythm"      => false,
    "Shooting"    => true,
    "Sports"      => "Soccer,Sports,Racing",
    "Strategy"    => false,
  );
}

/**
 * Fetch games
 *
 * @param   array  $args Fetching parameters
 * @return  void
 */
function myarcade_feed_gamemonetize( $args = array() ) {

  $defaults = array(
    'echo'     => false,
    'settings' => array(),
  );

  $args = wp_parse_args( $args, $defaults );
  extract($args);

  $new_games = 0;
  $add_game  = false;

  $gamemonetize            = MyArcade()->get_settings( 'gamemonetize' );
  $gamemonetize_categories = myarcade_get_categories_gamemonetize();
  $feedcategories          = MyArcade()->get_settings( 'categories' );
  $general                 = MyArcade()->get_settings( 'general' );

  // Init settings var's
  if ( ! empty( $settings ) ) {
    $settings = array_merge( $gamemonetize, $settings );
  }
  else {
    $settings = $gamemonetize;
  }

  $limit = 'All';

  if ( ! empty( $settings['limit'] ) ) {
    $limit = $settings['limit'];
  }


  // Generate Feed URL
  $settings['feed'] = add_query_arg( array(
    'format'   => 'json',
    'amount'   => $limit,
    'category' => $settings['category'],
  ), trim( $settings['feed'] ) );

  if ( isset( $general['types'] ) && 'mobile' == $general['types'] ) {
    $settings['feed'] = add_query_arg( array( "type"  => "mobile" ),  $settings['feed'] );
  }
  else {
    $settings['feed'] = add_query_arg( array( "type"  =>"html5" ),  $settings['feed'] );
  }

  // Include required fetch functions
  require_once( MYARCADE_CORE_DIR . '/fetch.php' );

  // Fetch games
  $json_games = myarcade_fetch_games( array( 'url' => $settings['feed'], 'service' => 'json', 'echo' => $echo) );

  //====================================
  if ( ! empty( $json_games ) ) {
    foreach ( $json_games as $game_obj ) {
      $add_game       = false;

      // Create a new game object
      $game           = new stdClass();

      $game->uuid     = $game_obj->id . '_gamemonetize';
      $game->game_tag = md5( $game_obj->id . 'gamemonetize' );

      if ( empty( $game_obj->category ) ) {
        $game_obj->category = 'Other';
      }

      // Get game categories into an array
      $categories        = explode( ',', $game_obj->category );
      // Initialize the category name
      $categories_string = 'Other';

      // Loop trough game categories
      foreach( $categories as $gamecat ) {
        // Loop trough MyArcade categories
        foreach ( $feedcategories as $feedcat ) {
          if ( 'checked' == $feedcat['Status'] ) {
            if ( ! empty( $gamemonetize_categories[ $feedcat['Name'] ] ) ) {
              // Set category name to check
              if ( $gamemonetize_categories[ $feedcat['Name'] ] === true ) {
                $cat_name = $feedcat['Name'];
              }
              else {
                $cat_name = $gamemonetize_categories[ $feedcat['Name'] ];
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

      $game->type          = 'gamemonetize';
      $game->name          = esc_sql( $game_obj->title );
      $game->slug          = myarcade_make_slug( $game_obj->title );
      $game->description   = esc_sql( $game_obj->description );
      $game->instructions  = esc_sql( $game_obj->instructions );
      $game->tags          = esc_sql( $game_obj->tags );
      $game->categs        = $categories_string;
      $game->swf_url       = esc_sql( $game_obj->url );
      $game->width         = esc_sql( $game_obj->width );
      $game->height        = esc_sql( $game_obj->height );
      $game->thumbnail_url = esc_sql( $game_obj->thumb );

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
function myarcade_embedtype_gamemonetize() {
  return 'iframe';
}

/**
 * Return if games can be downloaded by this distirbutor
 *
 * @return  bool True if games can be downloaded
 */
function myarcade_can_download_gamemonetize() {
  return false;
}
