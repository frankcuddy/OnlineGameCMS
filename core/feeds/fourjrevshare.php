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
function myarcade_save_settings_fourjrevshare() {

  myarcade_check_settings_nonce();

  $settings = array();
  $settings['feed'] = esc_sql( filter_input( INPUT_POST, 'fourjrevshare_url' ) );
  $settings['pubid'] = filter_input( INPUT_POST, 'fourjrevshare_pubid' );

  $settings['cron_publish'] = filter_input( INPUT_POST, 'fourjrevshare_cron_publish', FILTER_VALIDATE_BOOLEAN );
  $settings['cron_publish_limit'] = filter_input( INPUT_POST, 'fourjrevshare_cron_publish_limit', FILTER_VALIDATE_INT, array( "options" => array( "default" => 1) ) );

  // Update settings
  update_option( 'myarcade_fourjrevshare', $settings );
}

/**
 * Display distributor settings on admin page
 *
 * @return  void
 */
function myarcade_settings_fourjrevshare() {
  $fourj = MyArcade()->get_settings( 'fourjrevshare' );
  ?>
  <h2 class="trigger"><?php myarcade_premium_span(); _e( "4J (Revenue Share)", 'myarcadeplugin'); ?></h2>
  <div class="toggle_container">
    <div class="block">
      <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
        <tr>
          <td colspan="2">
            <i>
               <?php printf( __( "This feed is for registered %s users with a valid publisher ID only.", 'myarcadeplugin' ), '<a href="http://w.4j.com/" target="_blank">4J</a>' ); ?>
            </i>
            <br /><br />
          </td>
        </tr>
        <tr><td colspan="2"><h3><?php _e("Feed URL", 'myarcadeplugin'); ?></h3></td></tr>
        <tr>
          <td>
            <input type="text" size="40"  name="fourjrevshare_url" value="<?php echo esc_url( $fourj['feed'] ); ?>" />
          </td>
          <td><i><?php _e("Edit this field only if Feed URL has been changed!", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e("Publisher ID", 'myarcadeplugin'); ?></h3></td></tr>
        <tr>
          <td>
            <input type="text" size="40"  name="fourjrevshare_pubid" value="<?php echo esc_attr( $fourj['pubid'] ); ?>" />
          </td>
          <td><i><?php _e("Enter your publisher ID here. This is required in order to make money with 4J.", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h3><?php _e("Automated Game Publishing", 'myarcadeplugin'); ?></h3></td></tr>

        <tr>
          <td>
            <input type="checkbox" name="fourjrevshare_cron_publish" value="true" <?php myarcade_checked($fourj['cron_publish'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?></label>
          </td>
          <td><i><?php _e("Enable this if you want to publish games automatically. Go to 'General Settings' to select a cron interval.", 'myarcadeplugin'); ?></i></td>
        </tr>

        <tr><td colspan="2"><h4><?php _e("Publish Games", 'myarcadeplugin'); ?></h4></td></tr>

        <tr>
          <td>
            <input type="text" size="40" name="fourjrevshare_cron_publish_limit" value="<?php echo esc_attr( $fourj['cron_publish_limit'] ); ?>" />
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
function myarcade_default_settings_fourjrevshare() {
  return array(
    'feed'                => 'http://h5.4j.com/gamefeed.php',
    'pubid'               => '',
    'cron_publish'        => false,
    'cron_publish_limit'  => '1',
  );
}

/**
 * Retrieve available distributor's categories mapped to MyArcadePlugin categories
 *
 * @return  array Distributor categories
 */
function myarcade_get_categories_fourjrevshare() {
  return array(
    "Action"      => 'Fruit,Bomb',
    "Adventure"   => 'Adventure',
    "Arcade"      => false,
    "Board Game"  => 'Quiz,Word,Memory,Matching',
    "Casino"      => false,
    "Defense"     => 'Defense',
    "Customize"   => 'Princess,Make up,Girl,Decorate',
    "Dress-Up"    => 'Dress up',
    "Driving"     => 'Driving,Racing,Car',
    "Education"   => 'Baby',
    "Fighting"    => 'Fighting',
    "Jigsaw"      => false,
    "Multiplayer" => false,
    "Other"       => 'Boy',
    "Puzzles"     => 'Puzzle',
    "Rhythm"      => false,
    "Shooting"    => 'Shooting,Killing,Gun',
    "Sports"      => 'Ball,Sports,Running',
    "Strategy"    => 'Brain,Match 3,Physics,Number,Timing,Simulation,Obstacle,Management,Bubble Shooter,Skil',
  );
}

/**
 * Display distributor fetch games options
 *
 * @return  void
 */
function myarcade_fetch_settings_fourjrevshare() {
  ?>
  <div class="myarcade_border white hide mabp_680" id="fourjrevshare">
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
function myarcade_feed_fourjrevshare( $args = array() ) {

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
function myarcade_embedtype_fourjrevshare() {
  return 'iframe';
}

/**
 * Return if games can be downloaded by this distirbutor
 *
 * @return  bool True if games can be downloaded
 */
function myarcade_can_download_fourjrevshare() {
  return false;
}
