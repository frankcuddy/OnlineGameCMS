<?php
/**
 * Displays the MyArcade Dashboard page on backend.
 *
 * @package MyArcadePlugin/Admin/Dashboard
 */

// No direct access.
if( !defined( 'ABSPATH' ) ) {
  die();
}

/**
 * Dashboard.
 */
function myarcade_show_stats() {
  global $wpdb;

  myarcade_header();
  ?>
  <div id="icon-index" class="icon32"><br /></div>
	<h2><?php esc_html_e( 'Dashboard', 'myarcadeplugin' ); ?></h2>

  <?php
	if ( 'unknown' === get_option( 'myarcade_allow_tracking' ) ) {
    myarcade_tracking_message();
  }

	// Get Settings.
  $general = get_option('myarcade_general');

	$unpublished_games = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}myarcadegames WHERE status = 'new'" ) );

  // Get published posts.
	$total_posts = wp_count_posts( MyArcade()->get_post_type() );
  ?>
  <div class="dash-left metabox-holder">
    <div class="postbox">
      <div class="statsico"></div>
			<h3 class="hndle"><span><?php esc_html_e( 'MyArcadePlugin Info', 'myarcadeplugin' ); ?></span></h3>
      <div class="preloader-container">
        <div class="insider" id="boxy">
          <ul>
						<li><?php esc_html_e( 'Total Live Games / Posts', 'myarcadeplugin' ); ?>: <a href="edit.php?post_status=publish&post_type=<?php echo esc_attr( MyArcade()->get_post_type() ); ?>"><strong><?php echo esc_html( $total_posts->publish ); ?></strong></a></li>
						<li><?php esc_html_e( 'Total Scheduled Games / Posts', 'myarcadeplugin' ); ?>: <a href="edit.php?post_status=pending&post_type=<?php echo esc_attr( MyArcade()->get_post_type() ); ?>"><strong><?php echo esc_html( $total_posts->future ); ?></strong></a></li>
						<li><?php esc_html_e( 'Total Draft Games / Posts', 'myarcadeplugin' ); ?>: <a href="edit.php?post_status=draft&post_type=<?php echo esc_attr( MyArcade()->get_post_type() ); ?>"><strong><?php echo esc_html( $total_posts->draft ); ?></strong></a></li>

            <li>&nbsp;</li>

						<li><?php esc_html_e( 'Unpublished Games', 'myarcadeplugin' ); ?>: <strong><?php echo esc_html( $unpublished_games ); ?></strong></li>
            <li>
              <?php esc_html_e( 'Post Status', 'myarcadeplugin' ); ?>: <strong><?php echo esc_html( $general['status'] ); ?></strong>
              <?php if ( 'future' === $general['status']) : ?>
               , <strong><?php echo esc_html( $general['schedule'] ); ?></strong> <?php esc_html_e( 'minutes schedule', 'myarcadeplugin' ); ?>.
              <?php endif; ?>
            </li>

            <li>&nbsp;</li>

						<li><?php esc_html_e( 'Download Games', 'myarcadeplugin' ); ?>: <strong><?php if ( $general['down_games'] ) { esc_html_e( 'Yes', 'myarcadeplugin' ); } else { esc_html_e( 'No', 'myarcadeplugin' ); } ?></strong></li>
						<li><?php esc_html_e( 'Download Screenshots', 'myarcadeplugin' ); ?>: <strong><?php if ( $general['down_screens'] ) { esc_html_e( 'Yes', 'myarcadeplugin' ); } else { esc_html_e( 'No', 'myarcadeplugin' ); } ?></strong></li>

            <li>&nbsp;</li>

            <li><?php esc_html_e( 'Product Support', 'myarcadeplugin' ); ?>:  <a href="https://myarcadeplugin.com/support/" target="_new"><?php esc_html_e( 'Forum', 'myarcadeplugin' ); ?></a></li>
          </ul>

          <div class="clear"> </div>
        </div>
      </div>
    </div><!-- postbox end -->

  </div><!-- end dash-left -->

  <div class="dash-right metabox-holder">

  <div class="postbox">
    <div class="newsico"></div>
			<h3 class="hndle" id="poststuff"><span><?php esc_html_e( 'Lastest MyArcadePlugin News', 'myarcadeplugin' ); ?></span></h3>
      <div class="preloader-container">
        <div class="insider" id="boxy">
        <?php
					wp_widget_rss_output(
						'https://myarcadeplugin.com/feed',
						array(
							'items'        => 5,
							'show_author'  => 0,
							'show_date'    => 1,
							'show_summary' => 0,
						)
					);
        ?>
        </div> <!-- inside end -->
      </div>
    </div> <!-- postbox end -->

    <div class="postbox">
      <div class="facebookico"></div>
				<h3 class="hndle" id="poststuff"><span><?php esc_html_e( 'Be Our Friend!', 'myarcadeplugin' ); ?></span></h3>
        <div class="preloader-container">
          <div class="insider" id="boxy">
						<p style="text-align:center"><strong><?php esc_html_e( 'If you like MyArcadePlugin, become our friend on Facebook', 'myarcadeplugin' ); ?></strong></p>
            <p style="text-align:center;">
              <iframe src="https://www.facebook.com/plugins/page.php?href=https%3A%2F%2Fwww.facebook.com%2FMyArcadePlugin%2F&tabs&width=500&height=250&small_header=false&adapt_container_width=true&hide_cover=false&show_facepile=true&appId=240110659370021" width="500" height="250" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allow="encrypted-media"></iframe>
            </p>
          </div> <!-- inside end -->
        </div>
      </div> <!-- postbox end -->
  </div><!-- end dash-right -->

  <div class="clear"></div>

  <strong>MyArcadePlugin Lite v<?php echo esc_html( MYARCADE_VERSION ); ?></strong> | <strong><a href="https://myarcadeplugin.com/" target="_blank">MyArcadePlugin.com</a> </strong>
  <?php
  myarcade_footer();
}
