<?php
/**
 * Display the stats page on backend
 */

// No direct access
if( !defined( 'ABSPATH' ) ) {
  die();
}

/**
 * Display the statistics page on backend
 *
 * @version 5.30.0
 * @return  void
 */
function myarcade_stats() {
  ?>
  <div class="wrap myarcade">
    <h2><?php _e("Statistics", 'myarcadeplugin'); ?></h2>
    <br />

    <?php
    if ( "yes" == get_option( 'myarcade_allow_tracking' ) ) {
      // Load the reports output
      MyArcade_Admin_Reports::output();

      // Display optout message
      ?>
      <div class="myarcade_message myarcade_notice">
        <?php printf( __( '%sDisable site stats%s and relinquish the benefits.', 'myarcadeplugin' ), '<a href="'.esc_url( wp_nonce_url( add_query_arg( "myarcade_tracker_optout", "true" ), "myarcade_tracker_optout", "myarcade_tracker_nonce" ) ).'">', '</a>' ); ?>
      </div>
      <?php
    }
    else {
      myarcade_tracking_message(false);
    }
    ?>
  </div>
  <?php
}