<?php
/**
 * File handle functions
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 */

// No direct access
if ( ! defined( 'ABSPATH' ) ) {
  die();
}

/**
 * Delete a given file from the hard drive
 *
 * @version 5.13.0
 * @access  public
 * @param   string $file_abs Absolute path to a file
 * @return  void
 */
function myarcade_del_file( $file_abs ) {
  if ( file_exists( $file_abs ) ) {
      @unlink( $file_abs );
  }
}

/**
 * Get the abspath of the given URL
 *
 * @version 5.28.1
 * @access  public
 * @param   string $url URL
 * @return  string|bool string if the absolute path has been found. Otherwise FALSE
 */
function myarcade_get_abs_path( $url )  {

  // Get the content URL
  $content_url = content_url();

  // Remove scheme from URL before comparision
  $scheme = array( 'http://', 'https://' );
  $clean_url = str_replace( $scheme, '', $url );
  $clean_content_url = str_replace( $scheme, '', $content_url );

  // Check if the URL matches with our content URL
  if ( strpos( $clean_url, $clean_content_url ) === FALSE ) {
    // External site
    return false;
  }

  // Remove content URL
  $link_part = str_replace( $clean_content_url, "", $clean_url );

  // Generate the file abs path
  $file_path = WP_CONTENT_DIR . $link_part;

  if ( file_exists( $file_path ) ) {
    return $file_path;
  }

  return false;
}

/**
 * Delete game files when deleting a post
 *
 * @param   int $post_ID Post ID
 * @return  bool
 */
function myarcade_delete_game( $post_ID ) {
  global $wpdb;

  // Proceed only if this is a game
  if ( ! is_game( $post_ID ) ) {
    // do nothing
    return;
  }

  // Delete featured image from the media library
  $thumbnail_id = get_post_thumbnail_id( $post_ID );

  if ( $thumbnail_id ) {
    wp_delete_attachment( $thumbnail_id );
  }

  // For MyArcadePlugin version below 5.28.0
  $thumbnail_url = get_post_meta( $post_ID, "mabp_thumbnail_url", true );

  if ( $thumbnail_url ) {
    // Try to determinate the file absolute path
    $thumbnail_path = myarcade_get_abs_path( $thumbnail_url );

    if ( $thumbnail_path ) {
      // Delete local file
      myarcade_del_file( $thumbnail_path );
    }
  }

  // Delete game screenshots if exists
  for ( $i = 1; $i <= 4; $i++ ) {
    $screenshot_url = get_post_meta( $post_ID, "mabp_screen".$i."_url", true );

    if ( $screenshot_url ) {
      $screenshot_path = myarcade_get_abs_path( $screenshot_url );

      if ( $screenshot_path ) {
        myarcade_del_file( $screenshot_path );
      }
    }
  }

  // Delete game file
  $game_file = get_post_meta( $post_ID, "mabp_swf_url", true );
  $game_path = myarcade_get_abs_path( $game_file  );

  if ( $game_path ) {
    myarcade_log_core( "Delete game file: {$game_path}" );

    // Check if this is html5 games
    if ( 'html5' == get_post_meta( $post_ID, "mabp_game_type", true ) ) {
      // remove index.html
      $game_path = str_replace( '/index.html', '', $game_path );

      myarcade_log_core( "Delete HTML5 Folder: {$game_path}" );

      // We have to make sure that the folder is within our games folder to prevent deleting of wrong files
      if ( strpos( $game_path, '/uploads/games/html5/' ) !== FALSE ) {
        require_once( ABSPATH . '/wp-admin/includes/class-wp-filesystem-base.php' );
        require_once( ABSPATH . '/wp-admin/includes/class-wp-filesystem-direct.php' );
        $direct = new WP_Filesystem_Direct( array() );
        $direct->rmdir( $game_path, true );
      }
		} else {
			// Delete a single file.
      myarcade_del_file( $game_path );
    }
	} else {
    myarcade_log_core( "Can't determinate game file: {$game_file}" );
  }

	// Get game_tag.
	$game_tag = $wpdb->get_var( $wpdb->prepare( "SELECT game_tag FROM {$wpdb->prefix}myarcadegames WHERE postid = %d", $post_ID ) );
	// Delete scores.
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}myarcadescores WHERE  game_tag = %s", $game_tag ) );
	// Delete game stats.
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}myarcade_plays WHERE post_id = %d", $post_ID ) );
	// Delete game.
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}myarcadegames WHERE  postid = %d", $post_ID ) );
}
add_action('before_delete_post', 'myarcade_delete_game');

/**
 * Downloads a file using WordPress HTTP function. After the download
 * the file content will be returned. On error the function will return false.
 *
 * @version 5.13.0
 * @access  public
 * @param   string $url URL
 * @return  array
 */
function myarcade_get_file( $url ) {

  $output = array ( 'response' => null, 'error' => null );

  $args = array('timeout' => '300', 'sslverify' => false);
  $response = wp_remote_get($url, $args);

  // Check for error
  if ( is_wp_error($response) ) {
    $output['error'] = $response->get_error_message();
  }
  else {
    // Check if the server sent a 404 code
    if (wp_remote_retrieve_response_code($response) == 404) {
      $output['error'] = __("File not found", 'myarcadeplugin');
    }
  }

  $output['response'] = wp_remote_retrieve_body($response);

  return $output;
}

/**
 * Determinate the file folder depended on the game type, file type and file name
 *
 * @version 5.15.0
 * @access  public
 * @param   string $name File name
 * @param   string $type Game type
 * @return  array        Folder paths
 */
function myarcade_get_folder_path($name = '', $type = '') {
  global $myarcade_feedback;

	$upload_dir = MyArcade()->upload_dir();

  if ( empty($name) || empty($type) ) {
    $myarcade_feedback->add_message("Missing parameters on the create folder function!");
    return $upload_dir;
  }

  $general = get_option('myarcade_general');
  $general['folder_structure'] = trim($general['folder_structure']);

  // If not folder structure is set then return false. Check if user has entered just a slash ("/")
  if ( empty($general['folder_structure']) || (strlen($general['folder_structure']) <= 1) ) {
    return $upload_dir;
  }

  // Init folder vars
  $folder = false;
  $sub_folder = "0-9";

  // Check the first char of the game name
  if ( ctype_alnum($name[0]) && !is_numeric($name[0]) ) {
    // Use alphabetic folder
    $sub_folder = ucfirst($name[0]);
  }

  // Replace placeholders with the defined folder structure
  $folder = str_replace("%game_type%", $type, $general['folder_structure']);
  $folder = str_replace("%alphabetical%", $sub_folder, $folder);
  // Clean up the folder string
  $folder = str_replace( '//', '/', $folder );
  // append a slash
  $folder = trailingslashit( $folder );
  // add slash at the beginning
  /*if ( strpos( $folder, '/') !== 0 ) {
    $folder = '/' . $folder;
  }*/

  // Check if the folder exists and create if needed
  if ( wp_mkdir_p( $upload_dir['gamesdir'] . $folder ) ) {
    // Folder created or already exists
    // Modify default game paths
    $upload_dir['gamesdir']   = $upload_dir['gamesdir'] . $folder;
    $upload_dir['gamesurl']   = $upload_dir['gamesurl'] . $folder;
    $upload_dir['gamesbase']  = $upload_dir['gamesbase'] . $folder;
  }
  else {
    // Folder creation failed
    $myarcade_feedback->add_message("Can't create folder: " . $upload_dir['gamesdir'] . $folder );
  }

  if ( wp_mkdir_p( $upload_dir['thumbsdir'] . $folder ) ) {
    // Folder created or already exists
    // Modify default game paths
    $upload_dir['thumbsdir']  = $upload_dir['thumbsdir'] . $folder;
    $upload_dir['thumbsurl']  = $upload_dir['thumbsurl'] . $folder;
    $upload_dir['thumbsbase'] = $upload_dir['thumbsbase'] . $folder;
  }
  else {
    // Folder creation failed
    $myarcade_feedback->add_message("Can't create folder: " . $upload_dir['thumbsdir'] . $folder );
  }

  return $upload_dir;
}

/**
 * Display a max post size message
 *
 * @version 5.13.0
 * @access  public
 * @return  void
 */
function myarcade_get_max_post_size_message() {

  $post_max_size = ini_get( 'post_max_size' ) . 'B';

  if ( $post_max_size ) {
    ?>
    <div class="mabp_info mabp_680">
      <?php echo sprintf( __("Your server settings allow you to upload files up to %s.", 'myarcadeplugin'), $post_max_size ); ?>
    </div>
    <?php
  }
}

/**
 * Returns the max post size in bytes
 *
 * @version 5.13.0
 * @access  public
 * @return  int
 */
function myarcade_get_max_post_size_bytes() {

  $post_max_size = ini_get( 'post_max_size' );

  switch (substr ($post_max_size, -1)) {
    case 'M': case 'm': return (int)$post_max_size * 1048576;
    case 'K': case 'k': return (int)$post_max_size * 1024;
    case 'G': case 'g': return (int)$post_max_size * 1073741824;
    default: return $post_max_size;
  }
}

/**
 * List all files available in the uploads/game_type folder
 *
 * @version 5.13.0
 * @access  public
 * @return  void
 */
function myarcade_get_filelist() {

  if ( empty( $_POST['type'] ) ) {
    die();
  }

	$upload_dir = MyArcade()->upload_dir();

  $type = sanitize_text_field( filter_input( INPUT_POST, 'type' ) );
  $dir =  $upload_dir['gamesdir'] . 'uploads/' . $type;

  $files_array = array();

  // Open directory
  $handle = opendir( $dir );

  if ( $handle ) {
    while (false !== ($file = readdir($handle))) {
      if ($file != "." && $file != ".." && $file != "Thumbs.db" && $file != ".DS_Store") {
        $files_array[] = $file;
      }
    }
    closedir($handle);

    natcasesort($files_array);
  }

  if ( !empty( $files_array ) ) {
    echo '<select name="fileselect'.$type.'" id="fileselect'.$type.'">';

    foreach ($files_array as $file_name) {
      echo '<option value="'.$file_name.'">'.$file_name.'</option>';
    }

    echo '</select>';

  }
  else {
   echo '<span id="fileselect'.$type.'">'. __("No files found!", 'myarcadeplugin') . '</span>';
  }

  die();
}
add_action('wp_ajax_myarcade_get_filelist', 'myarcade_get_filelist');

/**
 * Adds an image to WordPrss media base
 *
 * @version 5.28.0
 * @since   5.28.0
 * @param   string $file_url  File URL
 * @param   string $file_path File absolute path
 * @return  integer           WordPress attachment ID or 0/false on error
 */
function myarcade_add_attachment( $file_url, $file_path ) {

  if ( is_multisite() ) {
    delete_transient( 'dirsize_cache' );
  }

  // Check the type of file. We'll use this as the 'post_mime_type'.
  $filetype = wp_check_filetype( basename( $file_path ), null );

  $attachment = array(
    'guid' => $file_url,
    'post_mime_type' => $filetype['type'],
    'post_title' => preg_replace('/\.[^.]+$/', '', basename( $file_path ) ),
    'post_content' => '',
    'post_status' => 'inherit'
  );

  $attachment_id = wp_insert_attachment( $attachment, $file_path );

  if ( $attachment_id ) {
    // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
		require_once ABSPATH . 'wp-admin/includes/image.php';

    // Generate the metadata for the attachment, and update the database record.
    $attachment_data = wp_generate_attachment_metadata( $attachment_id, $file_path );
    wp_update_attachment_metadata( $attachment_id, $attachment_data );
  }

  return $attachment_id;
}
