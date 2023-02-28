<?php
/**
 * Display the summary stats
 */

class MyArcade_Admin_Widget_Summary {

  public static function output() {
    ?>
    <table width="100%" class="widefat myarcade-table-stats" id="myarcade-summary-stats">
      <tbody>
        <tr>
          <th width="80%"></th>
          <th class="th-center">
            <?php _e( 'Plays', 'myarcadeplugin' ); ?>
          </th>
        </tr>
        <tr>
          <th><?php _e( 'Today', 'myarcadeplugin' ); ?></th>
          <th class="th-center"><span><?php echo MyArcade_Stats::get_plays( 'today' ); ?></span></th>
        </tr>
        <tr>
          <th><?php _e( 'Yesterday', 'myarcadeplugin' ); ?></th>
          <th class="th-center"><span><?php echo MyArcade_Stats::get_plays( 'yesterday' ); ?></span></th>
        </tr>
        <tr>
          <th><?php _e( 'Last 7 Days', 'myarcadeplugin' ); ?></th>
          <th class="th-center"><span><?php echo MyArcade_Stats::get_plays( 'week' ); ?></span></th>
        </tr>
        <tr>
          <th><?php _e( 'Last 30 Days', 'myarcadeplugin' ); ?></th>
          <th class="th-center"><span><?php echo MyArcade_Stats::get_plays( 'month' ); ?></span></th>
        </tr>
        <tr>
          <th><?php _e( 'Total', 'myarcadeplugin' ); ?></th>
          <th class="th-center"><span><?php echo MyArcade_Stats::get_plays( 'total' ); ?></span></th>
        </tr>
      </tbody>
    </table>
    <?php
  }
}

MyArcade_Admin_Widget_Summary::output();