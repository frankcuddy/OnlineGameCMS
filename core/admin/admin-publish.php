<?php
/**
 * Displays the publish games page on backend
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 */

// No direct Access
if ( ! defined( 'ABSPATH' ) ) {
  die();
}

/**
 * Publish Games page
 *
 * @version 5.28.0
 * @return  void
 */
function myarcade_publish_games() {
	global $wpdb;

  myarcade_header();

  $general = get_option('myarcade_general');

  $feedcategories = get_option('myarcade_categories');

  $action = filter_input( INPUT_POST, 'action' );

  // Init some needed vars
  if ( 'publish' == $action ) {
    $game_type        = filter_input( INPUT_POST, 'distr' );
    $leaderboard      = filter_input( INPUT_POST, 'leaderboard' );
    $status           = filter_input( INPUT_POST, 'status' );
    $schedule         = filter_input( INPUT_POST, 'scheduletime', FILTER_VALIDATE_INT, array( "options" => array( "default" => $general['schedule'] ) ) );
    $order            = filter_input( INPUT_POST, 'order' );
    $cat              = filter_input( INPUT_POST, 'category' );
    $posts            = filter_input( INPUT_POST, 'games', FILTER_VALIDATE_INT );
    $download_screens = ( filter_input( INPUT_POST, 'downloadscreens' ) ) ? true : false;
    $download_games   = ( filter_input( INPUT_POST, 'downloadgames' ) ) ? true : false;

    // Generate the query
    $query_array = array();
    $query_string = '';

    $query_array[] = "status = 'new'";

    if ( $game_type != 'all') {
      $query_array[] = "game_type = '".$game_type."'";
    }

    if ( "1" == $leaderboard ) {
      $query_array[] = "leaderboard_enabled = '1'";
    }

    if ( $cat != 'all') {
      $query_array[] = "categories LIKE '%".$feedcategories[ (int) $cat ]['Name']."%'";
    }

    if ( $posts ) {
      $limit = " limit ".$posts;
    }
    else {
      $limit = '';
    }

    $count = count($query_array);

    if ( $count > 1 ) {
      for($i=0; $i < count($query_array); $i++) {
        $query_string .= $query_array[$i];
        if ( $i < ($count - 1) ) {
          $query_string .= ' AND ';
        }
      }
    }
    elseif ($count == 1) {
      $query_string = $query_array[0];
    }

    if ( !empty($query_string) ) {
      $query_string = " WHERE ".$query_string;
    }

    $games = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}myarcadegames {$query_string} ORDER BY id {$order} {$limit}" );

    // Generate a string with all game IDs
    if ( !empty($games) ) {
      foreach( $games as $id ) {
        $ids[] = $id->id;
      }

      $ids = implode(',', $ids);
      $start_publishing = 'yes';
    }
    else {
      $ids = '';
      $start_publishing = 'no';
    }
  }
  else {
    $game_type        = 'all';
    $leaderboard      = '0';
    $status           = $general['status'];
    $schedule         = $general['schedule'];
    $order            = 'ASC';
    $cat              = 'all';
    $posts            = $general['posts'];
    $download_screens = $general['down_screens'];
    $download_games   = $general['down_games'];

    $start_publishing = 'init';
  }

	$distributors = MyArcade()->distributors();
  ?>
  <div id="icon-options-general" class="icon32"><br /></div>
  <h2><?php _e("Publish Games", 'myarcadeplugin'); ?></h2>
  <br />

  <form method="post" action="" class="myarcade_form" name="searchForm">
    <input type="hidden" name="action" value="publish" />
    <div class="myarcade_border grey" style="width:680px">
      <div class="myarcade_border white" style="width:300px;float:left;height:30px;">
        <?php _e("Game Type", 'myarcadeplugin'); ?>:
        <select name="distr" id="distr">
					<option value="all" <?php myarcade_selected($game_type, 'all'); ?>><?php esc_html_e( 'All', 'myarcadeplugin' ); ?></option>
          <optgroup label="Game Distributors">
						<?php foreach ($distributors as $slug => $name) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php myarcade_selected($game_type, $slug); ?>><?php echo esc_html( $name ); ?></option>
            <?php endforeach; ?>
          </optgroup>
          <optgroup label="Imported Games">
						<option value="html5" <?php myarcade_selected($game_type, 'html5'); ?>><?php esc_html_e("HTML5 Games", 'myarcadeplugin');?></option>
						<option value="embed" <?php myarcade_selected($game_type, 'embed'); ?>><?php esc_html_e("Embed Codes", 'myarcadeplugin'); ?></option>
						<option value="iframe" <?php myarcade_selected($game_type, 'iframe'); ?>><?php esc_html_e("Iframe (URL)", 'myarcadeplugin'); ?></option>
						<option value="ibparcade" <?php myarcade_selected($game_type, 'ibparcade'); ?>><?php esc_html_e("IBPArcade Games", 'myarcadeplugin'); ?></option>
						<option value="phpbb" <?php myarcade_selected($game_type, 'phpbb'); ?>><?php esc_html_e("PHPBB Games", 'myarcadeplugin'); ?></option>
						<option value="dcr" <?php myarcade_selected($game_type, 'dcr'); ?>><?php esc_html_e("Shockwave Games (DCR)", 'myarcadeplugin'); ?></option>
						<option value="custom" <?php myarcade_selected($game_type, 'custom'); ?>><?php esc_html_e("Flash Games (SWF)", 'myarcadeplugin'); ?></option>
          </optgroup>
        </select>
      </div>

      <div class="myarcade_border white" style="width:300px;float:left;margin-left:20px;height:30px;padding: 10px 5px 10px 10px;">
        <input type="checkbox" name="leaderboard" value="1" <?php myarcade_checked($leaderboard, '1'); ?> /> <?php _e('Score Games', 'myarcadeplugin'); ?><br />
      </div>

      <div class="clear"> </div>

      <div class="myarcade_border white" style="width:300px;height:30px;float:left;">
        <?php _e("Status", 'myarcadeplugin'); ?>:
        <select name="status" id="status">
          <option value="publish" <?php myarcade_selected($status, 'publish'); ?>><?php _e("Publish", 'myarcadeplugin') ?></option>
          <option value="draft" <?php myarcade_selected($status, 'draft'); ?>><?php _e("Draft", 'myarcadeplugin') ?></option>
          <option value="future" <?php myarcade_selected($status, 'future'); ?>><?php _e("Scheduled", 'myarcadeplugin') ?></option>
        </select>
				<?php esc_html_e( 'time', 'myarcadeplugin' ); ?> <input type="number" name="scheduletime" value="<?php echo esc_attr( $schedule ); ?>" size="3" /> <?php esc_html_e( 'min.', 'myarcadeplugin' ); ?>
      </div>

      <div class="myarcade_border white" style="width:300px;height:30px;float:left;margin-left:20px;">
        <?php _e("Order", 'myarcadeplugin'); ?>:
        <select name="order" id="order">
          <option value="ASC" <?php myarcade_selected($order, 'ASC'); ?>><?php _e("Older Games First (ASC)", 'myarcadeplugin') ?></option>
          <option value="DESC" <?php myarcade_selected($order, 'DESC'); ?>><?php _e("Newer Games First (DESC)", 'myarcadeplugin') ?></option>
        </select>
      </div>

      <div class="clear"> </div>

      <div class="myarcade_border white" style="width:300px;height:30px;float:left;">
        <?php _e("Game Categories", 'myarcadeplugin'); ?>:
        <select name="category" id="category">
          <option value="all" <?php myarcade_selected($cat, 'all'); ?>><?php _e("All Activated", 'myarcadeplugin') ?></option>
          <?php
            for ($x=0; $x<count($feedcategories); $x++) {
              if ( $feedcategories[$x]['Status'] == 'checked' ) {
                ?>
								<option value="<?php echo esc_attr( $x ); ?>" <?php myarcade_selected($cat,  $x); ?>>
									<?php echo esc_html( $feedcategories[$x]['Name'] ); ?>
                </option>
                <?php
              }
            }
          ?>
        </select>
      </div>

      <div class="myarcade_border white" style="width:300px;height:30px;float:left;margin-left:20px;">
        <?php _e("Create", 'myarcadeplugin'); ?>
				<input type="number" name="games" value="<?php echo esc_attr( $posts ); ?>" />
        <?php _e("game posts", 'myarcadeplugin'); ?>
      </div>

      <div class="myarcade_border white" style="width:300px;height:50px;float:left;">
				<input type="checkbox" value="1" id="downloadscreens" name="downloadscreens" <?php myarcade_checked($download_screens, true); ?>/> <?php _e( 'Download Screenshots', 'myarcadeplugin' ); ?><br />
				<input type="checkbox" value="1" id="downloadgames" name="downloadgames" <?php myarcade_checked($download_games, true); ?>/> <?php _e( 'Download Games', 'myarcadeplugin' ); ?>
      </div>

      <div class="clear"> </div>

      <input class="button-primary" id="submit" type="submit" name="submit" value="Create Posts" />
    </div>
  </form>

  <script type="text/javascript">
    function myarcade_check_dir(dir) {
      jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {action:'myarcade_handler', func:'dircheck', directory: dir},
        function(data) {
          jQuery('#down_' + dir).html(data);
        });
    }

    jQuery(document).ready(function($){
      $("#downloadgames").change(function() {
        if ( $('#downloadgames').attr('checked') ) {
          myarcade_check_dir('games');
        } else {
          $('#down_games').html("");
        }
      });
      $("#downloadscreens").change(function() {
        if ( $('#downloadscreens').attr('checked') ) {
          myarcade_check_dir('thumbs');
        } else {
          $('#down_thumbs').html("");
        }
      });
    });
  </script>

  <?php
	$upload_dir = MyArcade()->upload_dir();
  ?>

  <div id="down_thumbs">
    <?php if ( $download_screens && ( ! is_writable( $upload_dir['thumbsdir'] ) ) ) {
      echo '<p class="mabp_error mabp_680">'.sprintf( __("The images directory '%s' must be writeable (chmod 777) in order to download game images.", 'myarcadeplugin'),  $upload_dir['thumbsdir'] ).'</p>';
    }
    ?>
  </div>
  <div id="down_games">
    <?php if ($download_games && ( ! is_writable( $upload_dir['gamesdir'] ) ) ) {
      echo '<p class="mabp_error mabp_680">'.sprintf(__("The games directory '%s' must be writeable (chmod 777) in order to download games.", 'myarcadeplugin'), $upload_dir['gamesdir'] ).'</p>';
    }
    ?>
  </div>

  <?php if ( $start_publishing == 'yes' ) : ?>

  <p class="mabp_info mabp_680">
    <?php _e("Please be patient while games are published. This can take a while if your server is slow or if there are a lot of games. Do not navigate away from this page until MyArcadePlugin is done or the games will not be published.", 'myarcadeplugin'); ?>
  </p>

  <?php
  $text_failures = sprintf('All done! %1$s game(s) where successfully published in %2$s second(s) and there were %3$s failures.', "' + myarcade_successes + '", "' + myarcade_totaltime + '", "' + myarcade_errors + '");
  $text_nofailures = sprintf('All done! %1$s game(s) where successfully published in %2$s second(s) and there were 0 failures.', "' + myarcade_successes + '", "' + myarcade_totaltime + '");
  ?>

  <noscript>
    <p>
      <em>
        <?php _e( 'You must enable Javascript in order to proceed!', 'myarcadeplugin') ?>
      </em>
    </p>
  </noscript>

  <div id="myarcade-bar" style="position:relative;height:25px;width:700px;">
    <div id="myarcade-bar-percent" style="position:absolute;left:50%;top:50%;width:300px;margin-left:-150px;height:25px;margin-top:-9px;font-weight:bold;text-align:center;"></div>
  </div>

  <p><input type="button" class="button hide-if-no-js" name="myarcade-stop" id="myarcade-stop" value="<?php _e( 'Abort Game Publishing', 'myarcadeplugin' ); ?>" /></p>

  <div id="message" class="mabp_info mabp_680" style="display:none"></div>

  <ul id="myarcade-gamelist">
    <li style="display:none"></li>
  </ul>

  <script type="text/javascript">
    // <![CDATA[
    jQuery(document).ready(function($){
      var i;
			var myarcade_games = [<?php echo esc_attr( $ids ); ?>];
      var myarcade_total = myarcade_games.length;
      var myarcade_count = 1;
      var myarcade_percent = 0;
      var myarcade_successes = 0;
      var myarcade_errors = 0;
      var myarcade_failedlist = '';
      var myarcade_resulttext = '';
      var myarcade_timestart = new Date().getTime();
      var myarcade_timeend = 0;
      var myarcade_totaltime = 0;
      var myarcade_continue = true;

      // Create the progress bar
      $("#myarcade-bar").progressbar();
      $("#myarcade-bar-percent").html( "0%" );

      // Stop button
      $("#myarcade-stop").click(function() {
        myarcade_continue = false;
        $('#myarcade-stop').val("<?php _e('Stopping...', 'myarcadeplugin' ); ?>");
      });

      // Clear out the empty list element that's there for HTML validation purposes
      $("#myarcade-gamelist li").remove();

      // Called after each resize. Updates debug information and the progress bar.
      function myarcadeUpdateStatus( id, success, response ) {
        $("#myarcade-bar").progressbar( "value", ( myarcade_count / myarcade_total ) * 100 );
        $("#myarcade-bar-percent").html( Math.round( ( myarcade_count / myarcade_total ) * 1000 ) / 10 + "%" );
        myarcade_count = myarcade_count + 1;

        if ( success ) {
          myarcade_successes = myarcade_successes + 1;
          $("#myarcade-debug-successcount").html(myarcade_successes);
          $("#myarcade-gamelist").prepend("<li>" + response.success + "</li>");
        }
        else {
          myarcade_errors = myarcade_errors + 1;
          myarcade_failedlist = myarcade_failedlist + ',' + id;
          $("#myarcade-debug-failurecount").html(myarcade_errors);
          $("#myarcade-gamelist").prepend("<li>" + response.error + "</li>");
        }
      }

      // Called when all images have been processed. Shows the results and cleans up.
      function myarcadeFinishUp() {
        myarcade_timeend = new Date().getTime();
        myarcade_totaltime = Math.round( ( myarcade_timeend - myarcade_timestart ) / 1000 );

        $('#myarcade-stop').hide();

        if ( myarcade_errors > 0 ) {
					myarcade_resulttext = '<?php echo $text_failures; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>';
        } else {
					myarcade_resulttext = '<?php echo $text_nofailures; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>';
        }

        $("#message").html("<strong>" + myarcade_resulttext + "</strong>");
        $("#message").show();
      }

      // Publish a specified game via AJAX
      function myarcade( id ) {
        $.ajax({
          type: 'POST',
          url: ajaxurl,
          data: { action: "myarcade_ajax_publish",
            id: id,
						status: '<?php echo esc_html( $status ); ?>',
						schedule: '<?php echo esc_attr( $schedule ); ?>',
            count: myarcade_count,
						download_screens: '<?php echo esc_attr( $download_screens ); ?>',
						download_games: '<?php echo esc_attr( $download_games ); ?>'
          },
          success: function( response ) {
            if ( response !== Object( response ) || ( typeof response.success === "undefined" && typeof response.error === "undefined" ) ) {
              response = new Object;
              response.success = false;
              response.error = "<?php printf( esc_js( __( 'Game publishing request was abnormally terminated (ID %s). This is likely due to the game exceeding available memory or some other type of fatal error.', 'myarcadeplugin' ) ), '" + id + "' ); ?>";
            }
            if ( response.success ) {
              myarcadeUpdateStatus( id, true, response );
            }
            else {
              myarcadeUpdateStatus( id, false, response );
            }
            if ( myarcade_games.length && myarcade_continue ) {
              myarcade( myarcade_games.shift() );
            }
            else {
              myarcadeFinishUp();
            }
          },
          error: function( response ) {
            myarcadeUpdateStatus( id, false, response );
            if ( myarcade_games.length && myarcade_continue ) {
              myarcade( myarcade_games.shift() );
            }
            else {
              myarcadeFinishUp();
            }
          }
        });
      }

      myarcade( myarcade_games.shift() );
    });
  // ]]>
  </script>
  <?php elseif ( $start_publishing == 'no') : ?>
  <p class="mabp_info mabp_680">
    <?php _e("No games found for your search criteria!", 'myarcadeplugin'); ?>
  </p>
  <?php endif; ?>

  <?php
  myarcade_footer();
}
?>