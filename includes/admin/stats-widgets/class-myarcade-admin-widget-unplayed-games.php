<?php
/**
 * Display the summary stats
 */

class MyArcade_Admin_Widget_Unplayed_Games {

  public static function output() {

    $games = MyArcade_Stats::get_unplayed_games();
    ?>
    <table width="100%" class="widefat myarcade-table-stats" id="myarcade-unplayed-games">
      <tbody>
        <tr>
          <th>
            <?php _e( 'Game', 'myarcadeplugin' ); ?>
          </th>
          <th class="th-center">
            <?php _e( 'Publish Date', 'myarcadeplugin' ); ?>
          </th>
        </tr>
        <?php
        if ( $games->have_posts() ) {
          global $post;
          while ( $games->have_posts() ) {
            $games->the_post();
            echo '<tr>';
            echo '<th><a href="'.get_permalink().'" target="_blank">'.get_the_title().'</a></th>';
            echo '<th class="th-center">'.get_the_date().'</th>';
            echo '</tr>';
          }
        }
        ?>
      </tbody>
    </table>
    <?php
  }
}

MyArcade_Admin_Widget_Unplayed_Games::output();