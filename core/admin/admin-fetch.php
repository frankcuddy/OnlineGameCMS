<?php
/**
 * Displays the fetch games page on backend
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 */

// No direct Access
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Generates the Fetch Games page
 *
 * @version 5.19.0
 * @access  public
 * @return  void
 */
function myarcade_fetch() {

  myarcade_header();

  ?>
  <div class="icon32" id="icon-plugins"><br/></div>
  <h2><?php _e("Fetch Games", 'myarcadeplugin'); ?></h2>

  <?php
  // Set default distributor
  $distributor = filter_input( INPUT_POST, 'distr', FILTER_UNSAFE_RAW, array( "options" => array( "default" => 'gamepix') ) );

  // Remove distributors from which we can't fetch games
  $distributors = apply_filters( 'myarcade_distributors_can_fetch', MyArcade()->distributors() );
  ?>
  <style type="text/css">.hide{display:none}</style>
  <br />
  <form method="post" class="myarcade_form">
    <input type="hidden" name="fetch" value="start" />
    <fieldset>
      <div class="myarcade_border grey" style="width:685px">
        <label for="distr"><?php _e("Select a game distributor", 'myarcadeplugin'); ?>: </label>
        <select name="distr" id="distr">
          <?php foreach ( $distributors as $slug => $name ) : ?>
          <option value="<?php echo esc_attr( $slug ); ?>" <?php myarcade_selected( $distributor, $slug ); ?>><?php echo esc_html( $name ); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <?php $keyword_filter = filter_input( INPUT_POST, 'keyword_filter' ); ?>
      <div class="myarcade_border white mabp_680">
        <label for="keyword_filter"><?php _e("Keyword", 'myarcadeplugin'); ?>: </label>
        <input type="text" name="keyword_filter" id="keyword_filter" value="<?php echo esc_attr( $keyword_filter ); ?>" /> <i><?php _e("Add only games that contain the given keyword in title or description.", 'myarcadeplugin'); ?></i>
      </div>

      <?php
      // Load fetch options for each distributor dynamically
      foreach ($distributors as $key => $name) {
        $settings_function = 'myarcade_fetch_settings_' . $key;

        // Get distributor integration file
        MyArcade()->load_distributor( $key );

        // Check if settings function exists
        if ( function_exists( $settings_function ) ) {
          $settings_function();
        }
        else {
          // Settings function doesn't exist.
          myarcade_no_fetching_options( $key );
        }
      }
      ?>
    </fieldset>

    <?php
    $myarcade_categories = get_option('myarcade_categories');
    if ( empty( $myarcade_categories ) ) {
      echo '<p class="mabp_error mabp_800">'.__("You will not be able to fetch games!", 'myarcadeplugin').' '.__('Navigate to "General Settings" and activate some game categories!', 'myarcadeplugin').'</p>';
    }
    else {
      echo '<input class="button-primary" id="submit" type="submit" name="submit" value="'. __("Fetch Games", 'myarcadeplugin').'" />';
    }
    ?>
  </form>

  <br />

  <?php
  if ( 'start' == filter_input( INPUT_POST, 'fetch' ) ) {
    // Start fetching here...
    if ( $distributor ) {
      myarcade_prepare_environment();

      // Generate fetch games function name
      $fetch_function = 'myarcade_feed_' . $distributor;

      // Get distributor integration file
      MyArcade()->load_distributor( $distributor );

      // Add filter query to distributor's settings. Generate a preg_match pattern
      $keyword_array = array_map( 'trim', explode(',', strtolower( $keyword_filter ) ) );
      $pattern = implode( '|', $keyword_array );

      if ( $pattern ) {
        $settings['keyword_filter'] = '[' . $pattern . ']';
      }
      else {
        $settings['keyword_filter'] = false;
      }

      $args = array( 'echo' => true, 'settings' => $settings );

      if ( function_exists( $fetch_function ) ) {
        $fetch_function($args);
      }
      else {
        ?>
        <p class="mabp_error mabp_680">
          <?php printf( __("ERROR: Required distributor file can't be found: %s!", 'myarcadeplugin'), $distributor); ?>
        </p>
        <?php
      }
    }
    else {
      ?>
      <p class="mabp_error">
        <?php _e("ERROR: Unknown game distributor!", 'myarcadeplugin'); ?>
      </p>
      <?php
    }
  }

  myarcade_footer();
}

/**
 * Display a default message if there are no fetching options available
 *
 * @version 5.19.0
 * @since   5.19.0
 * @access  public
 * @return  void
 */
function myarcade_no_fetching_options( $key ) {
  ?>
  <div class="myarcade_border white hide mabp_680" id="<?php echo esc_attr( $key ); ?>">
    <p class="mabp_info">
      <?php _e("There are no specific settings available. Just Fetch the games :)", 'myarcadeplugin');?>
    </p>
  </div>
  <?php
}

/**
 * Remove distributors we can't fetch games from
 *
 * @version 5.29.0
 * @since   5.19.0
 * @access  public
 * @param   array $distributors Available game distributors
 * @return  array
 */
function myarcade_distributors_can_fetch( $distributors = array() ) {

  unset( $distributors['playtomax'] );
  unset( $distributors['plinga'] );
  unset( $distributors['scirra'] );
  unset( $distributors['agf'] );
  unset( $distributors['fog'] );

  return $distributors;
}
add_filter( 'myarcade_distributors_can_fetch', 'myarcade_distributors_can_fetch' );
