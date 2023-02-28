<?php
/**
 * Stats reports page
 */
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

class MyArcade_Admin_Reports {

  /**
   * Generate the stats page output with charts and data
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
   * @access  public
   * @return  void
   */
  public static function output() {

    // Load required scripts
    self::load_scripts();

    wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );

    self::register_meta_boxes();
    ?>
    <div class="metabox-holder" id="overview-widgets">
      <div class="postbox-container" id="myarcade-postbox-container-1">
        <?php do_meta_boxes( 'myarcade_stats_metaboxes', 'side', '' ); ?>
      </div>

      <div class="postbox-container" id="myarcade-postbox-container-2">
        <?php do_meta_boxes( 'myarcade_stats_metaboxes', 'normal', '' ); ?>
      </div>
    </div>
    <div style="clear:both"></div>
    <script type="text/javascript">
      jQuery(document).ready(function () {
        // postboxes setup
        postboxes.add_postbox_toggles( 'myarcade_stats_metaboxes' );
      });
    </script>
    <?php
  }

  /**
   * Load required scripts for graphs and dashboard boxes
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
   * @access  private
   * @return  void
   */
  private static function load_scripts() {
    wp_enqueue_script( 'common' );
    wp_enqueue_script( 'wp-lists' );
    wp_enqueue_script( 'postbox' );

    $backend_script_path = str_replace( array( 'http:', 'https:' ), '', MYARCADE_URL . '/assets/' );

    wp_register_script( 'myarcade-stats-backend', $backend_script_path . 'js/myarcade-stats-backend.js', array( 'jquery' ), MYARCADE_VERSION, true );
    wp_enqueue_script( 'myarcade-stats-backend' );

    wp_enqueue_style( 'jqplot-css', $backend_script_path . 'js/jqplot/jquery.jqplot.min.css', true, '1.0.9' );

    // Load the charts code.
    wp_enqueue_script( 'jqplot', $backend_script_path . 'js/jqplot/jquery.jqplot.min.js', true, '1.0.9' );
    wp_enqueue_script( 'jqplot-daterenderer', $backend_script_path . 'js/jqplot/plugins/jqplot.dateAxisRenderer.min.js', true, '1.0.9' );
    wp_enqueue_script( 'jqplot-tickrenderer', $backend_script_path . 'js/jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js', true, '1.0.9' );
    wp_enqueue_script( 'jqplot-axisrenderer', $backend_script_path . 'js/jqplot/plugins/jqplot.canvasAxisLabelRenderer.min.js', true, '1.0.9' );
    wp_enqueue_script( 'jqplot-textrenderer', $backend_script_path . 'js/jqplot/plugins/jqplot.canvasTextRenderer.min.js', true, '1.0.9' );
    wp_enqueue_script( 'jqplot-tooltip', $backend_script_path . 'js/jqplot/plugins/jqplot.highlighter.min.js', true, '1.0.9' );
    wp_enqueue_script( 'jqplot-donutrenderer', $backend_script_path . 'js/jqplot/plugins/jqplot.donutRenderer.min.js', true, '1.0.9' );
  }

  /**
   * Register available postbox/stats widgets
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
   * @access  public
   * @return  void
   */
  private static function register_meta_boxes() {

    // Left Sidebar

    add_meta_box(
      'myarcade_summary_postbox',
      __("Summary", 'myarcadeplugin'),
      array(__CLASS__, 'generate_postbox_content' ),
      'myarcade_stats_metaboxes',
      'side',
      null,
      array( 'widget' => 'summary' )
    );

    add_meta_box(
      'myarcade_top_games_postbox',
      __("Top Games off All Time", 'myarcadeplugin'),
      array(__CLASS__, 'generate_postbox_content' ),
      'myarcade_stats_metaboxes',
      'side',
      null,
      array( 'widget' => 'top-games' )
    );

    add_meta_box(
      'myarcade_user_ratio_postbox',
      __("User Ratio (Last 30 Days)", 'myarcadeplugin'),
      array(__CLASS__, 'generate_postbox_content' ),
      'myarcade_stats_metaboxes',
      'side',
      null,
      array( 'widget' => 'user-ratio' )
    );

    add_meta_box(
      'myarcade_top_users_postbox',
      __("Most Active Users", 'myarcadeplugin'),
      array(__CLASS__, 'generate_postbox_content' ),
      'myarcade_stats_metaboxes',
      'side',
      null,
      array( 'widget' => 'top-users' )
    );

    add_meta_box(
      'myarcade_unpopular_games_postbox',
      __("Unpopular Games", 'myarcadeplugin'),
      array(__CLASS__, 'generate_postbox_content' ),
      'myarcade_stats_metaboxes',
      'side',
      null,
      array( 'widget' => 'unpopular-games' )
    );

    add_meta_box(
      'myarcade_unplayed_games_postbox',
      __("Currently Unplayed Games", 'myarcadeplugin'),
      array(__CLASS__, 'generate_postbox_content' ),
      'myarcade_stats_metaboxes',
      'side',
      null,
      array( 'widget' => 'unplayed-games' )
    );

    // Main Widget Area

    add_meta_box(
      'myarcade_game_plays_postbox',
      __("Game Plays", 'myarcadeplugin'),
      array(__CLASS__, 'generate_postbox_content' ),
      'myarcade_stats_metaboxes',
      'normal',
      null,
      array( 'widget' => 'plays' )
    );

    add_meta_box(
      'myarcade_latest_plays_postbox',
      __("Latest Game Plays", 'myarcadeplugin'),
      array(__CLASS__, 'generate_postbox_content' ),
      'myarcade_stats_metaboxes',
      'normal',
      null,
      array( 'widget' => 'latest-plays' )
    );

    add_meta_box(
      'myarcade_game_plays_hourly_postbox',
      __("Hourly Game Plays", 'myarcadeplugin'),
      array(__CLASS__, 'generate_postbox_content' ),
      'myarcade_stats_metaboxes',
      'normal',
      null,
      array( 'widget' => 'plays-hourly' )
    );
  }

  /**
   * Generate the postbox content
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
   * @access  public
   * @param   string $post Unused
   * @param   array $args Widget parameters
   * @return  void
   */
  public static function generate_postbox_content( $post, $args ) {

    // Set the loading image
    $loading_img = '<div style="width: 100%; text-align: center;"><img src=" ' . MYARCADE_URL . '/assets/images/loading.gif" alt="' . __( 'Loading...', 'myarcadeplugin' ) . '"></div>';
    // Generate the container id
    $container_id = str_replace( '.', '_', $args['args']['widget'] . '_postbox' );

    if ( ! $container_id ) {
      return;
    }

    // Echo the placeholder div
    echo '<div id="' . $container_id . '">' . $loading_img . '</div>';

    // Now we can load the widget content with javascript
    ?>
    <script type="text/javascript">
      jQuery(document).ready( function($) {
        myarcade_stats_get_widget_content( '<?php echo esc_attr( $args['args']['widget'] ); ?>', '<?php echo esc_attr( $container_id ); ?>' );
      });
    </script>
    <?php
  }
}