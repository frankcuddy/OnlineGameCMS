<?php
/**
 * Display the top games ordered by number of plays
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

class MyArcade_Admin_Widget_Top_Games {

  public static function output() {

    $games = MyArcade_Stats::get_top_games();
    ?>
    <table width="100%" class="widefat myarcade-table-stats" id="myarcade-top-games">
      <tbody>
        <tr>
          <th width="80%">
            <?php _e( 'Game', 'myarcadeplugin' ); ?>
          </th>
          <th class="th-center">
            <?php _e( 'Plays', 'myarcadeplugin' ); ?>
          </th>
        </tr>
        <?php
        if ( $games->have_posts() ) {
          global $post;
          while ( $games->have_posts() ) {
            $games->the_post();
            echo '<tr>';
            echo '<th><a href="'.get_permalink().'" target="_blank">'.get_the_title().'</a></th>';
            echo '<th class="th-center"><span>'.get_post_meta($post->ID, "myarcade_plays", true).'</span></th>';
            echo '</tr>';
          }
        }
        ?>
      </tbody>
    </table>
    <?php
  }
}

MyArcade_Admin_Widget_Top_Games::output();