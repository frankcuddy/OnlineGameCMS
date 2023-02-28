<?php
/**
 * HTML Games Feed
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 */

// No direct Access
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Display distributor settings on admin page
 *
 * @version 5.18.0
 * @access  public
 * @return  void
 */
function myarcade_settings_htmlgames() {

  $htmlgames = MyArcade()->get_settings( 'htmlgames' );
  ?>
  <h2 class="trigger"><?php myarcade_premium_span(); _e("HTML Games", 'myarcadeplugin'); ?></h2>
  <div class="toggle_container">
    <div class="block">
      <?php myarcade_premium_message(); ?>
      <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
        <tr>
          <td colspan="2">
            <i>
              <?php printf( __( "%s distributes HTML5 games.", 'myarcadeplugin' ), '<a href="http://htmlgames.com" target="_blank">HTMLGames.com</a>' ); ?>
            </i>
            <br /><br />
          </td>
        </tr>
        <tr><td colspan="2"><h3><?php _e("Feed URL", 'myarcadeplugin'); ?></h3></td></tr>
        <tr>
          <td>
            <input type="text" size="40"  name="htmlgames_url" value="<?php echo esc_url( $htmlgames['feed'] ); ?>" />
          </td>
          <td><i><?php _e("Edit this field only if Feed URL has been changed!", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e("Category", 'myarcadeplugin'); ?></h3></td></tr>

        <tr>
          <td>
            <select size="1" name="htmlgames_category" id="htmlgames_category">
              <option value="all" <?php myarcade_selected($htmlgames['category'], 'all'); ?> ><?php _e("All Games", 'myarcadeplugin'); ?></option>
              <option value="Classic games" <?php myarcade_selected($htmlgames['category'], '2'); ?> ><?php _e("Classic games", 'myarcadeplugin'); ?></option>
              <option value="Hidden objects" <?php myarcade_selected($htmlgames['category'], '3'); ?> ><?php _e("Hidden objects", 'myarcadeplugin'); ?></option>
              <option value="Mahjong" <?php myarcade_selected($htmlgames['category'], '5'); ?> ><?php _e("Mahjong", 'myarcadeplugin'); ?></option>
              <option value="Match 3 games" <?php myarcade_selected($htmlgames['category'], '6'); ?> ><?php _e("Match 3 games", 'myarcadeplugin'); ?></option>
              <option value="Mind games" <?php myarcade_selected($htmlgames['category'], '7'); ?> ><?php _e("Mind games", 'myarcadeplugin'); ?></option>
              <option value="Solitaire" <?php myarcade_selected($htmlgames['category'], '8'); ?> ><?php _e("Solitaire", 'myarcadeplugin'); ?></option>
            </select>
          </td>
          <td><i><?php _e("Select which games you would like to fetch.", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e("Thumbnail Size", 'myarcadeplugin'); ?></h3></td></tr>

        <tr>
          <td>
            <select size="1" name="htmlgames_thumbnail" id="htmlgames_thumbnail">
              <option value="thumb1" <?php myarcade_selected($htmlgames['thumbnail'], 'thumb1'); ?> ><?php _e("120x120", 'myarcadeplugin'); ?></option>
              <option value="thumb2" <?php myarcade_selected($htmlgames['thumbnail'], 'thumb2'); ?> ><?php _e("196x196", 'myarcadeplugin'); ?></option>
              <option value="thumb3" <?php myarcade_selected($htmlgames['thumbnail'], 'thumb3'); ?> ><?php _e("300x200", 'myarcadeplugin'); ?></option>
            </select>
          </td>
          <td><i><?php _e("Select a thumbnail size.", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", 'myarcadeplugin'); ?></h3></td></tr>

        <tr>
          <td>
            <input type="checkbox" name="htmlgames_cron_publish" value="true" <?php myarcade_checked($htmlgames['cron_publish'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?></label>
          </td>
          <td><i><?php _e("Enable this if you want to publish games automatically. Go to 'General Settings' to select a cron interval.", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h4><?php _e("Publish Games", 'myarcadeplugin'); ?></h4></td></tr>

        <tr>
          <td>
            <input type="text" size="40"  name="htmlgames_cron_publish_limit" value="<?php echo esc_attr( $htmlgames['cron_publish_limit'] ); ?>" />
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
 * Retrieve distributor's default settings
 *
 * @version 5.19.0
 * @since   5.19.0
 * @access  public
 * @return  array Default settings
 */
function myarcade_default_settings_htmlgames() {
  return array(
    'feed'          => 'http://www.htmlgames.com/rss/games.php',
    'category'      => 'all',
    'thumbnail'     => 'thumb1',
    'cron_publish'  => false,
    'cron_publish_limit' => '1',
  );
}

/**
 * Handle distributor settings update
 *
 * @version 5.19.0
 * @access  public
 * @return  void
 */
function myarcade_save_settings_htmlgames() {

  myarcade_check_settings_nonce();

  $settings = array();
  $settings['feed'] = esc_sql( filter_input( INPUT_POST, 'htmlgames_url' ) );
  $settings['category'] = filter_input( INPUT_POST, 'htmlgames_category' );
  $settings['thumbnail'] = filter_input( INPUT_POST, 'htmlgames_thumbnail' );
  $settings['cron_publish'] = filter_input( INPUT_POST, 'htmlgames_cron_publish', FILTER_VALIDATE_BOOLEAN );
  $settings['cron_publish_limit'] = filter_input( INPUT_POST, 'htmlgames_cron_publish_limit', FILTER_VALIDATE_INT, array( "options" => array( "default" => 1) ) );

  // Update settings
  update_option('myarcade_htmlgames', $settings);
}

/**
 * Retrieve available distributor's categories mapped to MyArcadePlugin categories
 *
 * @version 5.19.0
 * @since   5.19.0
 * @access  public
 * @return  array Distributor categories
 */
function myarcade_get_categories_htmlgames() {
  return array(
    "Action"      => false,
    "Adventure"   => false,
    "Arcade"      => "Classic games",
    "Board Game"  => "Mahjong,Match 3 games,Solitaire",
    "Casino"      => false,
    "Defense"     => false,
    "Customize"   => false,
    "Dress-Up"    => false,
    "Driving"     => false,
    "Education"   => false,
    "Fighting"    => false,
    "Jigsaw"      => false,
    "Multiplayer" => false,
    "Other"       => "Hidden objects,Mind games",
    "Puzzles"     => false,
    "Rhythm"      => false,
    "Shooting"    => false,
    "Sports"      => false,
    "Strategy"    => false,
  );
}

/**
 * Display distributor fetch games options
 */
function myarcade_fetch_settings_htmlgames() {

  ?>
  <div class="myarcade_border white hide mabp_680" id="htmlgames">
    <?php myarcade_premium_message(); ?>
  </div>
  <?php
}

/**
 * Fetch games
 *
 * @version 5.27.0
 * @since   5.18.0
 * @access  public
 * @param   array  $args Fetching parameters
 * @return  void
 */
function myarcade_feed_htmlgames( $args = array() ) {
  ?>
  <div class="myarcade_border white mabp_680">
  <?php myarcade_premium_message(); ?>
  </div>
  <?php
}

/**
 * Return game embed method
 *
 * @version 5.18.0
 * @since   5.18.0
 * @access  public
 * @return  string Embed Method
 */
function myarcade_embedtype_htmlgames() {
  return 'iframe';
}

/**
 * Return if games can be downloaded by this distirbutor
 *
 * @version 5.19.0
 * @since   5.19.0
 * @access  public
 * @return  bool True if games can be downloaded
 */
function myarcade_can_download_htmlgames() {
  return false;
}
