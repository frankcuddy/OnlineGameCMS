<?php
/**
 * Displays the import games page on backend
 *
 * @package MyArcadePlugin/Admin/Game/Import
 */

// No direct access.
if( !defined( 'ABSPATH' ) ) {
  die();
}

/**
 * Import games page.
 */
function myarcade_import_games() {
  global $wpdb;

  myarcade_header();

  $general= get_option( 'myarcade_general' );

	// Crete an empty game class.
  $game = new stdClass();

  $impcostgame   = filter_input( INPUT_POST, 'impcostgame' );
  $importtype    = filter_input( INPUT_POST, 'importtype' );
  $importgame    = filter_input( INPUT_POST, 'importgame' );
  $gamename      = filter_input( INPUT_POST, 'gamename' );
  $importgametag = filter_input( INPUT_POST, 'importgametag' );

	if ( 'import' === $impcostgame ) {
		if ( 'embed' === $importtype || 'iframe' === $importtype || 'html5' === $importtype ) {
      $decoded = urldecode( $importgame );
			$converted     = str_replace( array( "\r\n", "\r", "\n" ), ' ', $decoded );
      $game->swf_url = esc_sql( $converted );
		} else {
      $game->swf_url = $importgame;
    }

    $game->width  = filter_input( INPUT_POST, 'gamewidth' );
    $game->height = filter_input( INPUT_POST, 'gameheight' );
    $game->slug   = filter_input( INPUT_POST, 'slug' );

    if ( ! $game->slug ) {
			$game->slug = preg_replace( '/[^a-zA-Z0-9 ]/', '', strtolower( $gamename ) );
			$game->slug = str_replace( " ", '-', $game->slug );
    }

    $gamecategs = filter_input( INPUT_POST, 'gamecategs', FILTER_DEFAULT, FILTER_FORCE_ARRAY );

    $game->name           = $gamename;
    $game->type           = $importtype;
    $game->uuid           = md5($game->name.'import');
    $game->game_tag       = ( $importgametag ) ? $importgametag : crc32( $game->uuid );
    $game->thumbnail_url  = filter_input( INPUT_POST, 'importthumb' );
    $game->description    = filter_input( INPUT_POST, 'gamedescr' );
    $game->instructions   = filter_input( INPUT_POST, 'gameinstr' );
    $game->tags           = esc_sql( filter_input( INPUT_POST, 'gametags' ) );
		$game->categs              = ( $gamecategs ) ? implode( ',', $gamecategs ) : 'Other';
    $game->created        = gmdate( 'Y-m-d H:i:s', ( time() + (get_option( 'gmt_offset' ) * 3600 ) ) );
    $game->leaderboard_enabled = filter_input( INPUT_POST, 'lbenabled' );
		$game->highscore_type      = 'DESC';
    $highscoretype        = filter_input( INPUT_POST, 'highscoretype' );

		if ( 'low' === $highscoretype ) {
      $game->highscore_type = 'ASC';
    }

    $game->status       = 'new';
    $game->screen1_url  = filter_input( INPUT_POST, 'importscreen1' );
    $game->screen2_url  = filter_input( INPUT_POST, 'importscreen2' );
    $game->screen3_url  = filter_input( INPUT_POST, 'importscreen3' );
    $game->screen4_url  = filter_input( INPUT_POST, 'importscreen4' );
    $game->video_url    = filter_input( INPUT_POST, 'video_url' );
    $game->score_bridge = filter_input( INPUT_POST, 'score_bridge' );

		// Add game to table.
    myarcade_insert_game($game);

    $publishstatus = filter_input( INPUT_POST, 'publishstatus' );

		// Add the game as blog post.
		if ( 'add' !== $publishstatus ) {
			$gameID = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}myarcadegames WHERE uuid = %s", $game->uuid ) );

      if ( !empty($gameID) ) {
				myarcade_add_games_to_blog(
					array(
						'game_id'     => $gameID,
						'post_status' => $publishstatus,
						'echo'        => false,
					)
				);

				echo '<div class="mabp_info mabp_680"><p>' . sprintf( esc_html__( "Import of '%s' was succsessful.", 'myarcadeplugin' ), $game->name ) . '</p></div>';
			} else {
				echo '<div class="mabp_error mabp_680"><p>' . esc_html__( "Can't import that game...", 'myarcadeplugin' ) . '</p></div>';
      }
		} else {
			echo '<div class="mabp_info mabp_680"><p>'. sprintf( esc_html__( "Game added successfully: %s", 'myarcadeplugin' ), $game->name ) . '</p></div>';
    }
  }

	// Generate the category array.
	if ( 'post' !== MyArcade()->get_post_type() && ! empty( $general['custom_category'] ) && taxonomy_exists( $general['custom_category'] ) ) {
    $taxonomy = $general['custom_category'];
	} else {
    $taxonomy = 'category';
  }

  $categories = get_terms( $taxonomy, array('hide_empty' => false) );
	$selected_method = filter_input( INPUT_POST, 'importmethod', FILTER_UNSAFE_RAW, array( 'options' => array( 'default' => 'importembedif' ) ) );
  ?>

  <div id="myabp_import">
		<h2><?php esc_html_e( 'Import Individual Games', 'myarcadeplugin' ); ?></h2>

    <div class="container">
      <div class="block">
        <table class="optiontable" width="100%">
          <tr>
						<td><h3><?php esc_html_e( 'Import Method', 'myarcadeplugin' ); ?></h3></td>
          </tr>
          <tr>
            <td>
              <select size="1" name="importmethod" id="importmethod">
                <option value="importswfdcr" <?php selected( 'importswfdcr', $selected_method ); ?>><?php esc_html_e( 'Flash or DCR game (.swf, .dcr)', 'myarcadeplugin' ); ?>&nbsp;</option>
                <option value="importembedif" <?php selected( 'importembedif', $selected_method ); ?>><?php esc_html_e( 'Embed / Iframe game', 'myarcadeplugin' ); ?></option>
                <option value="importhtml5" <?php selected( 'importhtml5', $selected_method ); ?>><?php esc_html_e( 'HTML5 game (.zip) (PRO)', 'myarcadeplugin' ); ?>&nbsp;</option>
                <option value="importibparcade" <?php selected(  'importibparcade', $selected_method ); ?>><?php esc_html_e( 'IBPArcade game (.tar) (PRO)', 'myarcadeplugin' ); ?></option>
                <option value="importphpbb" <?php selected( 'importphpbb', $selected_method ); ?>><?php esc_html_e( 'PHPBB game (.zip) (PRO)', 'myarcadeplugin' ); ?></option>
                <option value="importunity" <?php selected( 'importunity', $selected_method ); ?>><?php esc_html_e( 'Unity3D game (.unity3d) (PRO)', 'myarcadeplugin'); ?></option>
              </select>
              <br />
							<i><?php esc_html_e( 'Choose a desired import method.', 'myarcadeplugin' ); ?></i>
            </td>
          </tr>
        </table>
      </div>
    </div>

		<?php
		myarcade_get_max_post_size_message();

		require_once 'import_form.php';
		?>
	</div>
  <div class="clear"></div>
  <?php
  myarcade_footer();
}
