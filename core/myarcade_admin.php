<?php
/**
 * Admin functions
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @package MyArcadePlugin/Admin
 */

// No direct access.
if ( !defined( 'ABSPATH' ) ) {
	die();
}

// Include Post Meta functions.
require_once MyArcade()->plugin_path() . '/core/admin/admin-post-meta.php';

/**
 * Register MyArcade menus.
 */
function myarcade_admin_menu() {

	$general = get_option('myarcade_general');

	if ( $general && isset($general['allow_user']) && $general['allow_user'] ) {
		$permisssion = 'edit_posts';
	} else {
		$permisssion = 'manage_options';
	}

	add_menu_page(
		'MyArcade',
		'MyArcade',
		$permisssion,
		basename( __FILE__ ),
		'myarcade_show_stats_page',
		MyArcade()->plugin_url() . '/assets/images/arcade.png'
	);

	add_submenu_page(
		basename( __FILE__ ),
		__( 'Dashboard', 'myarcadeplugin' ),
		__( 'Dashboard', 'myarcadeplugin' ),
		$permisssion,
		basename( __FILE__ ),
		'myarcade_show_stats_page'
	);

	add_submenu_page(
		basename( __FILE__ ),
		__( 'Fetch Games', 'myarcadeplugin' ),
		__( 'Fetch Games', 'myarcadeplugin' ),
		'manage_options',
		'myarcade-fetch',
		'myarcade_fetch_page'
	);

	$hookname = add_submenu_page(
		basename( __FILE__ ),
		__( 'Import Games', 'myarcadeplugin' ),
		__( 'Import Games', 'myarcadeplugin' ),
		$permisssion,
		'myarcade-import-games',
		'myarcade_import_games_page'
	);

	add_action('load-'.$hookname, 'myarcade_import_scripts');

	add_submenu_page(
		basename( __FILE__ ),
		__( 'Publish Games', 'myarcadeplugin' ),
		__( 'Publish Games', 'myarcadeplugin' ),
		'manage_options',
		'myarcade-publish-games',
		'myarcade_publish_games_page'
	);

	add_submenu_page(
		basename( __FILE__ ),
		__( 'Manage Games', 'myarcadeplugin' ),
		__( 'Manage Games', 'myarcadeplugin' ),
		'manage_options',
		'myarcade-manage-games',
		'myarcade_manage_games_page'
	);

	add_submenu_page(
		basename( __FILE__ ),
		__( 'Manage Scores', 'myarcadeplugin' ),
		__( 'Manage Scores', 'myarcadeplugin' ),
		'manage_options',
		'myarcade-manage-scores',
		'myarcade_manage_scores_page'
	);

	add_submenu_page(
		basename(__FILE__),
		__( 'Statistics', 'myarcadeplugin' ),
		__( 'Statistics', 'myarcadeplugin' ),
		'manage_options',
		'myarcade-stats',
		'myarcade_stats_page'
	);

	add_submenu_page(
		basename( __FILE__ ),
		__( 'Settings' ),
		__( 'Settings' ),
		'manage_options',
		'myarcade-edit-settings',
		'myarcade_settings_page'
	);
}
add_action('admin_menu', 'myarcade_admin_menu', 9);

/**
 * Display the bulk game publishing page.
 */
function myarcade_publish_games_page() {
	include_once MyArcade()->plugin_path() . '/core/admin/admin-publish.php';
	myarcade_publish_games();
}

/**
 * Display the fetch games page.
 */
function myarcade_fetch_page() {
	include_once MyArcade()->plugin_path() . '/core/admin/admin-fetch.php';
	myarcade_fetch();
}

/**
 * Display the import games page.
 */
function myarcade_import_games_page() {
	include_once MyArcade()->plugin_path() . '/core/admin/admin-import.php';
	myarcade_import_games();
}

/**
 * Displays the dashboard page.
 */
function myarcade_show_stats_page() {
	include_once MyArcade()->plugin_path() . '/core/admin/admin-dashboard.php';
	myarcade_show_stats();
}

/**
 * Display the settings page.
 */
function myarcade_settings_page() {
	include_once MyArcade()->plugin_path() . '/core/admin/admin-settings.php';
	myarcade_settings();
}

/**
 * Display the manage games page
 */
function myarcade_manage_games_page() {
	include_once MyArcade()->plugin_path() . '/core/admin/admin-manage.php';
	myarcade_manage_games();
}

/**
 * Display manage scores page.
 */
function myarcade_manage_scores_page() {
	include_once MyArcade()->plugin_path() . '/core/admin/admin-manage-scores.php';
	myarcade_manage_scores();
}

/**
 * Display the stats page.
 */
function myarcade_stats_page() {
	include_once MyArcade()->plugin_path() . '/core/admin/admin-stats.php';
	myarcade_stats();
}

/**
 * Load game import scripts.
 */
function myarcade_import_scripts() {
	wp_enqueue_script( 'myarcade-import', MyArcade()->plugin_url() . '/assets/js/import.js', array( 'jquery', 'jquery-form' ), false, true );

	wp_localize_script(
		'myarcade-import',
		'MyArcadeImport',
		array(
			'cannot_import'         => __( 'Error: Can not import that game!', 'myarcadeplugin' ),
			'game_missing'          => __( 'No game file added!', 'myarcadeplugin' ),
			'thumb_missing'         => __( 'No thumbnail added!', 'myarcadeplugin' ),
			'name_missing'          => __( 'Game name not set!', 'myarcadeplugin' ),
			'description_missing'   => __( 'There is no game description!', 'myarcadeplugin' ),
			'category_missing'      => __( 'Select at least one category!', 'myarcadeplugin' ),
			'max_filesize_exceeded' => __( 'ERROR: Max allowed file size exceeded!', 'myarcadeplugin' ),
			'error_string'          => __( 'Error:', 'myarcadeplugin' ),
		'rich_editing'  => get_user_option('rich_editing'),
		'allowed_size'  => myarcade_get_max_post_size_bytes(),
		)
	);
}

/**
 * Load required scripts.
 */
function myarcade_admin_scripts() {
	global $pagenow;

	$screen = get_current_screen();

	if ( 'post.php' === $pagenow ) {
		wp_register_script( 'myarcade_writepanel', MyArcade()->plugin_url() . '/assets/js/writepanel.js', array( 'jquery' ), false, true );
		wp_enqueue_script('myarcade_writepanel');
	}

	if ( 'admin.php' === $pagenow ) {
		switch ( $screen->id ) {
			case 'myarcade_page_myarcade-publish-games':
				wp_enqueue_script( 'jquery-ui-progressbar', MyArcade()->plugin_url() . '/assets/js/jquery.ui.progressbar.min.js', array( 'jquery-ui-core', 'jquery-ui-widget' ), '1.8.6', true );
				wp_enqueue_style( 'jquery-ui-myarcadeplugin', MyArcade()->plugin_url() . '/assets/css/jquery-ui.css' );
				break;

			case 'myarcade_page_myarcade-fetch':
				wp_enqueue_script( 'myarcadeplugin-script', MyArcade()->plugin_url() . '/assets/js/myarcadeplugin.js', array( 'jquery' ), false, true );
				break;
		}
	}
}
add_action('admin_enqueue_scripts', 'myarcade_admin_scripts');

/**
 * Retrieve file location folders for the wp media uploader.
 *
 * @return array|bool Array of folder parts or false if not a game or error.
 */
function myarcade_get_post_upload_dirs() {

	// Check if we try to upload a new game image/file to existing post.
	$type = filter_input( INPUT_POST, 'type' );
	$post_id = filter_input( INPUT_POST, 'post_id' );

	if ( false !== strpos( $type, 'myarcade_') && is_game( $post_id ) ) {
		// Get game details.
		$game_type = get_post_meta( $post_id, 'mabp_game_type', true );

		if ( ! $game_type ) {
			$game_type = 'custom';
		}

		$game_slug = get_post_field( 'post_name', $post_id );

		remove_filter( 'wp_handle_upload_prefilter', 'myarcade_wp_handle_upload_prefilter' );
		remove_filter('upload_dir', 'myarcade_game_post_upload_dir');

		$upload_dir_specific = myarcade_get_folder_path( $game_slug, $game_type );

		add_filter( 'wp_handle_upload_prefilter', 'myarcade_wp_handle_upload_prefilter' );
		add_filter('upload_dir', 'myarcade_game_post_upload_dir');

		return $upload_dir_specific;
	}

	return false;
}

/**
 * Raname game files uploaded with wp media uploader to fit into the MyArcade folder structure.
 *
 * @param  array $file Array of (name, type, tmp_name). Like $_FILE.
 * @return array $file Array
 */
function myarcade_wp_handle_upload_prefilter( $file ) {

	$upload_dir_specific = myarcade_get_post_upload_dirs();

	if ( $upload_dir_specific ) {
		$type = filter_input( INPUT_POST, 'type' );
		$post_id = filter_input( INPUT_POST, 'post_id' );
		$game_slug = get_post_field( 'post_name', $post_id );
		$extension = pathinfo( $file['name'], PATHINFO_EXTENSION );

		// Get the new unique file name.
		if ( 'myarcade_image' === $type ) {
		 $dir = 'thumbsdir';
		} else {
			$dir = 'gamesdir';
		}

		// Overwrite the file name.
		$file['name'] = wp_unique_filename( $upload_dir_specific[ $dir ], $game_slug . '.' . $extension );
	}

	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'myarcade_wp_handle_upload_prefilter' );

/**
 * Modifies the WordPress upload folders.
 *
 * @param  array $upload_dir Upload directory.
 * @return  array
 */
function myarcade_game_post_upload_dir( $upload_dir ) {

	$upload_dir_specific = myarcade_get_post_upload_dirs();

	if ( $upload_dir_specific ) {
		$type = filter_input( INPUT_POST, 'type' );

		switch ( $type ) {
			case 'myarcade_image':
				$upload_dir['path']   = untrailingslashit( $upload_dir_specific['thumbsdir'] );
				$upload_dir['subdir'] = untrailingslashit( str_replace($upload_dir_specific['basedir'], '', $upload_dir_specific['thumbsdir'] ) );
				$upload_dir['url']    = untrailingslashit( $upload_dir_specific['thumbsurl'] );
				break;

			case 'myarcade_game':
				$upload_dir['path']   = untrailingslashit( $upload_dir_specific['gamesdir'] );
				$upload_dir['subdir'] = untrailingslashit( str_replace($upload_dir_specific['basedir'], '', $upload_dir_specific['gamesdir'] ) );
				$upload_dir['url']    = untrailingslashit( $upload_dir_specific['gamesurl'] );
				break;
		}
	}

	return $upload_dir;
}
add_filter( 'upload_dir', 'myarcade_game_post_upload_dir' );

/**
 * Trigger WordPress media_upload_file action.
 */
function myarcade_media_upload_game_files() {
	do_action('media_upload_file');
}
add_action('media_upload_myarcade_image', 'myarcade_media_upload_game_files');
add_action('media_upload_myarcade_game', 'myarcade_media_upload_game_files');

/**
 * Extend WordPress upload mimes.
 *
 * @param  array $existing_mimes Array of mime types.
 * @return  array
 */
function myarcade_upload_mimes( $existing_mimes=array() ) {
	// Allow DCR file upload.
	$existing_mimes['swf'] = 'application/x-shockwave-flash';
	$existing_mimes['dcr'] = 'mime/type';

	return $existing_mimes;
}
add_filter('upload_mimes', 'myarcade_upload_mimes');

/**
 * Load requires scripts and styles.
 */
function myarcade_load_scriptstyle() {
	global $pagenow;

	$page = filter_input( INPUT_GET, 'page' );

	if ( 'admin.php' === $pagenow ) {
		wp_enqueue_script('jquery');

		if ( ( 'myarcade-manage-games' === $page ) || ( 'myarcade-manage-scores' === $page ) || ( 'myarcade-fetch' === $page ) ) {
			// Thickbox.
			wp_enqueue_script('thickbox');
			$thickcss = get_option( 'siteurl' ) . '/' . WPINC . '/js/thickbox/thickbox.css';
			wp_enqueue_style('thickbox_css', $thickcss, false, false, 'screen');
		}
	}

	if ( 'admin.php' === $pagenow || 'post.php' === $pagenow || 'myarcade_admin.php' == $page || 'myarcade-stats' == $page ) {
		// Add MyArcade CSS.
		$css = MyArcade()->plugin_url() . '/assets/css/myarcadeplugin.css';
		wp_enqueue_style('myarcade_css', $css, false, false, 'screen');
	}
}
add_action('admin_menu', 'myarcade_load_scriptstyle', 99);

/**
 * Show MyArcadePlugin notices.
 */
function myarcade_notices() {
}

/**
 * Show MyArcadePlugin header on plugin option pages.
 *
 * @param boolean $echo Set true to output the header. Otherwise nothing will happen.
 */
function myarcade_header($echo = true) {
	if (!$echo) {
		return;
	}
	?>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery(".toggle_container").hide();
			jQuery("h2.trigger").click(function(){
				jQuery(this).toggleClass("active").next().slideToggle("slow");
			});
		});
	</script>
	<?php
	echo '<div class="wrap">';
	?>
	<p style="margin-top: 10px"><img src="<?php echo esc_url( MyArcade()->plugin_url() ) . '/assets/images/logo.png'; ?>" alt="MyArcadePlugin" /></p>
	<?php
}

/**
 * Show MyArcadePlugin footer on plugin options page.
 *
 * @param boolean $echo Set true to output the footer. Otherwise nothing will happen.
 */
function myarcade_footer($echo = true) {
	if (!$echo) {
		return;
	}

	echo '</div>';
}

/**
 * Take over the update check
 *
 * @param  object $checked_data Data.
 * @return  object
 */
function myarcade_check_for_update( $checked_data ) {

	if ( empty( $checked_data->checked[ MyArcade()->plugin_basename ] ) ) {
		return $checked_data;
	}

	$request_args = array(
		'slug'    => MYARCADE_PLUGIN_SLUG,
		'version' => $checked_data->checked[ MyArcade()->plugin_basename ],
	);

	$request_string = prepare_request('update_check', $request_args);

	// Start checking for an update.
	$raw_response = wp_remote_post( MYARCADE_UPDATE_API . 'check.php', $request_string );

	if ( ! is_wp_error( $raw_response ) && isset( $raw_response['response']['code'] ) && ( 200 === $raw_response['response']['code'] ) ) {
		$response = unserialize($raw_response['body']);
	}

	if (isset($response) && is_object($response) && !empty($response)) {
		// Feed the update data into WP updater.
		$checked_data->response[ MyArcade()->plugin_basename ] = $response;
	}

	return $checked_data;
}
add_filter('pre_set_site_transient_update_plugins', 'myarcade_check_for_update');

/**
 * Take over the plugin info screen
 *
 * @param  bolean $result Result.
 * @param  string $action Action.
 * @param  object $args Args.
 * @return  stdClass
 */
function myarcade_api_call( $result, $action, $args ) {

	if ( ! isset( $args->slug ) || MYARCADE_PLUGIN_SLUG !== $args->slug ) {
		// Proceed only if this is request for our own plugin.
		return $result;
	}

	// Get the current version.
	$plugin_info = get_site_transient('update_plugins');
	$current_version = $plugin_info->checked[ MyArcade()->plugin_basename ];

	$args->version = $current_version;

	$request_string = prepare_request($action, $args);

	$request = wp_remote_post( MYARCADE_UPDATE_API . 'check.php', $request_string );

	if ( is_wp_error( $request ) ) {
		$response = new WP_Error( 'plugins_api_failed', __( 'An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>' ), $request->get_error_message() );
	} else {
		$response = unserialize( $request['body'] );

		if ( false === $response ) {
			$response = new WP_Error( 'plugins_api_failed', __( 'An unknown error occurred', 'myarcadeplugin' ), $request['body'] );
		}
	}

	return $response;
}
add_filter('plugins_api', 'myarcade_api_call', 10, 3);

/**
 * Create request query for the update check.
 *
 * @param  string $action Action.
 * @param  array  $args   Arguments.
 * @return  array
 */
function prepare_request( $action, $args ) {
	global $wp_version;

	return array (
		'body' => array (
			'action' => $action,
			'request' => serialize($args),
			'url' => get_bloginfo('url'),
			'item' => MYARCADE_PLUGIN_SLUG,
		),
		'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
	);
}

/**
 * Show a game.
 *
 * @param object $game Game object.
 */
function myarcade_show_game($game) {

	$contest = '';

	if ($game->leaderboard_enabled) {
		$leader = 'enabled';
	} else {
		$leader = 'disabled';
	}

	$play_url = MyArcade()->plugin_url() . '/core/playgame.php?gameid=' . $game->id;
	$edit_url = MyArcade()->plugin_url() . '/core/editgame.php?gameid=' . $game->id;

	// Buttons.
	$publish = "<button class=\"button-secondary\" onclick = \"jQuery('#gstatus_$game->id').html('<div class=\'gload\'> </div>');jQuery.post('" . admin_url( 'admin-ajax.php' ) . "',{action:'myarcade_handler',gameid:'$game->id',func:'publish'},function(data){jQuery('#gstatus_$game->id').html(data);});\">" . __( 'Publish', 'myarcadeplugin' ) . '</button>&nbsp;';
	$draft   = "<button class=\"button-secondary\" onclick = \"jQuery('#gstatus_$game->id').html('<div class=\'gload\'> </div>');jQuery.post('" . admin_url( 'admin-ajax.php' ) . "',{action:'myarcade_handler',gameid:'$game->id',func:'draft'},function(data){jQuery('#gstatus_$game->id').html(data);});\">" . __( 'Draft', 'myarcadeplugin' ) . '</button>&nbsp;';
	$delete  = "<button class=\"button-secondary\" onclick = \"jQuery('#gstatus_$game->id').html('<div class=\'gload\'> </div>');jQuery.post('" . admin_url( 'admin-ajax.php' ) . "',{action:'myarcade_handler',gameid:'$game->id',func:'delete'},function(data){jQuery('#gstatus_$game->id').html(data);});\">" . __( 'Delete', 'myarcadeplugin' ) . '</button>&nbsp;';
	$delgame = "<div class=\"myhelp\"><img style=\"cursor: pointer;border:none;padding:0;\" src='" . MyArcade()->plugin_url() . "/assets/images/delete.png' alt=\"Remove game from the database\" onclick = \"jQuery('#gstatus_$game->id').html('<div class=\'gload\'> </div>');jQuery.post('" . admin_url( 'admin-ajax.php' ) . "',{action:'myarcade_handler',gameid:'$game->id',func:'remove'},function(){jQuery('#gamebox_$game->id').fadeOut('slow');});\" />
	<span class=\"myinfo\">" . __( 'Remove this game from the database', 'myarcadeplugin' ) . '</span></div>';

	// Chek game dimensions.
	if ( empty($game->height) ) {
		$game->height = '600';
	}

	if ( empty($game->width)  ) {
		$game->width = '480';
	}

	$edit = '<a href="#" class="button-secondary" onclick="alert(\'' . __( 'If you want to edit games please consider upgrading to MyArcadePlugin Pro', 'myarcadeplugin' ) . '\');return false;" title="' . __( 'Edit', 'myarcadeplugin' ) . '">' . __( 'Edit', 'myarcadeplugin' ) . '</a>&nbsp;';

	if ( 'published' === $game->status ) {
		$edit_post = '<a href="post.php?post='.$game->postid.'&action=edit" class="button-secondary" target="_blank">Edit Post</a>&nbsp;';

		// contest button.
		if ( $game->leaderboard_enabled && defined( 'MYARCADECONTEST_VERSION' ) ) {
			$contest = '<a class="button" href="post-new.php?post_type=contest&gameid='.$game->postid.'">'.__( 'New Contest', 'myarcadeplugin').'</a>';
		}
	} else {
	 $edit_post = '';
	}

	// Generate content for the game box.
	if ( 'published' === $game->status ) {
		$name = get_the_title($game->postid);
		$thumb_url = get_post_meta($game->postid, 'mabp_thumbnail_url', true);
		$game_post = get_post($game->postid);
		$description = wp_strip_all_tags( $game_post->post_content );

		if ( strlen($description) > 320 ) {
			$dots = '..';
		} else {
			$dots = '';
		}

		$description = mb_substr( stripslashes($description), 0, 320) . $dots;

		$categs = wp_get_post_categories($game->postid);
		$categories = false;

		if ( $categs ) {
			$count = count($categs);

			for ($i=0; $i<$count; $i++) {
				$c = get_category($categs[$i]);
				$categories .= $c->name;

				if ($i < ($count - 1) ) {
				 $categories .= ', ';
				}
			}
		}

		if ($categories) {
			$categories = '<div style="margin-top:6px;"><strong>Categories:</strong> ' . $categories . '</div>';
		}
	} else {
		$game_categs = false;

		if ( isset($game->categs) ) {
			$game_categs = $game->categs;
		} elseif ( isset( $game->categories ) ) {
			$game_categs = $game->categories;
		}

		if ( is_array($game_categs) ) {
			$categories = '';
			$count = count($game_categs);

			for ($i=0; $i<$count; $i++) {
				$categories .= $game_categs[$i];

				if ($i < ($count - 1) ) {
				 $categories .= ', ';
				}
			}
		} else {
			$categories = $game_categs;
		}

		$name      = $game->name;
		$thumb_url = $game->thumbnail_url;

		$description = str_replace(array("\r", "\r\n", "\n"), ' ', $game->description);

		if ( strlen($description) > 320 ) {
			$dots = '..';
		} else {
			$dots = '';
		}

		$description = mb_substr( stripslashes($description), 0, 280) . $dots;

		if ( isset($categories) ) {
			$categories = '<div style="margin-top:6px;"><strong>Categories:</strong> ' . $categories . '</div>';
		} else {
			$categories = '';
		}
	}
	?>
	<div class="show_game" id="gamebox_<?php echo esc_attr( $game->id ); ?>">
		<div class="block">
			<table class="optiontable" width="100%">
				<tr valign="top">
					<td width="110" align="center">
						<img src="<?php echo esc_url( $thumb_url ); ?>" width="100" height="100" alt="" />
						<div class="g-features">
							<span class="lb_<?php echo esc_attr( $leader ); ?>" title="Leaderboards <?php echo ucfirst( esc_attr( $leader ) ); ?>"></span>
						</div>
					</td>
					<td colspan="2">
						<table>
							<tr valign="top">
								<td width="520">
									<strong><div id="gname_<?php echo esc_attr( $game->id ); ?>"><?php echo esc_html( $name ); ?></div></strong>
								</td>
								<td>
									<?php
									if ( isset($game->game_type) ) {
										$type = $game->game_type;
									} elseif ( isset( $game->type ) ) {
										$type = $game->type;
									} else {
										$type = '';
									}

									echo esc_html( ucfirst( $type ) );
									?>
								</td>
							</tr>
							<tr>
								<td>
									<?php echo wp_kses_post( $description ); ?>
									<br />
									<?php echo wp_kses_post( $categories ); ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center">
						<p style="margin-top:3px"><a class="thickbox button-primary" title="<?php echo esc_attr( $name ); ?>" href="<?php echo esc_url( $play_url ); ?>&keepThis=true&TB_iframe=true&height=<?php echo esc_attr( $game->height ); ?>&width=<?php echo esc_attr( $game->width ); ?>"><?php esc_attr_e( 'Play', 'myarcadeplugin' ); ?></a></p>
					</td>
					<td>
						<?php
							echo $delgame; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

							switch ($game->status) {
								case 'ignored':
								case 'new':
									echo $delete; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
									echo $edit; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
									echo $publish; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
									echo $draft; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
									break;
								case 'published':
									echo $delete; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
									echo $edit_post; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
									echo $contest; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
									break;
								case 'deleted':
									echo $edit; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
									echo $publish; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
									echo $draft; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
									break;
							}
						?>
					</td>
					<td width="130">
						<div id="gstatus_<?php echo esc_attr( $game->id ); ?>" style="margin: 0;font-weight:bold;float:right;">
							<?php echo esc_html( $game->status ); ?>
						</div>
					</td>
				</tr>

			</table>
		</div>
	</div>
	<?php
}

/**
 * MyArcade AJAX handler.
 */
function myarcade_handler() {
	global $wpdb;

	// Check if the current user has permissions to do that.
	if ( ! current_user_can('manage_options') ) {
		wp_die('You do not have permissions access this site!');
	}

	$game_id = filter_input( INPUT_POST, 'gameid' );
	$func    = filter_input( INPUT_POST, 'func' );
	$status  = '';

	switch ( $func ) {
		// Manage Games.
		case 'publish':
			$status = __( 'Game published', 'myarcadeplugin' );
		case 'draft':
			if ( ! $game_id ) {
				esc_attr_e( 'No Game ID!', 'myarcadeplugin' );
				die();
	}

			// Publish this game.
			$result = myarcade_add_games_to_blog(
				array(
					'game_id'     => $game_id,
					'echo'        => false,
					'post_status' => $func,
				)
			);

			if ( $result ) {
				if ( ! $status ) {
					$status = __( 'Draft added', 'myarcadeplugin' );
				}
			} else {
				$status = __( 'Error occured', 'myarcadeplugin' );
			}

			echo esc_html( $status );
			break;

		case 'delete':
			if ( ! $game_id ) {
				esc_attr_e( 'No Game ID!', 'myarcadeplugin' );
				die();
			}

			// Check if game is published.
			$game   = $wpdb->get_row( $wpdb->prepare( "SELECT postid, name FROM {$wpdb->prefix}myarcadegames WHERE id = %d", $game_id), ARRAY_A );
			$postid = $game['postid'];

			if ( !$postid )  {
				// Alternative check for older versions of MyArcadePlugin.
				$name = $game['name'];
				$postid = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_title = %s", $name ) );
			}

			if ($postid) {
				myarcade_delete_game($postid);
				// Delete post.
				wp_delete_post($postid);
			}

			// Update game status.
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}myarcadegames SET status = 'deleted', postid = '' WHERE id = %d", $game_id ) );

			// Get game status.
			$status = $wpdb->get_var( $wpdb->prepare( "SELECT status FROM {$wpdb->prefix}myarcadegames WHERE id = %d", $game_id ) );
			echo esc_attr( $status );
			break;

		case 'remove':
			if ( ! $game_id ) {
				_e( 'No Game ID!', 'myarcadeplugin' );
				die();
			}

			// Remove this game from mysql database.
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}myarcadegames WHERE id = %d LIMIT 1", $game_id ) );
			echo esc_html__( 'Game removed', 'myarcadeplugin' );
			break;

		// Category Mapping.
		case 'addmap':
			$mapcat = intval( filter_input( INPUT_POST, 'mapcat', FILTER_VALIDATE_INT ) );

			if ( $mapcat > 0 ) {
				// Init var for map processing.
				$map_category = true;
				$section = filter_input( INPUT_POST, 'section' );

				if ( 'general' === $section ) {
					// Get game_categories as array.
					$feedcategories = get_option('myarcade_categories');
					$feed_count     = count( $feedcategories );

					for ( $i = 0; $i < $feed_count; $i++ ) {
						$feedcat = filter_input( INPUT_POST, 'feedcat' );

						if ( $feedcategories[ $i ]['Slug'] === $feedcat ) {
							if ( empty($feedcategories[$i]['Mapping']) ) {

								$feedcategories[ $i ]['Mapping'] = $mapcat;
							} else {
								// Check, if this category is already mapped.
								$mapped_cats = explode(',', $feedcategories[$i]['Mapping']);

								foreach ($mapped_cats as $mapped_cat) {
									if ( $mapped_cat == $mapcat ) {
										$map_category = false;
										break;
									}
								}

								$feedcategories[ $i ]['Mapping'] = $feedcategories[ $i ]['Mapping'] . ',' . $mapcat;
							}

							break;
						}
					}

					if ( true === $map_category ) {
						// Update Mapping.
						update_option('myarcade_categories', $feedcategories);

						$general= get_option('myarcade_general');

						if ( 'post' === $general['post_type'] ) {
							$cat_name = get_cat_name( $mapcat );
						} else {
							if (taxonomy_exists($general['custom_category'])) {
								$cat_name_tax = get_term_by( 'id', $mapcat, $general['custom_category'] );
								$cat_name = $cat_name_tax->name;
							}
						}
						?>
						<span id="general_delmap_<?php echo esc_attr( $mapcat ); ?>_<?php echo esc_attr( $feedcategories[ $i ]['Slug'] ); ?>" class="remove_map">
							<img style="float:left;top:2px;position:relative;" src="<?php echo esc_html( MyArcade()->plugin_url() ); ?>/assets/images/remove.png" alt="UnMap" onclick="myabp_del_map('<?php echo esc_attr( $mapcat ); ?>', '<?php echo esc_attr( $feedcategories[ $i ]['Slug'] ); ?>', 'general')" />&nbsp;<?php echo esc_html( $cat_name ); ?>
						</span>
						<?php
					}
				}
			}
			break;

		case 'delmap':
			$mapcat = intval( filter_input( INPUT_POST, 'mapcat', FILTER_VALIDATE_INT ) );

			if ( $mapcat > 0 ) {
				$update_mapping = false;

				$section = filter_input( INPUT_POST, 'section' );

				if ( 'general' === $section ) {
					// Get game_categories as array.
					$feedcategories = get_option('myarcade_categories');
					$feedcat        = filter_input( INPUT_POST, 'feedcat' );
					$feed_count     = count( $feedcategories );

					for ( $i = 0; $i < $feed_count; $i++ ) {
						if ( $feedcategories[ $i ]['Slug'] === $feedcat ) {
							$mapped_cats = explode(',', $feedcategories[$i]['Mapping']);
							$mapped_cats_count = count( $mapped_cats );

							for ( $j = 0; $j < $mapped_cats_count; $j++ ) {
								if ( intval( $mapped_cats[ $j ] ) === $mapcat ) {
									unset($mapped_cats[$j]);
									$feedcategories[$i]['Mapping'] = implode(',', $mapped_cats);
									$update_mapping = true;
									break;
								}
							}
							break;
						}
					}

					if ( true === $update_mapping ) {
						update_option('myarcade_categories', $feedcategories);
					}
				}
			}
			break;

		// Database Actions.
		case 'delgames':
			$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}myarcadegames" );
			?>
			<script type="text/javascript">
				alert('All games deleted!');
			</script>
			<?php
			break;

		case 'remgames':
			$wpdb->query( "DELETE FROM {$wpdb->prefix}myarcadegames WHERE status = 'deleted'" );
			?>
			<script type="text/javascript">
				alert('Games marked as "deleted" where removed from the database!');
			</script>
			<?php
			break;

		case 'zeroscores':
			$wpdb->query( "DELETE FROM {$wpdb->prefix}myarcadescores WHERE score = '0' OR score = ''" );
			?>
			<script type="text/javascript">
				alert('Zero scores deleted!');
			</script>
			<?php
			break;

		case 'delscores':
			$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}myarcadescores" );
			$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}myarcadehighscores" );
			$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}myarcademedals" );
			?>
			<script type="text/javascript">
				alert('All scores deleted!');
			</script>
			<?php
			break;

		case 'delete_score':
			$score_id = intval( filter_input( INPUT_POST, 'scoreid' ) );

			if ( $score_id ) {

				// get score.
				$old_score = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}myarcadescores WHERE id =  %d", $score_id ) );
				// Get highscore.
				$highscore = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}myarcadehighscores WHERE game_tag = %s AND user_id = %d AND score = %s", $old_score->game_tag, $old_score->user_id, $old_score->score ) );

				if ( $highscore ) {
					// The user is highscore holder.
					// Remove highscore.
					$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}myarcadehighscores WHERE id = %d", $highscore->id ) );
				}

				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}myarcadescores WHERE id = %d", $score_id ) );
			}
			break;

		case 'delete_game_scores':
			$game_tag = filter_input( INPUT_POST, 'game_tag' );

			if ( $game_tag ) {
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}myarcadescores WHERE game_tag = %s", $game_tag ) );
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}myarcadehighscores WHERE game_tag = %s", $game_tag ) );
			}
			break;

		case 'dircheck':
			$directory = filter_input( INPUT_POST, 'directory' );

			if ( $directory ) {
				$upload_dir = MyArcade()->upload_dir();

				if ( 'games' === $directory ) {
					if ( !is_writable( $upload_dir['gamesdir'] ) ) {
						?>
						<p class="mabp_error mabp_680">
							<?php printf( esc_html__( "The games directory '%s' must be writeable (chmod 777) in order to download games.", 'myarcadeplugin' ), $upload_dir['gamesdir'] ); ?>
						</p>
						<?php
					}
				} else {
					if ( !is_writable( $upload_dir['thumbsdir'] ) ) {
						?>
						<p class="mabp_error mabp_680">
							<?php printf( esc_html__( "The thumbs directory '%s' must be writeable (chmod 777) in order to download thumbnails or screenshots.", 'myarcadeplugin' ), $upload_dir['thumbsdir'] ); ?>
						</p>
						<?php
					}
				}
			}
			break;
	}

	die();
}
add_action('wp_ajax_myarcade_handler', 'myarcade_handler');

/**
 * Display settings update notice.
 */
function myarcade_plugin_update_notice() {

	$feedaction = filter_input( INPUT_POST, 'feedaction' );

	// Avoid message displaying when settings have been saved.
	if ( 'save' === $feedaction ) {
		return;
	}
	?>
	<div style="border-radius:4px;-moz-border-radius:4px;-webkit-border-radius:4px;background:#FEB1B1;border:1px solid #FE9090;color:#820101;font-size:14px;font-weight:bold;height:auto;margin:30px 15px 15px 0px;overflow:hidden;padding:4px 10px 6px;line-height:30px;">
		MyArcadePlugin was just updated / installed - Please visit the <a href="admin.php?page=myarcade-edit-settings">Plugin Options Page</a>, setup the plugin and save settings!
	</div>
	<?php
}

// Check if we should display the settings update notice.
if ( get_transient('myarcade_settings_update_notice') ) {
	add_action('admin_notices', 'myarcade_plugin_update_notice', 99);
}

/**
 * Helper function for form selections.
 *
 * @param  string $selected Selected item.
 * @param  string $current  Current item.
 */
function myarcade_selected( $selected, $current ) {
	if ( $selected === $current) {
		echo ' selected';
	}
}

/**
 * Helper function for check boxes.
 *
 * @param mixed $var   Var.
 * @param mixed $value Value.
 */
function myarcade_checked( $var, $value ) {
	if ( $var === $value) {
		echo ' checked';
	}
}

/**
 *  Helper function for multi selectors.
 *
 * @param mixed $var   Var.
 * @param mixed $value Value.
 */
function myarcade_checked_array( $var, $value ) {
	if ( is_array($var) ) {
		foreach ($var as $element) {
			if ( $element === $value ) {
				echo ' checked';
				break;
			}
		}
	}
}

/**
 * Display the tracking message notification.
 *
 * @param bool $show_skip_button Wheather to show the button or not.
 */
function myarcade_tracking_message( $show_skip_button = true ) {
	?>
	<div class="myarcade_message myarcade_notice">
		<h3><?php esc_html_e( 'MyArcadePlugin Stats', 'myarcadeplugin' ); ?></h3>
		<p>
			<?php printf( esc_html__( 'Enable site statistics to collect <strong>game plays and play duration</strong> for each game. This will help you to optimize your site and to get a better overview of your visitors. By enabling this feature MyArcadePlugin will collect and send us non-sensitive diagnostic data and usage information. Those data will help us to make MyArcadePlugin even better. %1$sFind out more%2$s.', 'myarcadeplugin' ), '<a href="https://myarcadeplugin.com/usage-tracking/" target="_blank">', '</a>' ); ?></p>
		<p class="submit">
			<a class="button-primary button button-large" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'myarcade_tracker_optin', 'true' ), 'myarcade_tracker_optin', 'myarcade_tracker_nonce' ) ); ?>"><?php esc_html_e( 'Enable', 'myarcadeplugin' ); ?></a>
			<?php if ( $show_skip_button ) : ?>
			<a class="button-secondary button button-large skip"  href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'myarcade_tracker_optout', 'true' ), 'myarcade_tracker_optout', 'myarcade_tracker_nonce' ) ); ?>"><?php esc_html_e( 'No thanks', 'myarcadeplugin' ); ?></a>
			<?php endif; ?>
		</p>
	</div>
<?php
}

/**
 * Handles tracker opt in/out.
 */
function myarcade_tracker_optin() {

	$optin  = filter_input( INPUT_GET, 'myarcade_tracker_optin' );
	$optout = filter_input( INPUT_GET, 'myarcade_tracker_optout' );
	$nonce  = filter_input( INPUT_GET, 'myarcade_tracker_nonce' );

	if ( $optin && wp_verify_nonce( $nonce, 'myarcade_tracker_optin' ) ) {
		update_option( 'myarcade_allow_tracking', 'yes' );
		MyArcade_Tracker::send_tracking_data();
	} elseif ( $optout && wp_verify_nonce( $nonce, 'myarcade_tracker_optout' ) ) {
		update_option( 'myarcade_allow_tracking', 'no' );
		delete_option( 'myarcade_tracker_last_send' );
	}
}
add_action( 'admin_init', 'myarcade_tracker_optin' );
