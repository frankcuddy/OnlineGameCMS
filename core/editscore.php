<?php
/**
 * Score Editing Module
 *
 * @package MyArcadePlugin/Scores
 */

// Locate WordPress root folder.
$root = dirname( dirname( dirname( dirname( dirname(__FILE__)))));

if ( file_exists($root . '/wp-load.php') ) {
  define('MYARCADE_DOING_ACTION', true);
	require_once $root . '/wp-load.php';
} else {
	// WordPress not found.
  die();
}

// Check user privilege.
if ( function_exists('current_user_can') && !current_user_can('manage_options') ) {
  die();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<title><?php esc_html_e( 'Edit Game', 'myarcadeplugin' ); ?></title>

<link rel='stylesheet' href='<?php bloginfo('url'); ?>/wp-admin/css/wp-admin.css' type='text/css' />
<link rel='stylesheet' href='<?php bloginfo('url'); ?>/wp-admin/css/colors-fresh.css' type='text/css' />
<link rel='stylesheet' href='<?php echo MYARCADE_URL; ?>/assets/css/myarcadeplugin.css' type='text/css' />
'
<script type="text/javascript" src="<?php echo get_option( 'siteurl' ) . '/' . WPINC . '/js/jquery/jquery.js'; ?>"></script>

</head>
<body>
  <div class="wrap">
    <div class="edit-score">
      <?php
      global $wpdb;

			$submit = filter_input( INPUT_POST, 'submit' );

			if ( $submit ) {
				$scoreid = filter_input( INPUT_POST, 'scoreid' );
				$score   = filter_input( INPUT_POST, 'score' );

				// get score.
        $old_score = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}myarcadescores WHERE id = %d", $scoreid ) );
				// Get highscore.
				$highscore = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}myarcadehighscores WHERE game_tag = %s AND user_id = %d AND score = %s", $old_score->game_tag, $old_score->user_id, $old_score->score ) );

        if ( $highscore ) {
					// Update highscore.
					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}myarcadehighscores SET score = %s WHERE id = %d", $score, $highscore->id ) );
        }

				$result = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}myarcadescores SET score = %s WHERE id = %d", $score, $scoreid ) );

        if ($result) {
					echo '<div id="message" class="updated fade">' . esc_attr__( 'Score has been updated!', 'myarcadeplugin' ) . '</div>';
          ?>
          <script type="text/javascript">
            jQuery(document).ready(function() {
							jQuery("td#scoreval_<?php echo esc_attr( $scoreid ); ?>", top.document).html("<?php echo esc_attr( $score ); ?>");
            });
          </script>
          <?php
				} else {
					echo '<div id="message" class="myerror fade">' . esc_attr__( "Can't update score!", 'myarcadeplugin' ) . '</div>';
        }
      } else {
				$scoreid = intval( filter_input( INPUT_GET, 'scoreid', FILTER_VALIDATE_INT ) );

				if ( ! $scoreid ) {
					wp_die( esc_attr__( 'Unknown score ID', 'myarcadeplugin' ) );
        }
      }

			$score = $wpdb->get_var( $wpdb->prepare( "SELECT score FROM {$wpdb->prefix}myarcadescores WHERE id = %d", $scoreid ) );

      if (!$score) {
				wp_die( esc_attr__( 'No score found', 'myarcadetheme' ) );
      }
      ?>
      <form method="post" name="formeditscore" id="formeditscore">
				<input type="hidden" name="scoreid" id="scoreid" value="<?php echo esc_attr( $scoreid ); ?>" />
        <br />
        <div class="container">
          <div class="block">
            <table class="optiontable">
              <tr>
                <td>Score</td>
								<td><input type="text" name="score" id="score" value="<?php echo esc_attr( $score ); ?>" /></td>
								<td><input class="button-secondary" id="submit" type="submit" name="submit" value="<?php esc_attr_e( 'Save Changes', 'myarcadeplugin' ); ?>" /></td>
              </tr>
            </table>
          </div>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
