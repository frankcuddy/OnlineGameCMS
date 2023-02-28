<?php
/**
 * 4J - http://w.4j.com
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 */

/**
 * Save options
 *
 * @return  void
 */
function myarcade_save_settings_fourj() {

  myarcade_check_settings_nonce();

  $settings = array();
  $settings['feed'] = esc_sql( filter_input( INPUT_POST, 'fourj_url' ) );
  $settings['advertisements'] = filter_input( INPUT_POST, 'fourj_advertisements', FILTER_VALIDATE_BOOLEAN );
  $settings['copyright'] = filter_input( INPUT_POST, 'fourj_copyright', FILTER_VALIDATE_BOOLEAN );

  $settings['cron_fetch'] = filter_input( INPUT_POST, 'fourj_cron_fetch', FILTER_VALIDATE_BOOLEAN );
  $settings['cron_fetch_limit'] = filter_input( INPUT_POST, 'fourj_cron_fetch_limit', FILTER_VALIDATE_INT, array( "options" => array( "default" => 1) ) );

  $settings['cron_publish'] = filter_input( INPUT_POST, 'fourj_cron_publish', FILTER_VALIDATE_BOOLEAN );
  $settings['cron_publish_limit'] = filter_input( INPUT_POST, 'fourj_cron_publish_limit', FILTER_VALIDATE_INT, array( "options" => array( "default" => 1) ) );

  // Update settings
  update_option( 'myarcade_fourj', $settings );
}

/**
 * Display distributor settings on admin page
 *
 * @return  void
 */
function myarcade_settings_fourj() {
  $fourj = MyArcade()->get_settings( 'fourj' );
  ?>
  <h2 class="trigger"><?php myarcade_premium_span(); _e( "4J", 'myarcadeplugin'); ?></h2>
  <div class="toggle_container">
    <div class="block">
      <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
        <tr>
          <td colspan="2">
            <i>
               <?php printf( __( "%s distributes Flash, WebGL, Unity3D and HTML5 games.", 'myarcadeplugin' ), '<a href="http://w.4j.com/" target="_blank">4J</a>' ); ?>
            </i>
            <br /><br />
          </td>
        </tr>
        <tr><td colspan="2"><h3><?php _e("Feed URL", 'myarcadeplugin'); ?></h3></td></tr>
        <tr>
          <td>
            <input type="text" size="40"  name="fourj_url" value="<?php echo esc_url( $fourj['feed'] ); ?>" />
          </td>
          <td><i><?php _e("Edit this field only if Feed URL has been changed!", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e("Without Advertisements", 'myarcadeplugin'); ?></h3></td></tr>
        <tr>
          <td>
            <input type="checkbox" name="fourj_advertisements" value="true" <?php myarcade_checked( $fourj['advertisements'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?></label>
          </td>
          <td><i><?php _e("Enable this if you want to fetch only games without advertisements.", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e("Contain Copyrighted Content", 'myarcadeplugin'); ?></h3></td></tr>
        <tr>
          <td>
            <input type="checkbox" name="fourj_copyright" value="true" <?php myarcade_checked( $fourj['copyright'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?></label>
          </td>
          <td><i><?php _e("Enable this if you want to fetch games that also contain copyrighted content.", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e("Automated Game Fetching", 'myarcadeplugin'); ?></h3></td></tr>
        <tr>
          <td>
            <input type="checkbox" name="fourj_cron_fetch" value="true" <?php myarcade_checked( $fourj['cron_fetch'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?></label>
          </td>
          <td><i><?php _e("Enable this if you want to fetch games automatically. Go to 'General Settings' to select a cron interval.", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h4><?php _e("Fetch Games", 'myarcadeplugin'); ?></h4></td></tr>

        <tr>
          <td>
            <input type="text" size="40"  name="fourj_cron_fetch_limit" value="<?php echo esc_attr( $fourj['cron_fetch_limit'] ); ?>" />
          </td>
          <td><i><?php _e("How many games should be fetched on every cron trigger?", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", 'myarcadeplugin'); ?></h3></td></tr>

        <tr>
          <td>
            <input type="checkbox" name="fourj_cron_publish" value="true" <?php myarcade_checked($fourj['cron_publish'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?></label>
          </td>
          <td><i><?php _e("Enable this if you want to publish games automatically. Go to 'General Settings' to select a cron interval.", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h4><?php _e("Publish Games", 'myarcadeplugin'); ?></h4></td></tr>

        <tr>
          <td>
            <input type="text" size="40" name="fourj_cron_publish_limit" value="<?php echo esc_attr( $fourj['cron_publish_limit'] ); ?>" />
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
function myarcade_default_settings_fourj() {
  return array(
    'feed'                => 'http://w.4j.com/games.php',
    'limit'               => '100',
    'advertisements'      => true,
    'copyright'           => false,
    'cron_fetch'          => false,
    'cron_fetch_limit'    => '1',
    'cron_publish'        => false,
    'cron_publish_limit'  => '1',
  );
}

/**
 * Generate an options array with submitted fetching parameters
 *
 * @return  array Fetching options
 */
function myarcade_get_fetch_options_fourj() {

  // Get distributor settings
  $settings = MyArcade()->get_settings( 'fourj' );
  $defaults = myarcade_default_settings_fourj();
  $settings = wp_parse_args( $settings, $defaults );

  $settings['method'] = 'latest';
  $settings['offset'] = 1;

  if ( 'start' == filter_input( INPUT_POST, 'fetch' ) ) {
    $settings['limit']   = filter_input( INPUT_POST, 'limitfourj', FILTER_VALIDATE_INT, array( "options" => array( "default" => 100 ) ) );
    $settings['method']  = filter_input( INPUT_POST, 'fetchmethodfourj', FILTER_UNSAFE_RAW, array( "options" => array( "default" => 'latest') ) );
    $settings['offset']  = filter_input( INPUT_POST, 'offsetfourj', FILTER_UNSAFE_RAW, array( "options" => array( "default" => '1') ) );
  }

  return $settings;
}

/**
 * Display distributor fetch games options
 *
 * @return  void
 */
function myarcade_fetch_settings_fourj() {
  ?>
  <div class="myarcade_border white hide mabp_680" id="fourj">
    <?php myarcade_premium_message(); ?>
  </div>
  <?php
}

/**
 * Retrieve available distributor's categories mapped to MyArcadePlugin categories
 *
 * @return  array Distributor categories
 */
function myarcade_get_categories_fourj() {
  return array(
    "Action"      => true,
    "Adventure"   => true,
    "Arcade"      => true,
    "Board Game"  => false,
    "Casino"      => true,
    "Defense"     => true,
    "Customize"   => false,
    "Dress-Up"    => "girl",
    "Driving"     => true,
    "Education"   => false,
    "Fighting"    => true,
    "Jigsaw"      => true,
    "Multiplayer" => true,
    "Other"       => "3D,cooking,other",
    "Puzzles"     => "puzzle",
    "Rhythm"      => "music,rhythm",
    "Shooting"    => true,
    "Sports"      => true,
    "Strategy"    => "escape,strategy,platform,physics",
  );
}

/**
 * Fetch games
 *
 * @param   array  $args Fetching parameters
 * @return  void
 */
function myarcade_feed_fourj( $args = array() ) {

  ?>
  <div class="myarcade_border white mabp_680">
    <?php myarcade_premium_message(); ?>
  </div>
  <?php
}
