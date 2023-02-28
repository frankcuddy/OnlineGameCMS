<?php
/**
 * Import games AJAX Handler
 * Handles file uploads for each game type
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @package MyArcade/Game/Imprt
 */

// No direct access.
if( !defined( 'ABSPATH' ) ) {
  die();
}

// Check user.
if ( function_exists('current_user_can') && !current_user_can('edit_posts') ) {
  die();
}

// Load required WordPress files.
require_once ABSPATH . 'wp-admin/includes/file.php';

// Courtesy of php.net, the strings that describe the error indicated in $_FILES[{form field}]['error'].
$upload_error_strings = array(
	false,
	__( 'The uploaded file exceeds the upload_max_filesize directive in php.ini.' ),
	__( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.', 'myarcadeplugin' ),
	__( 'The uploaded file was only partially uploaded.', 'myarcadeplugin' ),
	__( 'No file was uploaded.', 'myarcadeplugin' ),
  '',
	__( 'Missing a temporary folder.', 'myarcadeplugin' ),
	__( 'Failed to write file to disk.', 'myarcadeplugin' ),
	__( 'File upload stopped by extension.', 'myarcadeplugin' ),
);

$upload_dir = MyArcade()->upload_dir();

$game = new stdClass();
$game->info_dim = '';
$game->error = '';

$result = false;

$upload_action = filter_input( INPUT_POST, 'upload' );
$gameurl       = filter_input( INPUT_POST, 'gameurl' );
$fileselectswf = filter_input( INPUT_POST, 'fileselectswf' );
$thumburl      = filter_input( INPUT_POST, 'thumburl' );

// Check the submission.
switch ( $upload_action ) {

	// Upload SWF / DCR File.
  case 'swf':
    if ( !empty($_FILES['gamefile']['name']) ) {
			// Error check.
      if ( !empty($_FILES['gamefile']['error']) ) {
				$game->error = $upload_error_strings[ intval( $_FILES['gamefile']['error'] ) ];
			} else {
        $file_temp = esc_html( $_FILES['gamefile']['tmp_name'] );
        $file_info = pathinfo( esc_html( $_FILES['gamefile']['name'] ) );
				// generate new file name.
        $upload_dir_specific = myarcade_get_folder_path($file_info['filename'], 'custom');
        $file_name = wp_unique_filename( $upload_dir_specific['gamesdir'], $file_info['basename'] );
        $result = move_uploaded_file($file_temp, $upload_dir_specific['gamesdir'] . $file_name);
				// Delete temp file.
				unlink( esc_html( $_FILES['gamefile']['tmp_name'] ) );
      }
		} elseif ( $gameurl ) {
      // grab from net?
      $file_temp = myarcade_get_file( $gameurl );

      if ( !empty($file_temp['error']) ) {
				// Get error message.
        $game->error = $file_temp['error'];
			} else {
        $file_info = pathinfo( $gameurl );
        $upload_dir_specific = myarcade_get_folder_path($file_info['filename'], 'custom');
        $file_name = wp_unique_filename( $upload_dir_specific['gamesdir'], $file_info['basename']);
        $result = file_put_contents(  $upload_dir_specific['gamesdir'] . $file_name, $file_temp['response']);
      }
		} elseif ( $fileselectswf ) {
      $full_abs_path = $upload_dir['gamesdir'] . '/uploads/swf/' . $fileselectswf;

      if ( !file_exists( $full_abs_path ) ) {
        $game->error = __("Can't find the selected file.", 'myarcadeplugin');
			} else {
        $file_info      = pathinfo( $fileselectswf );
        $upload_dir_specific = myarcade_get_folder_path($file_info['filename'], 'custom');
        $file_name      = wp_unique_filename($upload_dir_specific['gamesdir'], $file_info['basename']);
        $result         = rename($full_abs_path, $upload_dir_specific['gamesdir'] . $file_name);
      }
		} else {
      $result = false;
    }

    if ( empty($game->error) ) {

			if ( true === $result ) {
				// Get the file extension.
				if ( 'dcr' === strtolower( $file_info['extension'] ) ) {
          $game->type = 'dcr';
				} else {
          $game->type = 'custom';
        }

        $game->name = ucfirst($file_info['filename']);
        $game->location_abs = $upload_dir_specific['gamesdir'] . $file_name;
        $game->location_url = $upload_dir_specific['gamesurl'] . $file_name;

				// try to detect dimensions.
        $game_dimensions = @getimagesize($game->location_abs);
        $game->width    = intval($game_dimensions[0]);
        $game->height   = intval($game_dimensions[1]);
        $game->info_dim = 'Game dimensions: '.$game->width.'x'.$game->height;

        if ( empty($game->width) || empty($game->height) ) {
          $game->width  = 0;
          $game->height = 0;
          $game->info_dim = 'Can not detect game dimensions';
        }

				// Try to get the game name.
        $name = explode('.', $game->name);
        $game->realname = ucfirst( str_replace('_', ' ', $name[0]) );
			} else {
				$game->error = __( 'Can not upload file!', 'myarcadeplugin' );
    }
  }
  break;

	// Upload Game Thumb.
  case 'thumb':
    if ( !empty($_FILES['thumbfile']['name']) ) {
			// Error check.
      if ( !empty($_FILES['gamefile']['error']) ) {
				$game->error = $upload_error_strings[ intval( $_FILES['gamefile']['error'] ) ];
			} else {
        $file_temp = $_FILES['thumbfile']['tmp_name'];
        $file_info = pathinfo($_FILES['thumbfile']['name']);
				// generate new file name.
        $upload_dir_specific = myarcade_get_folder_path($file_info['filename'], 'custom');
        $file_name = wp_unique_filename( $upload_dir_specific['thumbsdir'], $file_info['basename'] );
        $result = move_uploaded_file($file_temp, $upload_dir_specific['thumbsdir'] . $file_name);
				// Delete temp file.
        @unlink($_FILES['thumbfile']['tmp_name']);
      }
		} elseif ( $thumburl ) {
      // grab from net?
      $file_temp = myarcade_get_file( $thumburl );

      if ( !empty($file_temp['error']) ) {
				// Get error message.
        $game->error = $file_temp['error'];
			} else {
        $file_info = pathinfo( $thumburl );
        $upload_dir_specific = myarcade_get_folder_path($file_info['filename'], 'custom');
        $file_name = wp_unique_filename( $upload_dir_specific['thumbsdir'], $file_info['basename'] );
        $result = file_put_contents( $upload_dir_specific['thumbsdir'] . $file_name, $file_temp['response'] );
      }
    }

    if ( empty($game->error) ) {
			if ( true === $result ) {
        $game->thumb_name = $file_name;
        $game->thumb_url  = $upload_dir_specific['thumbsurl'] . $file_name;
        $game->thumb_id   = myarcade_add_attachment( $game->thumb_url, $upload_dir_specific['thumbsdir'] . $file_name );
			} else {
        $game->error = 'Can not upload thumbnail!';
      }
    }
  break;

	// Upload Game Screenshots.
  case 'screen':
    for ($i = 0; $i <= 3; $i++) {
			$screenshot = 'screen' . $i;
      $result = false;

      if ( !empty($_FILES[$screen]['name']) ) {
				// Error check.
        if ( !empty($_FILES[$screen]['error']) ) {
					$game->error = $upload_error_strings[ $_FILES[ $screenshot ]['error'] ];
				} else {
					// There is a screen to upload.
					$file_temp           = $_FILES[ $screenshot ]['tmp_name'];
					$file_info           = pathinfo( $_FILES[ $screenshot ]['name'] );
          $upload_dir_specific = myarcade_get_folder_path($file_info['filename'], 'custom');
          $file_name = wp_unique_filename( $upload_dir_specific['thumbsdir'], $file_info['basename'] );
          $result = move_uploaded_file($file_temp, $upload_dir_specific['thumbsdir'] . $file_name);
					// Delete temp file.
					@unlink( $_FILES[ $screenshot ]['tmp_name'] );
      }
			} elseif ( ! empty( $_POST[ $screenshot . 'url' ] ) ) {
				// There is a screen to grab.
				$file_temp = myarcade_get_file( $_POST [ $screenshot . 'url' ] );

        if ( !empty($file_temp['error']) ) {
					// Get error message.
          $game->error = $file_temp['error'];
				} else {
          $file_info = pathinfo($_POST[$screen.'url']);
          $upload_dir_specific = myarcade_get_folder_path($file_info['filename'], 'custom');
          $file_name = wp_unique_filename( $upload_dir_specific['thumbsdir'], $file_info['basename']);
          $result = file_put_contents( $upload_dir_specific['thumbsdir'] . $file_name, $file_temp['response']);
        }
      }

			if ( true === $result ) {
        $game->screen_abs[$i] = $upload_dir_specific['thumbsdir'] . $file_name;
        $game->screen_url[$i] = $upload_dir_specific['thumbsurl'] . $file_name;
        $game->screen_name[$i]= $file_name;
        $game->screen_error[$i] = 'OK';
			} else {
        $game->screen_error[$i] = 'Upload Failed For Screen No. '.($i+1).' '.$game->error;
        $game->screen_abs[$i] = '';
        $game->screen_url[$i] = '';
        $game->screen_name[$i]= '';
      }
    }
		break;

	// Import Embed / Iframe Code.
  case 'emif':
    if ( !empty( $_POST['embedcode'] ) ) {
      $game_code = filter_input( INPUT_POST, 'embedcode' );

			// Check the code.
      if( filter_var( $game_code, FILTER_VALIDATE_URL ) ) {
        $game->type = 'iframe';
			} else {
        $game->type = 'embed';
      }

      $game->importgame = urlencode( str_replace( '"', '\'', $game_code ) );
      $game->result = 'OK';
		} else {
			$game->error = 'No embed code entered!';
		}
		break;

	default:
		$game->error = __( 'Unknown Import Method', 'myarcadeplugin' );
  break;
}

// Prepare the output.
$json = wp_json_encode( $game );

wp_die( $json );
