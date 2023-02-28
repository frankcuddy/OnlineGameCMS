<?php
/**
 * Display the top users ordered by number of plays
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

class MyArcade_Admin_Widget_Top_Users {

  public static function output() {

    $users = MyArcade_Stats::get_top_users();
    ?>
    <table width="100%" class="widefat myarcade-table-stats" id="myarcade-top-users">
      <tbody>
        <tr>
          <th>
            <?php _e( 'User', 'myarcadeplugin' ); ?>
          </th>
          <th class="th-center">
            <?php _e( 'Plays', 'myarcadeplugin' ); ?>
          </th>
        </tr>
        <?php
        if ( $users ) {
          foreach ( $users as $user ) {
            $user_data = get_userdata( $user->user_id );
            if ( $user_data ) {
              $user_name = $user_data->user_nicename;
            }
            else {
              $user_name = __( "Unknown", 'myarcadeplugin' );
            }

            echo '<tr>';
            echo '<th>'.$user_name.'</th>';
            echo '<th class="th-center"><span>'.$user->plays.'</span></th>';
            echo '</tr>';
          }
        }
        ?>
      </tbody>
    </table>
    <?php
  }
}

MyArcade_Admin_Widget_Top_Users::output();