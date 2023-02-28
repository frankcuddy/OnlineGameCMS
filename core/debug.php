<?php
/**
 * MyArcadePlugin Debugging Functions
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @package MyArcadePlugin/Debug
 */

// No direct Access.
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

define( 'MYARCADE_DEBUG',            false );
define( 'MYARCADE_DEBUG_CORE',       false );
define( 'MYARCADE_DEBUG_IMPORT',     false );
define( 'MYARCADE_DEBUG_SCORES',     false );
define( 'MYARCADE_DEBUG_TRANSLATOR', false );

/**
 * Writes a message into a log file
 *
 * @param string $message Message which should be logged.
 */
function myarcade_debuglog ( $message = '' ) {

	$logdir = MyArcade()->plugin_dir() . '/logs';

  if ( !file_exists($logdir) ) {
		// Create logging folder.
    @mkdir($logdir, '0777');
  }

  if ( file_exists($logdir) ) {
    $logfile = $logdir . '/' . date('Y_m_d') . '_log.txt';

		// open or create a log file.
    if ( !is_file($logfile) ) {
			// File doesn't exist. Create a log file.
      $fp = fopen($logfile, 'w+');
		} elseif ( is_writable( $logfile ) && is_file( $logfile ) ) {
			// Open existing file.
      $fp = fopen($logfile, 'a+');
    }

    // Did we open a file?
    if ( $fp ) {
			// Log the message.
      $content = "\n\n";
			$content .= '===========' . date( 'l dS \of F Y h:i:s A' ) . '===========';
      $content .= "\n\n";
      $content .= $message;
      fwrite($fp,$content);
      fclose($fp);
    }
  }
}

if ( MYARCADE_DEBUG ) {
  add_action('myarcade_logging','myarcade_debuglog');
}

/**
 * Log score submission.
 *
 * @param string $message Message which should be logged.
 */
function myarcade_log_score( $message = '' ) {
  if ( !MYARCADE_DEBUG ) {
    return;
  }

  if ( MYARCADE_DEBUG_SCORES ) {
    do_action('myarcade_logging', $message);
  }
}

/**
 * Log manual import.
 *
 * @param string $message Message which should be logged.
 */
function myarcade_log_import( $message = '' ) {
  if ( !MYARCADE_DEBUG ) {
    return;
  }

  if ( MYARCADE_DEBUG_IMPORT ) {
    do_action('myarcade_logging', $message);
  }
}

/**
 * Log core.
 *
 * @param string $message Message which should be logged.
 */
function myarcade_log_core ( $message = '' ) {
  if ( !MYARCADE_DEBUG ) {
    return;
  }

  if ( MYARCADE_DEBUG_CORE ) {
    do_action('myarcade_logging', $message);
  }
}

/**
 * Log translator.
 *
 * @param string $message Message which should be logged.
 */
function myarcade_log_translator($message = '') {
  if ( !MYARCADE_DEBUG ) {
    return;
  }

  if ( MYARCADE_DEBUG_TRANSLATOR ) {
    do_action('myarcade_logging', $message);
  }
}
