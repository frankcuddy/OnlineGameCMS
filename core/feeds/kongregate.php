<?php
/**
 * Kongregate - https://www.kongregate.com/games_for_your_site
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
function myarcade_settings_kongregate() {
	$kongregate = MyArcade()->get_settings( 'kongregate' );
  ?>
  <h2 class="trigger"><?php myarcade_premium_span(); _e("Kongregate", 'myarcadeplugin'); ?></h2>
  <div class="toggle_container">
    <div class="block">
      <?php myarcade_premium_message(); ?>
      <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
        <tr>
          <td colspan="2">
            <i>
							<?php printf( __( '%s distributes Flash games.', 'myarcadeplugin' ), '<a href="http://www.kongregate.com/games_for_your_site" target="_blank">Kongegrate</a>' ); ?>
            </i>
            <br /><br />
          </td>
        </tr>
				<tr><td colspan="2"><h3><?php _e( 'Feed URL', 'myarcadeplugin' ); ?></h3></td></tr>

        <tr>
          <td>
						<input type="text" size="40"  name="kongurl" value="<?php echo esc_url( $kongregate['feed'] ); ?>" />
          </td>
					<td><i><?php _e( 'Edit this field only if Feed URL has been changed!', 'myarcadeplugin' ); ?></i></td>
        </tr>

				<tr><td colspan="2"><h3><?php _e( 'Automated Game Publishing', 'myarcadeplugin' ); ?></h3></td></tr>

        <tr>
          <td>
						<input type="checkbox" name="kong_cron_publish" value="true" <?php myarcade_checked( $kongregate['cron_publish'], true ); ?> /><label class="opt">&nbsp;<?php _e( 'Yes', 'myarcadeplugin' ); ?></label>
          </td>
          <td><i><?php _e("Enable this if you want to publish games automatically. Go to 'General Settings' to select a cron interval.", 'myarcadeplugin'); ?></i></td>
        </tr>

				<tr><td colspan="2"><h4><?php _e( 'Publish Games', 'myarcadeplugin' ); ?></h4></td></tr>

        <tr>
          <td>
						<input type="text" size="40"  name="kong_cron_publish_limit" value="<?php echo esc_attr( $kongregate['cron_publish_limit'] ); ?>" />
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
function myarcade_default_settings_kongregate() {
  return array(
    'feed'          => 'http://www.kongregate.com/games_for_your_site.xml',
    'cron_publish'  => false,
    'cron_publish_limit' => '1',
  );
}

/**
 * Handle distributor settings update
 */
function myarcade_save_settings_kongregate() {

  myarcade_check_settings_nonce();

	// Kongeregate Settings.
  $kongregate = array();

  $kongregate['feed'] = esc_url_raw( filter_input( INPUT_POST, 'kongurl' ) );
  $kongregate['cron_publish'] = (isset($_POST['kong_cron_publish']) ) ? true : false;
  $kongregate['cron_publish_limit']  = (isset($_POST['kong_cron_publish_limit']) ) ? intval($_POST['kong_cron_publish_limit']) : 1;

	// Update Settings.
  update_option('myarcade_kongregate', $kongregate);
}

/**
 * Retrieve available distributor's categories mapped to MyArcadePlugin categories
 *
 * @return  array Distributor categories
 */
function myarcade_get_categories_kongregate() {
  return array(
		'Action'      => true,
		'Adventure'   => 'Adventure & RPG',
		'Arcade'      => false,
		'Board Game'  => false,
		'Casino'      => false,
		'Defense'     => false,
		'Customize'   => false,
		'Dress-Up'    => false,
		'Driving'     => false,
		'Education'   => false,
		'Fighting'    => false,
		'Jigsaw'      => false,
		'Multiplayer' => true,
		'Other'       => 'Idle',
		'Puzzles'     => 'Puzzle',
		'Rhythm'      => 'Music & More',
		'Shooting'    => 'Shooter',
		'Sports'      => 'Sports & Racing',
		'Strategy'    => 'Strategy & Defense',
  );
}

/**
 * Fetch Kongregate games
 *
 * @param array $args Fetching parameters.
 */
function myarcade_fetch_settings_kongregate() {

  ?>
  <div class="myarcade_border white hide mabp_680" id="kongregate">
    <?php myarcade_premium_message(); ?>
  </div>
  <?php
}

/**
 * Fetch Kongregate games
 *
 * @param array $args Fetching parameters.
 */
function myarcade_feed_kongregate( $args = array() ) {
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
function myarcade_embedtype_kongregate() {
  return 'flash';
}
