<?php
/**
 * Plugin Name:  MyArcadePlugin Lite
 * Plugin URI:   https://myarcadeplugin.com
 * Description:  WordPress Arcade Plugin
 * Version:      6.0.0
 * Author:       Daniel Bakovic
 * Author URI:   https://myarcadeplugin.com
 * Requires at least: 5.6
 * Requires PHP: 7.0
 *
 * @package MyArcadePlugin
 */

if ( ! class_exists( 'MyArcadePlugin' ) ) :
	/**
	 * Main MyArcadePlugin Class
	 */
	final class MyArcadePlugin {

		/**
		 * MyArcadePlugin version number
		 *
		 * @var string
		 */
		public $version = '6.0.0';

		/**
		 * A single instance of MyArcadePlugin.
		 *
		 * @var MyArcadePlugin
		 */
		protected static $instance = null;

		/**
		 * Plugin URL.
		 *
		 * @var string
		 */
		public $plugin_url;

		/**
		 * Plugin Path.
		 *
		 * @var string
		 */
		public $plugin_path;

		/**
		 * Plugin basename folder/filename.php.
		 *
		 * @var string
		 */
		public $plugin_basename;

		// Define tables for MyScoresPresenter compatibility.
		// This will be removed after MyScoresPresenter Update.

		/**
		 * Stores the games table name.
		 *
		 * @var string
		 */
		public $game_table;

		/**
		 * Stores the scores table name.
		 *
		 * @var string
		 */
		public $score_table;

		/**
		 * Stores the high scores table name.
		 *
		 * @var string
		 */
		public $highscore_table;

		/**
		 * Stores the user's table name.
		 *
		 * @var string
		 */
		public $user_table;

		/**
		 * Stores the medal table name.
		 *
		 * @var string
		 */
		public $medal_table;

		/**
		 * Stores the game plays table name.
		 *
		 * @var string
		 */
		public $plays_table;

		/**
		 * Main MyArcadePlugin Instance.
		 *
		 * Ensures only one instance of MyArcadePlugin is loaded or can be loaded.
		 *
		 * @see     MyArcade()
		 * @return  Main MyArcadePlugin instance
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Cloning is forbidden.
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?' ), '6.0.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?' ), '6.0.0' );
		}

		/**
		 * MyArcadePlugin Constructor.
		 */
		public function __construct() {

			// Define constants.
			$this->define_constants();

			// Include required files.
			$this->includes();

			// Init hooks.
			$this->init_hooks();
		}

		/**
		 * Define constants
		 *
		 * @access  private
		 */
		private function define_constants() {
			global $wpdb;

			$this->plugin_basename = plugin_basename( __FILE__ );

			$this->define( 'MYARCADE_VERSION', $this->version );

			// Define needed table constants for backward compatibility.
			$this->define( 'MYARCADE_GAME_TABLE', $wpdb->prefix . 'myarcadegames' );
			$this->define( 'MYARCADE_HIGHSCORES_TABLE', $wpdb->prefix . 'myarcadehighscores' );
			$this->define( 'MYARCADE_USER_TABLE', $wpdb->prefix . 'myarcadeuser' );

			$this->game_table      = MYARCADE_GAME_TABLE;
			$this->score_table     = $wpdb->prefix . 'myarcadescores';
			$this->highscore_table = MYARCADE_HIGHSCORES_TABLE;
			$this->user_table      = MYARCADE_USER_TABLE;
			$this->medal_table     = $wpdb->prefix . 'myarcademedals';
			$this->plays_table     = $wpdb->prefix . 'myarcade_plays';

			// Define the plugins abs path.
			$dirname = basename( dirname( __FILE__ ) );
			$this->define( 'MYARCADE_DIR', dirname( __FILE__ ) );
			$this->define( 'MYARCADE_CORE_DIR', MYARCADE_DIR . '/core' );
			$this->define( 'MYARCADE_URL', plugins_url() . '/' . $dirname );

			$this->define( 'MYARCADE_UPDATE_API', 'http://api.myarcadeplugin.com/' );

			$this->define( 'MYARCADE_PLUGIN_FOLDER_NAME', basename( dirname( __FILE__ ) ) );
			$this->define( 'MYARCADE_PLUGIN_SLUG', 'myarcadeplugin-lite' );
		}

		/**
		 * Define constant if not already set.
		 *
		 * @access private
		 * @param  string      $name  Define name.
		 * @param  string|bool $value Define value.
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		private function includes() {

			include_once $this->plugin_path() . '/includes/class-myarcade-autoloader.php';
			require_once $this->plugin_path() . '/includes/class-myarcade-install.php';

			require_once $this->plugin_path() . '/core/schedule.php';
			require_once $this->plugin_path() . '/core/debug.php';
			require_once $this->plugin_path() . '/core/score_handler.php';
			require_once $this->plugin_path() . '/core/template.php';
			require_once $this->plugin_path() . '/core/game.php';
			require_once $this->plugin_path() . '/core/output.php';
			require_once $this->plugin_path() . '/core/user.php';
			require_once $this->plugin_path() . '/core/feedback.php';

			if ( $this->is_request( 'admin' ) ) {
				require_once $this->plugin_path() . '/core/myarcade_admin.php';
			}

			// Do this on the backend and on cron triggers.
			if ( $this->is_request( 'admin' ) || defined( 'MYARCADE_DOING_ACTION' ) || defined( 'DOING_CRON' ) ) {
				require_once $this->plugin_path() . '/core/translate.php';
				require_once $this->plugin_path() . '/core/addgames.php';
				require_once $this->plugin_path() . '/core/file.php';
			}

			if ( $this->is_request( 'frontend' ) ) {
				$this->frontend_includes();
			}

			// Stats / Tracking.
			if ( 'yes' === get_option( 'myarcade_allow_tracking', 'no' ) ) {
				if ( $this->is_request( 'cron' ) ) {
					// Include on on cron jobs.
					include_once $this->plugin_path() . '/includes/class-myarcade-tracker.php';
				}

				if ( ! $this->is_request( 'admin' ) ) {
					include_once $this->plugin_path() . '/includes/class-myarcade-stats-aggregator.php';
				}

				// Include ajax handler only when doing an ajax request.
				if ( $this->is_request( 'ajax' ) ) {
					include_once $this->plugin_path() . '/includes/class-myarcade-stats-ajax.php';
				}
			}
		}

		/**
		 * Includes required for frontend only
		 */
		private function frontend_includes() {
			require_once $this->plugin_path() . '/includes/class-myarcade-session.php';
		}

		/**
		 * Init required actions and filters
		 *
		 * @access private
		 */
		private function init_hooks() {
			register_activation_hook( __FILE__, array( 'MyArcade_Install', 'plugin_activation' ) );
			register_deactivation_hook( __FILE__, array( 'MyArcade_Install', 'plugin_deactivation' ) );

			add_action( 'init', array( $this, 'init' ) );
			add_action( 'wp_ajax_myarcade_import_handler', array( $this, 'import_handler' ) );

			if ( $this->is_network_activated() ) {
				if ( is_main_site() ) {
					add_action( 'network_admin_notices', 'myarcade_notices' );
				}
			} else {
				add_action( 'admin_notices', 'myarcade_notices' );
			}
		}

		/**
		 * Init when WordPress initializes.
		 */
		public function init() {

			// Set up localisation.
			load_plugin_textdomain( 'myarcadeplugin', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
		}

		/**
		 * Locate and include distributor's integration file
		 *
		 * @param string $key Distributors key/slug.
		 */
		public function load_distributor( $key ) {

			$distributor_file = apply_filters( 'myarcade_distributor_integration', $this->plugin_path() . '/core/feeds/' . $key . '.php', $key );

			if ( file_exists( $distributor_file ) ) {
				include_once $distributor_file;
			}
		}

		/**
		 * Get distributor's settings.
		 *
		 * @param  string $key Distributors key/slug.
		 * @return array       Settings.
		 */
		public function get_settings( $key ) {

			$settings = get_option( 'myarcade_' . $key );

			if ( ! $settings ) {
				// No settings found. Check if distirbutor settings have been requested.
				$distributors = MyArcade()->distributors();

				if ( array_key_exists( $key, $distributors ) ) {
					// Default settings function.
					$settings_function = 'myarcade_default_settings_' . $key;

					if ( function_exists( $settings_function ) ) {
						$settings = $settings_function();
					} else {
						// Function doesn't exist. Try to find the distributor integration file.
						$distributor_file = apply_filters( 'myarcade_distributor_integration', MYARCADE_CORE_DIR . '/feeds/' . $key . '.php', $key );

						if ( file_exists( $distributor_file ) ) {
							include_once $distributor_file;

							if ( function_exists( $settings_function ) ) {
								$settings = $settings_function();
							}
						}
					}
				}
			}

			if ( ! $settings ) {
				$settings = array();
			}

			return $settings;
		}

		/**
		 * Set default game distributors
		 *
		 * @return array Array of supported game distributors
		 */
		public function distributors() {
			return apply_filters(
				'myarcade_game_distributors',
				array(
					'famobi'           => 'Famobi',
					'gamedistribution' => 'GameDistribution',
					'gamemonetize'     => 'GameMonetize',
					'gamepix'          => 'GamePix',
					'myarcadefeed'     => 'MyArcadeFeed',
					'softgames'        => 'Softgames',
					'fourj'            => '4J (Pro)',
					'fourjrevshare'    => '4J (Revenue Share) (Pro)',
					'gamearter'        => 'GameArter (Pro)',
					'htmlgames'        => 'HTML Games (Pro)',
					'kongregate'       => 'Kongregate (Pro)',
					'wanted5games'     => 'Wanted 5 Games (Pro)',
				)
			);
		}

		/**
		 * Set default custom game types
		 *
		 * @return array Array of custom game types.
		 */
		public function custom_game_types() {
			return apply_filters(
				'myarcade_import_methods',
				array(
					'embed'     => __( 'Embed Code', 'myarcadeplugin' ),
					'custom'    => __( 'Flash (SWF)', 'myarcadeplugin' ),
					'html5'     => __( 'HTML5 Game', 'myarcadeplugin' ),
					'ibparcade' => __( 'IBPArcade Game', 'myarcadeplugin' ),
					'iframe'    => __( 'Iframe URL', 'myarcadeplugin' ),
					'phpbb'     => __( 'PHPBB Game', 'myarcadeplugin' ),
					'dcr'       => __( 'Shochwave (DCR)', 'myarcadeplugin' ),
					'unity'     => __( 'Unity', 'myarcadeplugin' ),
				)
			);
		}

		/**
		 * Get the current game post type.
		 *
		 * @return string Post type.
		 */
		public function get_post_type() {

			$general = get_option( 'myarcade_general' );

			if ( 'post' !== $general['post_type'] && post_type_exists( $general['post_type'] ) ) {
				$post_type = $general['post_type'];
			} else {
				$post_type = 'post';
			}

			return $post_type;
		}

		/**
		 * Load game import handler
		 */
		public function import_handler() {
			require_once $this->plugin_path() . '/core/admin/import_handler.php';
		}

		/**
		 * Get MyArcade upload directories
		 *
		 * @return array Upload directories (absolute and url).
		 */
		public function upload_dir() {

			$wp_upload_dir = wp_upload_dir();
			$games_base    = 'games';
			$thumbs_base   = 'thumbs';

			/**
			 * Example
			 * 'basedir' => string 'C:\xampp\htdocs\myarcadeplugin5/wp-content/uploads/'
			 * 'baseurl' => string 'http://myarcadeplugin5.loc/wp-content/uploads/'
			 * 'gamesdir' => string 'C:\xampp\htdocs\myarcadeplugin5/wp-content/uploads/games/'
			 * 'gamesurl' => string 'http://myarcadeplugin5.loc/wp-content/uploads/games/'
			 * 'gamesbase' => string 'uploads/games/'
			 * 'thumbsdir' => string 'C:\xampp\htdocs\myarcadeplugin5/wp-content/uploads/thumbs/'
			 * 'thumbsurl' => string 'http://myarcadeplugin5.loc/wp-content/uploads/thumbs/'
			 * 'thumbsbase' => string 'uploads/thumbs/'
			 */

			$upload_dir = apply_filters(
				'myarcade_upload_dir',
				array(
					'basedir'    => $wp_upload_dir['basedir'] . '/',
					'baseurl'    => $wp_upload_dir['baseurl'] . '/',
					'gamesdir'   => $wp_upload_dir['basedir'] . '/' . $games_base . '/',
					'gamesurl'   => $wp_upload_dir['baseurl'] . '/' . $games_base . '/',
					'gamesbase'  => basename( $wp_upload_dir['baseurl'] ) . '/' . $games_base . '/',
					'thumbsdir'  => $wp_upload_dir['basedir'] . '/' . $thumbs_base . '/',
					'thumbsurl'  => $wp_upload_dir['baseurl'] . '/' . $thumbs_base . '/',
					'thumbsbase' => basename( $wp_upload_dir['baseurl'] ) . '/' . $thumbs_base . '/',
				)
			);

			// Create directories if necessary.
			wp_mkdir_p( $upload_dir['gamesdir'] );
			wp_mkdir_p( $upload_dir['thumbsdir'] );

			return $upload_dir;
		}

		/**
		 * Check if MyArcadePlugin is activated for network
		 *
		 * @return  bool TRUE if activated for network
		 */
		public function is_network_activated() {
			if ( is_multisite() ) {
				if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
					require_once ABSPATH . '/wp-admin/includes/plugin.php';
				}

				if ( is_plugin_active_for_network( $this->plugin_basename ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * What type of request is this?
		 *
		 * @param string $type ajax, frontend, admin or myarcade.
		 */
		public function is_request( $type ) {
			switch ( $type ) {
				case 'admin':
					return is_admin();
				case 'ajax':
					return defined( 'DOING_AJAX' ) ? DOING_AJAX : false;
				case 'cron':
					return defined( 'DOING_CRON' ) ? DOING_CRON : false;
				case 'frontend':
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
				case 'myarcade':
					return defined( 'MYARCADE_DOING_ACTION' ) ? MYARCADE_DOING_ACTION : false;
			}
		}

		/**
		 * Get the plugin url.
		 *
		 * @return string Plugin URL.
		 */
		public function plugin_url() {
			if ( ! $this->plugin_url ) {
				$this->plugin_url = plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) );
			}

			return $this->plugin_url;
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path() {

			if ( ! $this->plugin_path ) {
				$this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
			}

			return $this->plugin_path;
		}
	}
endif;

/**
 * Keep for backward compatibility.
 * Used by MyArcadeFeed
 */
function myarcade_init() {}

/**
 * MyArcadePlugin Premium Hint
 *
 */
function myarcade_premium_img() {
  echo '<img src="'.MYARCADE_URL.'/assets/images/locked.png" alt="Pro Version Only!" title="Pro Version Only!" />';
}

/**
 * Upgrade to premium message
 *
 * @param   boolean $alert
 */
function myarcade_premium_message( $alert = true ) {
  if ( $alert ) {
    echo '<div class="mabp_error">';
  }

  echo '<p>Get <a href="https://myarcadeplugin.com/buy" target="_blank">MyArcadePlugin Pro</a> to enable this feature. Your <strong>20% off</strong> coupon code: "<strong>upgrademylite</strong>".</p>';

  if ( $alert ) {
    echo "</div>";
  }
}

function myarcade_premium_span( $color = 'yellow' ) {
  echo '<span style="color:'.$color.'"">PRO FEATURE</span> - ';
}

/**
 * Returns the main instance of MyArcade to prevent the need to use globals.
 *
 * @return object MyArcadePlugin
 */
function MyArcade() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return MyArcadePlugin::instance();
}

MyArcade();
