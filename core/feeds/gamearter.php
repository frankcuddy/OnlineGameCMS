<?php
/**
 * GameArter - https://www.gamearter.com/export/v1/games
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 */

/**
 * Save options
 *
 * @return void
 */
function myarcade_save_settings_gamearter() {

  myarcade_check_settings_nonce();

  $settings                       = array();
  $settings['feed']               = esc_sql( filter_input( INPUT_POST, 'gamearter_url' ) );
  $settings['cron_publish']       = filter_input( INPUT_POST, 'gamearter_cron_publish', FILTER_VALIDATE_BOOLEAN );
  $settings['cron_publish_limit'] = filter_input( INPUT_POST, 'gamearter_cron_publish_limit', FILTER_VALIDATE_INT, array( "options" => array( "default" => 1) ) );

  // Update settings.
  update_option( 'myarcade_gamearter', $settings );
}

/**
 * Display distributor settings on admin page.
 *
 * @return void
 */
function myarcade_settings_gamearter() {
  $gamearter = MyArcade()->get_settings( 'gamearter' );
  ?>
  <h2 class="trigger"><?php myarcade_premium_span(); _e( "GameArter", 'myarcadeplugin'); ?></h2>
  <div class="toggle_container">
    <div class="block">
      <?php myarcade_premium_message(); ?>
      <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
        <tr>
          <td colspan="2">
            <i>
            <?php printf( __( '%s distributes WEBGL and HTML5 games.', 'myarcadeplugin' ), '<a href="https://www.gamearter.com/games" target="_blank">GameArter</a>' ); ?>
            </i>
            <br /><br />
          </td>
        </tr>
        <tr><td colspan="2"><h3><?php _e("Feed URL", 'myarcadeplugin'); ?></h3></td></tr>
        <tr>
          <td>
            <input type="text" size="40"  name="gamearter_url" value="<?php echo esc_url( $gamearter['feed'] ); ?>" />
          </td>
          <td><i><?php _e("Edit this field only if Feed URL has been changed!", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", 'myarcadeplugin'); ?></h3></td></tr>

        <tr>
          <td>
            <input type="checkbox" name="gamearter_cron_publish" value="true" <?php myarcade_checked($gamearter['cron_publish'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?></label>
          </td>
          <td><i><?php _e("Enable this if you want to publish games automatically. Go to 'General Settings' to select a cron interval.", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h4><?php _e("Publish Games", 'myarcadeplugin'); ?></h4></td></tr>

        <tr>
          <td>
            <input type="text" size="40" name="gamearter_cron_publish_limit" value="<?php echo esc_attr( $gamearter['cron_publish_limit'] ); ?>" />
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
function myarcade_default_settings_gamearter() {
  return array(
    'feed'                => 'https://www.gamearter.com/export/v1/games',
    'cron_publish'        => false,
    'cron_publish_limit'  => '1',
  );
}

/**
 * Retrieve available distributor's categories mapped to MyArcadePlugin categories
 *
 * @return  array Distributor categories
 */
function myarcade_get_categories_gamearter() {
  return array(
    "Action"      => true,
    "Adventure"   => false,
    "Arcade"      => false,
    "Board Game"  => false,
    "Casino"      => false,
    "Defense"     => false,
    "Customize"   => 'girls',
    "Dress-Up"    => false,
    "Driving"     => true,
    "Education"   => false,
    "Fighting"    => false,
    "Jigsaw"      => false,
    "Multiplayer" => 'mmo,multiplayer',
    "Other"       => '3D',
    "Puzzles"     => false,
    "Rhythm"      => false,
    "Shooting"    => false,
    "Sports"      => true,
    "Strategy"    => 'logic,strategy',
  );
}

/**
 * Display distributor fetch games options
 *
 * @return  void
 */
function myarcade_fetch_settings_gamearter() {
  ?>
  <div class="myarcade_border white hide mabp_680" id="gamearter">
    <?php myarcade_premium_message(); ?>
  </div>
  <?php
}

/**
 * Fetch games
 *
 * @param   array  $args Fetching parameters
 * @return  void
 */
function myarcade_feed_gamearter( $args = array() ) {

  ?>
  <div class="myarcade_border white mabp_680">
  <?php myarcade_premium_message(); ?>
  </div>
  <?php
}

/**
 * Return game embed method
 *
 * @return  string Embed Method
 */
function myarcade_embedtype_gamearter() {
  return 'iframe';
}

/**
 * Return if games can be downloaded by this distirbutor
 *
 * @return  bool True if games can be downloaded
 */
function myarcade_can_download_gamearter() {
  return false;
}
