<?php
/**
 * Display the summary stats
 */

class MyArcade_Admin_Widget_Latest_Plays {

  public static function output() {

    $game_plays = MyArcade_Stats::get_latest_plays();
    ?>
    <table width="100%" class="widefat myarcade-table-stats" id="myarcade-latest-plays">
      <tbody>
        <tr>
          <th>
            <?php _e( 'Game', 'myarcadeplugin' ); ?>
          </th>
          <th class="th-center">
            <?php _e( 'User', 'myarcadeplugin' ); ?>
          </th>
          <th class="th-center">
            <?php _e( 'Played ago', 'myarcadeplugin' ); ?>
          </th>
          <th class="th-center">
            <?php _e( 'Duration', 'myarcadeplugin' ); ?>
          </th>
        </tr>
        <?php
        if ( $game_plays ) {
          $current_time = time() + get_option( 'gmt_offset' ) * 60 * 60;

          foreach ( $game_plays as $play ) {
            $user_name = __( "Guest", 'myarcadeplugin' );

            if ( $play->user_id ) {
              $user_data = get_userdata( $play->user_id );
              if ( $user_data ) {
                $user_name = $user_data->user_nicename;
              }
            }

            if ( $play->duration > 60 ) {
              $duration = gmdate( "H:i:s", $play->duration );
            }
            else {
              $duration = sprintf( __( "%s seconds", 'myarcadeplugin' ), $play->duration );
            }

            echo '<tr>';
            echo '<td><a href="'.get_permalink( $play->post_id ).'" target="_blank">'.get_the_title( $play->post_id ).'</td>';
            echo '<td class="th-center">'.$user_name.'</td>';
            echo '<td class="th-center">'.human_time_diff( strtotime( $play->date ), $current_time ).'</td>';
            echo '<td class="th-center">'.$duration.'</td>';
            echo '</tr>';
          }
        }
        else {
          echo '<tr><td colspan="4">'.__( "No games found", 'myarcadeplugin').'</td></tr>';
        }
        ?>
      </tbody>
    </table>
    <?php
  }
}

MyArcade_Admin_Widget_Latest_Plays::output();