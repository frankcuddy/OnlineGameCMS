<?php
/**
 * Displays the manage scores page on backend.
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 */

// No direct access.
if( !defined( 'ABSPATH' ) ) {
  die();
}

/**
 * Manage Scores.
 */
function myarcade_manage_scores() {
  global $wpdb;

  myarcade_header();

	// Begin Pagination.
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}myarcadescores" );

  if ( $count ) {
		// This is the number of results displayed per page.
    $page_rows = 50;

		// This tells us the page number of our last page.
    $last = ceil($count / $page_rows);

		// This makes sure the page number isn't below one, or more than our maximum pages.
		$pagenum = filter_input( INPUT_GET, 'pagenum', FILTER_VALIDATE_INT, array( 'options' => array( 'default' => 1, 'min_range' => 1 ) ) );

   if ( $pagenum > $last )  {
      $pagenum = $last;
    }

		// This sets the range to display in our query.
		$limit_start = ($pagenum - 1) * $page_rows;

		// Calculate counts for next and previous.
		if ( $pagenum !== $last ) {
      $next = $pagenum + 1;
    }

    if ($pagenum > 1) {
      $previous = $pagenum - 1;
    }

		// Generate from .. to.
    $from = 1 + ($pagenum - 1) * $page_rows;

    if ($pagenum < $last) {
      $to = $from + $page_rows - 1;
		} else {
      $to = $count;
    }

    $from_to = $from.' - '.$to;
		// End Paginagion.

		$scores = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}myarcadescores ORDER by id DESC limit %d, %d", $limit_start, $page_rows ) );
    ?>
		<h2><?php esc_html_e( 'Manage Scores', 'myarcadeplugin' ); ?></h2>
    <br />
    <?php myarcade_premium_message(); ?>
    <!-- Print pagination -->
    <div class="tablenav" style="float: left;">
      <div class="tablenav-pages">
				<span class="displaying-num"><?php printf( esc_html__( 'Displaying %s of %s', 'myarcadeplugin' ), $from_to, $count ); ?></span>
        <?php if ($pagenum > 1) : ?>
					<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF']; ?>?page=myarcade-manage-scores&pagenum=1'><?php esc_html_e( 'First', 'myarcadetheme' ); ?></a>
					<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF']; ?>?page=myarcade-manage-scores&pagenum=<?php echo esc_attr( $previous ); ?>'><?php esc_html_e( 'Previous', 'myacadetheme' ); ?></a>
        <?php endif; ?>
				<span class='page-numbers current'><?php echo esc_html( $pagenum ); ?></span>
				<?php if ( $pagenum !== $last ) : ?>
					<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF']; ?>?page=myarcade-manage-scores&pagenum=<?php echo esc_attr( $next ); ?>'><?php esc_html_e( 'Next', 'myarcadeplugin' ); ?></a>
					<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF']; ?>?page=myarcade-manage-scores&pagenum=<?php echo esc_attr( $last ); ?>'><?php esc_html_e( 'Last', 'myarcadeplugin' ); ?></a>
        <?php endif; ?>
      </div>
    </div>

    <table class="widefat fixed">
      <thead>
      <tr>
				<th scope="col" width="100"><?php esc_html_e( 'Image', 'myarcadeplugin' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Game', 'myarcadeplugin' ); ?></th>
				<th scope="col"><?php esc_html_e( 'User', 'myarcadeplugin' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Date', 'myarcadeplugin' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Score', 'myarcadeplugin' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Action', 'myarcadeplugin' ); ?></th>
      </tr>
      </thead>
      <tbody>
          <?php
				foreach ( $scores as $score ) :
          $user = get_user_by('id', $score->user_id);

					$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'mabp_game_tag' AND meta_value = %s", $score->game_tag ) );

          if (! $post_id ) {
            ?>
						<tr id="scorerow_<?php echo esc_attr( $score->id ); ?>">
              <td colspan="6">
                <p class="mabp_error">
									<?php
										printf( esc_html__( 'No WordPress post found for this score. Score ID: %s', 'myarcadeplugin'), $score->id );
									?>
                </p>
              </td>
            </tr>
            <?php
            continue;
          }

					$edit_url = MyArcade()->plugin_url() . '/core/editscore.php?scoreid=' . $score->id;
					$delete   = "<button class=\"button-secondary\" onclick = \"jQuery('#score_{$score->id}\').html('<div class=\'gload\'> </div>');jQuery.post('" . admin_url( 'admin-ajax.php' ) . "',{action:'myarcade_handler',gameid: false, scoreid: '{$score->id}',func:'delete_score'},function(){jQuery('#scorerow_{$score->id}').fadeOut('slow');});\" >" . esc_html__( 'Delete', 'myarcadeplugin' ) . '</button>';
          ?>
					<tr id="scorerow_<?php echo esc_attr( $score->id ); ?>">
						<td><img src="<?php echo esc_url( get_post_meta( $post_id, 'mabp_thumbnail_url', true ) ); ?>" width="50" height="50" alt="" /></td>
						<td><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" title="" target="_blank"><?php echo get_the_title( $post_id ); ?></a></td>
						<td><?php echo esc_html( $user->display_name ); ?></td>
						<td><?php echo esc_html( $score->date ); ?></td>
						<td id="scoreval_<?php echo esc_attr( $score->id ); ?>"><?php echo esc_html( $score->score ); ?></td>
						<td>
							<a href="<?php echo esc_url( $edit_url ); ?>&keepThis=true&TB_iframe=true&height=300&width=500" class="button-secondary thickbox edit" title="<?php echo esc_html__( 'Edit Score', 'myarcadeplugin' ); ?>"><?php echo esc_html__( 'Edit', 'myarcadeplugin' ); ?></a>

							<button class="button-secondary" onclick="jQuery('#score_<?php echo esc_attr($score->id); ?>').html('<div class=\'gload\'> </div>');jQuery.post('<?php echo admin_url( 'admin-ajax.php' ); ?>',{action:'myarcade_handler',gameid: false, scoreid: '<?php echo esc_attr( $score->id ); ?>',func:'delete_score'},function(){jQuery('#scorerow_<?php echo esc_attr( $score->id ); ?>').fadeOut('slow');});"><?php echo esc_html__( 'Delete', 'myarcadeplugin' ); ?></button>


							<span id="score_<?php echo esc_attr( $score->id ); ?>"></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php
	} else {
		echo '<p>' . esc_html__( 'No scores available', 'myarcadeplugin' ) . '</p>';
  }

  myarcade_footer();
}
