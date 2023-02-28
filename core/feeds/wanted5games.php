<?php
/**
 * Wanted 5 Games - https://wanted5games.com/
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @package MyArcadePlugin/Feed
 */

/**
 * Display distributor settings on admin page.
 */
function myarcade_settings_wanted5games() {
	$wanted5games = MyArcade()->get_settings( 'wanted5games' );
	?>
	<h2 class="trigger"><?php myarcade_premium_span(); esc_html_e( 'Wanted 5 Games', 'myarcadeplugin' ); ?></h2>
	<div class="toggle_container">
		<div class="block">
			<?php myarcade_premium_message(); ?>
			<table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
				<tr>
					<td colspan="2">
						<i>
						<?php printf( esc_html__( '%s distributes HTML5 games with a revenue share program. A publiher ID is required in order to fetch games from Wanted 5 Games.', 'myarcadeplugin' ), '<a href="https://wanted5games.com/" target="_blank">Wanted 5 Games</a>' ); ?>
						</i>
						<br /><br />
					</td>
				</tr>
				<tr><td colspan="2"><h3><?php esc_html_e( 'Feed URL', 'myarcadeplugin' ); ?></h3></td></tr>
				<tr>
					<td>
						<input type="text" size="40"  name="wanted5games_url" value="<?php echo esc_url( $wanted5games['feed'] ); ?>" />
					</td>
					<td><i><?php esc_html_e( 'Edit this field only if Feed URL has been changed!', 'myarcadeplugin' ); ?></i></td>
				</tr>

				<tr><td colspan="2"><h3><?php esc_html_e( 'Publisher ID', 'myarcadeplugin' ); ?></h3></td></tr>
				<tr>
					<td>
						<input type="text" size="40"  name="wanted5games_publisher_id" value="<?php echo esc_attr( $wanted5games['publisher_id'] ); ?>" />
					</td>
					<td><i><?php esc_html_e( 'Enter your Publisher ID if available.', 'myarcadeplugin' ); ?></i></td>
				</tr>

				<tr><td colspan="2"><h3><?php esc_html_e( 'Category', 'myarcadeplugin' ); ?></h3></td></tr>

				<tr>
					<td>
						<select size="1" name="wanted5games_category" id="wanted5games_category">
							<option value="all" <?php myarcade_selected( $wanted5games['category'], 'all' ); ?> ><?php esc_html_e( 'All Games', 'myarcadeplugin' ); ?></option>
							<option value="action" <?php myarcade_selected( $wanted5games['category'], 'action' ); ?> ><?php esc_html_e( 'Action', 'myarcadeplugin' ); ?></option>
							<option value="adventure" <?php myarcade_selected( $wanted5games['category'], 'adventure' ); ?> ><?php esc_html_e( 'Adventure', 'myarcadeplugin' ); ?></option>
							<option value="animals" <?php myarcade_selected( $wanted5games['category'], 'animals' ); ?> ><?php esc_html_e( 'Animals', 'myarcadeplugin' ); ?></option>
							<option value="arcade" <?php myarcade_selected( $wanted5games['category'], 'arcade' ); ?> ><?php esc_html_e( 'Arcade', 'myarcadeplugin' ); ?></option>
							<option value="bingo" <?php myarcade_selected( $wanted5games['category'], 'bingo' ); ?> ><?php esc_html_e( 'Bingo', 'myarcadeplugin' ); ?></option>
							<option value="board-and-card" <?php myarcade_selected( $wanted5games['category'], 'board-and-card' ); ?> ><?php esc_html_e( 'Board and Card', 'myarcadeplugin' ); ?></option>
							<option value="bubble-shooter" <?php myarcade_selected( $wanted5games['category'], 'bubble-shooter' ); ?> ><?php esc_html_e( 'Bubble Shooter', 'myarcadeplugin' ); ?></option>
							<option value="casino" <?php myarcade_selected( $wanted5games['category'], 'casino' ); ?> ><?php esc_html_e( 'Casino', 'myarcadeplugin' ); ?></option>
							<option value="cooking" <?php myarcade_selected( $wanted5games['category'], 'cooking' ); ?> ><?php esc_html_e( 'Cooking', 'myarcadeplugin' ); ?></option>
							<option value="dress-up" <?php myarcade_selected( $wanted5games['category'], 'dress-up' ); ?> ><?php esc_html_e( 'Dress-up', 'myarcadeplugin' ); ?></option>
							<option value="educational" <?php myarcade_selected( $wanted5games['category'], 'educational' ); ?> ><?php esc_html_e( 'Educational', 'myarcadeplugin' ); ?></option>
							<option value="kissing" <?php myarcade_selected( $wanted5games['category'], 'kissing' ); ?> ><?php esc_html_e( 'Kissing', 'myarcadeplugin' ); ?></option>
							<option value="mahjong" <?php myarcade_selected( $wanted5games['category'], 'mahjong' ); ?> ><?php esc_html_e( 'Mahjong', 'myarcadeplugin' ); ?></option>
							<option value="match-3" <?php myarcade_selected( $wanted5games['category'], 'match-3' ); ?> ><?php esc_html_e( 'Match 3', 'myarcadeplugin'); ?></option>
							<option value="platform" <?php myarcade_selected( $wanted5games['category'], 'platform' ); ?> ><?php esc_html_e( 'Platform', 'myarcadeplugin' ); ?></option>
							<option value="quiz" <?php myarcade_selected( $wanted5games['category'], 'quiz' ); ?> ><?php esc_html_e( 'Quiz', 'myarcadeplugin' ); ?></option>
							<option value="racing" <?php myarcade_selected( $wanted5games['category'], 'racing' ); ?> ><?php esc_html_e( 'Racing', 'myarcadeplugin' ); ?></option>
							<option value="shooting" <?php myarcade_selected( $wanted5games['category'], 'shooting' ); ?> ><?php esc_html_e( 'Shooting', 'myarcadeplugin' ); ?></option>
							<option value="skill" <?php myarcade_selected( $wanted5games['category'], 'skill' ); ?> ><?php esc_html_e( 'Skill', 'myarcadeplugin' ); ?></option>
							<option value="sports" <?php myarcade_selected( $wanted5games['category'], 'sports' ); ?> ><?php esc_html_e( 'Sports', 'myarcadeplugin' ); ?></option>
							<option value="strategy" <?php myarcade_selected( $wanted5games['category'], 'strategy' ); ?> ><?php esc_html_e( 'Strategy', 'myarcadeplugin' ); ?></option>
							<option value="sudoku" <?php myarcade_selected( $wanted5games['category'], 'sudoku' ); ?> ><?php esc_html_e( 'Sudoku', 'myarcadeplugin' ); ?></option>
							<option value="time-management" <?php myarcade_selected( $wanted5games['category'], 'time-management' ); ?> ><?php esc_html_e( 'Time Management', 'myarcadeplugin' ); ?></option>
							<option value="tower-defense" <?php myarcade_selected( $wanted5games['category'], 'tower-defense' ); ?> ><?php esc_html_e( 'Tower Defense', 'myarcadeplugin' ); ?></option>
						</select>
					</td>
					<td><i><?php esc_html_e( 'Select which games you would like to fetch.', 'myarcadeplugin' ); ?></i></td>
				</tr>

				<tr><td colspan="2"><h3><?php esc_html_e( 'Audience', 'myarcadeplugin' ); ?></h3></td></tr>

				<tr>
					<td>
						<select size="1" name="wanted5games_audience" id="wanted5games_audience">
							<option value="all" <?php myarcade_selected( $wanted5games['audience'], 'all' ); ?> ><?php esc_html_e( 'All', 'myarcadeplugin' ); ?></option>
							<option value="male" <?php myarcade_selected( $wanted5games['audience'], 'male' ); ?> ><?php esc_html_e( 'Male', 'myarcadeplugin' ); ?></option>
							<option value="female" <?php myarcade_selected( $wanted5games['audience'], 'female' ); ?> ><?php esc_html_e( 'Female', 'myarcadeplugin' ); ?></option>
							<option value="boys" <?php myarcade_selected( $wanted5games['audience'], 'boys' ); ?> ><?php esc_html_e( 'Boys', 'myarcadeplugin' ); ?></option>
							<option value="girls" <?php myarcade_selected( $wanted5games['audience'], 'girls' ); ?> ><?php esc_html_e( 'Girls', 'myarcadeplugin' ); ?></option>
						</select>
					</td>
					<td><i><?php esc_html_e( 'Select the audience the games should match.', 'myarcadeplugin' ); ?></i></td>
				</tr>

				<tr><td colspan="2"><h3><?php esc_html_e( 'Automated Game Fetching', 'myarcadeplugin' ); ?></h3></td></tr>

				<tr>
					<td>
						<input type="checkbox" name="wanted5games_cron_fetch" value="true" <?php myarcade_checked( $wanted5games['cron_fetch'], true ); ?> /><label class="opt">&nbsp;<?php esc_html_e( 'Yes', 'myarcadeplugin' ); ?></label>
					</td>
					<td><i><?php esc_html_e( "Enable this if you want to fetch games automatically. Go to 'General Settings' to select a cron interval.", 'myarcadeplugin' ); ?></i></td>
				</tr>

				<tr><td colspan="2"><h4><?php esc_html_e( 'Fetch Games', 'myarcadeplugin' ); ?></h4></td></tr>

				<tr>
					<td>
						<input type="number" min="1" name="wanted5games_cron_fetch_limit" value="<?php echo esc_attr( $wanted5games['cron_fetch_limit'] ); ?>" />
					</td>
					<td><i><?php esc_html_e( 'How many games should be fetched on every cron trigger?', 'myarcadeplugin' ); ?></i></td>
				</tr>

				<tr><td colspan="2"><h3><?php esc_html_e( 'Automated Game Publishing', 'myarcadeplugin' ); ?></h3></td></tr>

				<tr>
					<td>
						<input type="checkbox" name="wanted5games_cron_publish" value="true" <?php myarcade_checked( $wanted5games['cron_publish'], true ); ?> /><label class="opt">&nbsp;<?php esc_html_e( 'Yes', 'myarcadeplugin' ); ?></label>
					</td>
					<td><i><?php esc_html_e( "Enable this if you want to publish games automatically. Go to 'General Settings' to select a cron interval.", 'myarcadeplugin' ); ?></i></td>
				</tr>

				<tr><td colspan="2"><h4><?php esc_html_e( 'Publish Games', 'myarcadeplugin' ); ?></h4></td></tr>

				<tr>
					<td>
						<input type="number" min="1" name="wanted5games_cron_publish_limit" value="<?php echo esc_attr( $wanted5games['cron_publish_limit'] ); ?>" />
					</td>
					<td><i><?php esc_html_e( 'How many games should be published on every cron trigger?', 'myarcadeplugin' ); ?></i></td>
				</tr>

			</table>
			<input class="button button-primary" id="submit" type="submit" name="submit" value="<?php esc_html_e( 'Save Settings', 'myarcadeplugin' ); ?>" />
		</div>
	</div>
	<?php
}

/**
 * Load default distributor settings
 *
 * @return  array Default settings
 */
function myarcade_default_settings_wanted5games() {
	return array(
		'feed'               => 'https://portal.wanted5games.com/api/games',
		'limit'              => 100,
		'audience'           => 'all',
		'category'           => 'all',
		'publisher_id'       => '590',
		'cron_fetch'         => false,
		'cron_fetch_limit'   => '1',
		'cron_publish'       => false,
		'cron_publish_limit' => '1',
	);
}

/**
 * Save options
 *
 * @return void
 */
function myarcade_save_settings_wanted5games() {

	myarcade_check_settings_nonce();

	$settings                       = array();
	$settings['feed']               = esc_sql( filter_input( INPUT_POST, 'wanted5games_url' ) );
	$settings['publisher_id']       = esc_sql( filter_input( INPUT_POST, 'wanted5games_publisher_id' ) );
	$settings['category']           = esc_sql( filter_input( INPUT_POST, 'wanted5games_category' ) );
	$settings['audience']           = esc_sql( filter_input( INPUT_POST, 'wanted5games_audience' ) );
	$settings['cron_fetch']         = filter_input( INPUT_POST, 'wanted5games_cron_fetch', FILTER_VALIDATE_BOOLEAN );
	$settings['cron_fetch_limit']   = filter_input( INPUT_POST, 'wanted5games_cron_fetch_limit', FILTER_VALIDATE_INT, array( 'options' => array( 'default' => 1 ) ) );
	$settings['cron_publish']       = filter_input( INPUT_POST, 'wanted5games_cron_publish', FILTER_VALIDATE_BOOLEAN );
	$settings['cron_publish_limit'] = filter_input( INPUT_POST, 'wanted5games_cron_publish_limit', FILTER_VALIDATE_INT, array( 'options' => array( 'default' => 1 ) ) );

	// Update settings.
	update_option( 'myarcade_wanted5games', $settings );
}

/**
 * Retrieve available distributor's categories mapped to MyArcadePlugin categories
 *
 * @return  array Distributor categories
 */
function myarcade_get_categories_wanted5games() {
	return array(
		'Action'      => 'action,bubble-shooter',
		'Adventure'   => true,
		'Arcade'      => true,
		'Board Game'  => 'board-and-card,mahjong,match-3,sudoku,bingo,quiz',
		'Casino'      => true,
		'Defense'     => false,
		'Customize'   => false,
		'Dress-Up'    => true,
		'Driving'     => 'racing',
		'Education'   => 'educational',
		'Fighting'    => false,
		'Jigsaw'      => false,
		'Multiplayer' => false,
		'Other'       => 'cooking,animals,kissing,skill',
		'Puzzles'     => false,
		'Rhythm'      => false,
		'Shooting'    => true,
		'Sports'      => true,
		'Strategy'    => 'tower-defense,platform,time-management',
	);
}

/**
 * Generate an options array with submitted fetching parameters.
 *
 * @return array Fetching options.
 */
function myarcade_get_fetch_options_wanted5games() {

	// Get distributor settings.
	$settings = MyArcade()->get_settings( 'wanted5games' );
	$defaults = myarcade_default_settings_wanted5games();
	$settings = wp_parse_args( $settings, $defaults );

	$settings['method'] = 'latest';
	$settings['offset'] = 1;

	if ( 'start' === filter_input( INPUT_POST, 'fetch' ) ) {
		// Set submitted fetching options.
		$settings['limit']  = filter_input( INPUT_POST, 'limitwanted5games' );
		$settings['method'] = filter_input( INPUT_POST, 'fetchmethodwanted5games', FILTER_UNSAFE_RAW, array( 'options' => array( 'default' => 'latest' ) ) );
		$settings['offset'] = filter_input( INPUT_POST, 'offsetwanted5games', FILTER_UNSAFE_RAW, array( 'options' => array( 'default' => '0' ) ) );
	}

	return $settings;
}

/**
 * Display distributor fetch games options.
 */
function myarcade_fetch_settings_wanted5games() {
  ?>
  <div class="myarcade_border white hide mabp_680" id="gamearter">
    <?php myarcade_premium_message(); ?>
  </div>
  <?php
}

/**
 * Fetch games.
 *
 * @param array $args Fetching parameters.
 */
function myarcade_feed_wanted5games( $args = array() ) {
	?>
	<div class="myarcade_border white mabp_680">
	<?php myarcade_premium_message(); ?>
	</div>
	<?php
}

/**
 * Return game embed method.
 *
 * @return string Embed Method.
 */
function myarcade_embedtype_wanted5games() {
	return 'iframe';
}

/**
 * Return if games can be downloaded by this distirbutor.
 *
 * @return bool True if games can be downloaded.
 */
function myarcade_can_download_wanted5games() {
	return false;
}
