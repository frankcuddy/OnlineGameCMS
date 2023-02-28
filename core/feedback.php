<?php
/**
 * Feedback class to store errors and notifications
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
*/

class MyArcade_Feedback {

  // Stores the list of errors
  var $errors = array();

  // Stores the list of messages
  var $messages = array();

  /**
   * Constructor
   *
   * @version 5.13.0
   * @access  public
   * @param   string  $type    message or error
   * @param   string  $message Message text
   */
  public function __construct( $type = '', $message = '' ) {

    switch ($type) {

      case 'message': {
        $this->messages[] = $message;
      } break;

      case 'error': {
        $this->errors[] = $message;
      } break;

      default: {
        return;
      }
    }
  }

  /**
   * Retrieve all error messages. Returns an array, string or outputs all error messages
   *
   * @version 5.13.0
   * @access  public
   * @param   array  $args
   * @return  string
   */
  public function get_errors( $args = array() ) {

    $defaults = array(
      'wrap_begin' => '<p class="mabp_error">',
      'wrap_end'   => '</p>',
      'output'     => 'return'
    );

    $r = wp_parse_args( $args, $defaults );
    extract($r);

    if ( !is_bool($output) && ($output == 'return') ) {
      return $this->errors;
    }

    $output_string = '';

    if ( $this->has_errors() ) {
      foreach ( $this->errors as $message ) {
        $output_string .= $wrap_begin.$message.$wrap_end;
      }

      if ( ( is_bool($output) && ($output === true) ) || ($output == 'echo' ) ) {
        echo esc_html( $output_string );
      }
      elseif ( $output == 'string') {
        return $output_string;
      }
    }
  }

  /**
   * Retrieve all messages. Returns an array, string or outputs all error messages
   *
   * @version 5.13.0
   * @access  public
   * @param   array  $args
   * @return  string
   */
  public function get_messages( $args = array() ) {

    $defaults = array(
      'wrap_begin' => '<p class="mabp_info">',
      'wrap_end'   => '</p>',
      'output'     => 'return',
    );

    $r = wp_parse_args( $args, $defaults );
    extract($r);

    if ( !is_bool($output) && ($output == 'return') ) {
      return $this->messages;
    }

    $output_string = '';

    if ( $this->has_messages() ) {
      foreach ( $this->messages as $message ) {
        $output_string .= $wrap_begin.$message.$wrap_end;
      }

      if ( ( is_bool($output) && ($output === true) ) || ($output == 'echo') ) {
        echo esc_hmtl( $output_string );
      }
      elseif ( $output == 'string') {
        return $output_string;
      }
    }
    else {
      return false;
    }
  }

  /**
   * Add a new error message
   *
   * @version 5.13.0
   * @access  public
   * @param   string $message Error message
   */
  function add_error( $message ) {
    $this->errors[] = $message;
  }

  /**
   * Add a new message
   *
   * @version 5.13.0
   * @access  public
   * @param   string $message Message string
   */
  function add_message($message) {
    $this->messages[] = $message;
  }

  /**
   * Check if there are error messages available
   *
   * @version 5.13.0
   * @access  public
   * @return  boolean TRUE if there are error messages available
   */
  function has_errors() {
    if ( empty($this->errors) ) {
      return false;
    }
    else {
      return true;
    }
  }

  /**
   * Check if there are messages available
   *
   * @version 5.13.0
   * @access  public
   * @return  boolean TRUE if there are messages available
   */
  function has_messages() {
    if ( empty($this->messages) ) {
      return false;
    }
    else {
      return true;
    }
  }
}

/**
 * Check wheather the variable is a MyArcadePlugin feedback object
 *
 * @version 5.13.0
 * @access  public
 * @param   object $thing MyArcade_Feedback object
 * @return  boolean TRUE if thing is a MyArcade_Feedback object
 */
function is_myarcade_feedback( $thing ) {
  if ( is_object($thing)  && is_a($thing, 'MyArcade_Feedback') ) {
    return true;
  }
  else {
    return false;
  }
}

// Create a new Feedback instance
global $myarcade_feedback;
if ( !is_myarcade_feedback($myarcade_feedback) ) {
  $myarcade_feedback = new MyArcade_Feedback();
}
?>