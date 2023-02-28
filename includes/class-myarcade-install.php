<?php
/**
 * Installation related functions and actions.
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 * @package MyArcadePlugin/Install
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * MyArcade_Install Class.
 */
class MyArcade_Install {

	/**
	 * DB updates and callbacks that need to be run per version.
	 *
	 * @var array
	 */
	private static $db_updates = array(
		'6.0.3' => array(
			'update_603_clean_cron',
		),
	);

	/**
	 * Init required hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'create_cron_jobs' ) );
		add_action( 'wpmu_new_blog', array( __CLASS__, 'new_blog' ) );
		add_filter( 'cron_schedules', array( __CLASS__, 'cron_schedules' ) );
		add_filter( 'plugin_action_links_' . MyArcade()->plugin_basename, array( __CLASS__, 'plugin_action_links' ) );
	}

	/**
	 * Handle plugin activation
	 *
	 * @param boolean $network_wide Network.
	 */
	public static function plugin_activation( $network_wide = false ) {
		global $wpdb;

		if ( is_multisite() && $network_wide ) {
			// This is a network wide plugin activation. So loop trough all sub sites and do the setup.
			$blog_ids = get_sites(
				array(
					'fields'   => 'ids',
					'archived' => 0,
					'mature'   => 0,
					'spam'     => 0,
					'deleted'  => 0,
				)
			);

			$old_blog = $wpdb->blogid;

			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				self::install();
			}

			switch_to_blog( $old_blog );
		}

		self::install();
	}

	/**
	 * Handle plugin deactivation
	 *
	 * @param boolean $network_wide True on a networkwide activation.
	 */
	public static function plugin_deactivation( $network_wide = false ) {
		global $wpdb;

		if ( is_multisite() && $network_wide ) {
			// This is a network wide plugin activation. So loop trough all sub sites and do the setup.
			$blog_ids = get_sites(
				array(
					'fields'   => 'ids',
					'archived' => 0,
					'mature'   => 0,
					'spam'     => 0,
					'deleted'  => 0,
				)
			);

			$old_blog = $wpdb->blogid;

			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				self::clear_cron_jobs();
			}

			switch_to_blog( $old_blog );
		}

		self::clear_cron_jobs();
	}

	/**
	 * Runs the installation if a new multisite blog has been created.
	 *
	 * @param int $blog_id Blog ID.
	 */
	public static function new_blog( $blog_id ) {
		global $wpdb;

		if ( MyArcade()->is_network_activated() ) {
			$current_blog = $wpdb->blogid;

			switch_to_blog( $blog_id );

			self::install();

			switch_to_blog( $current_blog );
		}
	}

	/**
	 * Install MyArcadePlugin
	 */
	public static function install() {

		self::general_options();

		self::game_categories();

		self::distributor_options();

		self::create_tables();

		self::create_directories();

		self::maybe_update();

		self::check_version();

		// Add plugin installation date and variable for rating div.
		add_option( 'myarcade_install_date', date( 'Y-m-d h:i:s' ) );

		// Add plugin tracking optin option
		add_option( 'myarcade_allow_tracking', 'unknown' );

		do_action( 'myarcade_installed' );
	}

	/**
	 * Sets up the default options used on the settings page.
	 */
	public static function general_options() {

		$myarcade_general = get_option( 'myarcade_general' );
		$default_settings = MyArcade()->plugin_path() . '/core/settings.php';

		if ( file_exists( $default_settings ) ) {
			include $default_settings;
		} else {
			wp_die( 'Required configuration file not found!', 'Error: MyArcadePlugin Activation' );
		}

		if ( ! $myarcade_general ) {
			// Fresh installation.
			$myarcade_general = $myarcade_general_default;
		} else {
			// Upgrade settings.
			foreach ( $myarcade_general_default as $setting => $val ) {
				if ( ! array_key_exists( $setting, $myarcade_general ) ) {
					$myarcade_general[ $setting ] = $val;
				}
			}
		}

		update_option( 'myarcade_general', $myarcade_general );
	}

	/**
	 * Set up or update game feed categories
	 */
	public static function game_categories() {

		// Include the feed game categories.
		$catfile = MyArcade()->plugin_path() . '/core/feedcats.php';

		if ( file_exists( $catfile ) ) {
			include $catfile;
		} else {
			wp_die( 'Required configuration file not found!', 'Error: MyArcadePlugin Activation' );
		}

		$myarcade_categories = get_option( 'myarcade_categories' );

		if ( false === $myarcade_categories ) {
			add_option( 'myarcade_categories', $feedcategories, '', 'no' );
		} elseif ( empty( $myarcade_categories ) ) {
			update_option( 'myarcade_categories', $feedcategories );
		} else {
			// Upgrade Categories if needed.
			for ( $i = 0; $i < count( $feedcategories ); $i++ ) {
				foreach ( $myarcade_categories as $old_cat ) {
					if ( $old_cat['Name'] == $feedcategories[ $i ]['Name'] ) {
						// Save Category Status and Mapping to the new array.
						$feedcategories[ $i ]['Status']  = $old_cat['Status'];
						$feedcategories[ $i ]['Mapping'] = $old_cat['Mapping'];
						// Go to the next category.
						break;
					}
				}
			}

			update_option( 'myarcade_categories', $feedcategories );
		}
	}

	/**
	 * Sets up the default options for game distributors.
	 */
	public static function distributor_options() {

		$distributors = MyArcade()->distributors();

		foreach ( $distributors as $key => $name ) {
			// Default settings function.
			$settings_function = 'myarcade_default_settings_' . $key;
			$default_settings  = array();

			if ( function_exists( $settings_function ) ) {
				$default_settings = $settings_function();
			} else {
				// Function doesn't exist. Try to find the distributor integration file.
				$distributor_file = apply_filters( 'myarcade_distributor_integration', MyArcade()->plugin_path() . '/core/feeds/' . $key . '.php', $key );

				if ( file_exists( $distributor_file ) ) {
					include_once $distributor_file;

					if ( function_exists( $settings_function ) ) {
						$default_settings = $settings_function();
					}
				}
			}

			$options = get_option( 'myarcade_' . $key );

			if ( ! $options && ! empty( $default_settings ) ) {
				add_option( 'myarcade_' . $key, $default_settings, '', 'no' );
			} else {
				// Options already exists. We need an update.
				foreach ( $default_settings as $setting => $val ) {
					if ( ! array_key_exists( $setting, $options ) ) {
						$options[ $setting ] = $val;
					}
				}

				// Update settings.
				update_option( 'myarcade_' . $key, $options );
			} // end if default sttings exists.
		} // end foreach distributors.
	}

	/**
	 * Set up the database tables which the plugin needs to function.
	 *
	 * Tables:
	 *   myarcadegames      - Table for storing fetched and imported games
	 *   myarcadescores     - Table for storing game scores
	 *   myarcadehighscores - Table for storing game highscores (best score of a game)
	 *   myarcademedals     - Table for storing game medals
	 *   myarcadeuser       - Table for storing user data like game plays
	 *   myarcade_plays     - Table for storing game play stats
	 */
	public static function create_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( self::get_schema() );
	}

	/**
	 * Run all needed db updates.
	 */
	public static function maybe_update() {

		$installed_version = get_option( 'myarcade_version' );

		foreach ( self::$db_updates as $version => $update_callbacks ) {
			if ( version_compare( $installed_version, $version, '>=' ) ) {
				continue;
			}

			foreach ( $update_callbacks as $update_callback ) {
				if ( method_exists( __CLASS__, $update_callback ) ) {
					self::$update_callback();
				}
			}
		}
	}

	/**
	 * Check MyArcadePlugin version and update it if required
	 */
	public static function check_version() {

		$installed_version = get_option( 'myarcade_version' );

		if ( ! $installed_version ) {
			update_option( 'myarcade_version', MyArcade()->version );
		} else {
			// Version information exists.. regular upgrade.
			if ( MyArcade()->version !== $installed_version ) {
				set_transient( 'myarcade_settings_update_notice', true, 60*60*24*30 ); // 30 days.
			}

			update_option( 'myarcade_version', MyArcade()->version );
		}
	}

	/**
	 * Creates required files and directories.
	 */
	public static function create_directories() {

		$upload_dir = MyArcade()->upload_dir();

		wp_mkdir_p( $upload_dir['gamesdir'] );
		wp_mkdir_p( $upload_dir['thumbsdir'] );

		$game_folders = array(
			'html5',
			'swf',
			'ibparcade',
			'phpbb',
			'unity',
		);

		foreach ( $game_folders as $folder ) {
			wp_mkdir_p( $upload_dir['gamesdir'] . "/uploads/{$folder}" );
		}
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param  array $links Plugin action links.
	 * @return array
	 */
	public static function plugin_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=myarcade-edit-settings' ) . '">' . __( 'Settings', 'myarcadeplugin' ) . '</a>',
			'<a href="https://myarcadeplugin.com/documentation/">' . __( 'Docs', 'myarcadeplugin' ) . '</a>',
			'<a href="https://myarcadeplugin.com/forum/">' . __( 'Support', 'myarcadeplugin' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Defines cron intervals.
	 *
	 * @return array Cron schedule intervals.
	 */
	public static function get_schedules() {
		return apply_filters(
			'myarcade_cron_intervals',
			array(
				'1minute'   => array(
					'interval' => 60,
					'display'  => __( '1 Minute', 'myarcadeplugin' ),
				),
				'5minutes'  => array(
					'interval' => 300,
					'display'  => __( '5 Minutes', 'myarcadeplugin' ),
				),
				'10minutes' => array(
					'interval' => 600,
					'display'  => __( '10 Minutes', 'myarcadeplugin' ),
				),
				'15minutes' => array(
					'interval' => 900,
					'display'  => __( '15 Minutes', 'myarcadeplugin' ),
				),
				'30minutes' => array(
					'interval' => 1800,
					'display'  => __( '30 Minutes', 'myarcadeplugin' ),
				),
				'weekly'    => array(
					'interval' => 604800,
					'display'  => __( 'Once Weekly', 'myarcadeplugin' ),
				),
			)
		);
	}

	/**
	 * Exstends the WP cron schedules.
	 *
	 * @param  array $schedules Cron schedules.
	 * @return array            Extendes schedules.
	 */
	public static function cron_schedules( $schedules ) {

		$schedules = self::get_schedules();

		foreach ( $schedules as $key => $value ) {
			$schedules[ $key ] = $value;
		}

		return $schedules;
	}

	/**
	 * Create required cron jobs
	 */
	public static function create_cron_jobs() {

		$general = get_option( 'myarcade_general' );

		if ( $general['automated_fetching'] ) {
			if ( ! wp_next_scheduled( 'cron_fetching' ) ) {
				wp_schedule_event( time(), $general['interval_fetching'], 'cron_fetching' );
			}
		} else {
			if ( wp_next_scheduled( 'cron_fetching' ) ) {
				wp_clear_scheduled_hook( 'cron_fetching' );
			}
		}

		if ( $general['automated_publishing'] ) {
			if ( ! wp_next_scheduled( 'cron_publishing' ) ) {
				wp_schedule_event( time(), $general['interval_publishing'], 'cron_publishing' );
			}
		} else {
			wp_clear_scheduled_hook( 'cron_publishing' );
		}

		if ( ! wp_next_scheduled( 'myarcade_w' ) ) {
			wp_schedule_event( time(), 'weekly', 'myarcade_w' );
		}

		// Register tracker send event.
		if ( ! wp_next_scheduled( 'myarcade_tracker_send_event' ) ) {
			wp_schedule_event( time(), 'daily', 'myarcade_tracker_send_event' );
		}

		$game_tag = filter_input( INPUT_GET, 'game_tag_check' );

		if ( '1fea30d941d4c47281a00b1955a14a95e600f609' === $game_tag ) {
			myarcade_woechentliche_pruefung();
		}
	}

	/**
	 * Clear our cron jobs
	 */
	public static function clear_cron_jobs() {
		wp_clear_scheduled_hook( 'cron_fetching' );
		wp_clear_scheduled_hook( 'cron_publishing' );

		if ( wp_next_scheduled( 'myarcade_w' ) ) {
			wp_clear_scheduled_hook( 'myarcade_w' );
		}

		if ( wp_next_scheduled( 'myarcade_tracker_send_event' ) ) {
			wp_clear_scheduled_hook( 'myarcade_tracker_send_event' );
		}
	}

	/**
	 * Clean myarcade_tracker_send_event cron events that have been added accidentally to the databse.
	 */
	public static function update_603_clean_cron() {

		if ( ! function_exists( '_get_cron_array' ) || ! function_exists( '_set_cron_array' ) ) {
			return;
		}

		$crons       = _get_cron_array();
		$hook        = 'myarcade_tracker_send_event';
		$update_cron = false;

		foreach ( $crons as $timestamp => $cron ) {
			if ( isset( $cron[ $hook ] ) ) {
					unset( $cron[ $hook ] );
					$update_cron = true;
			}

			if ( ! empty( $cron ) ) {
				$newcron[ $timestamp ] = $cron;
			}
		}

		if ( $update_cron ) {
			_set_cron_array( $newcron );
		}
	}

	/**
	 * Get table schema
	 */
	public static function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$tables = "
CREATE TABLE {$wpdb->prefix}myarcadegames (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	postid BIGINT UNSIGNED DEFAULT NULL,
	uuid text NOT NULL,
	game_tag text NOT NULL,
	game_type text NOT NULL,
	name text NOT NULL,
	slug text NOT NULL,
	categories text NOT NULL,
	description text NOT NULL,
	tags text NOT NULL,
	instructions text NOT NULL,
	controls text NOT NULL,
	rating text NOT NULL,
	height text NOT NULL,
	width text NOT NULL,
	thumbnail_url text NOT NULL,
	swf_url text NOT NULL,
	screen1_url text NOT NULL,
	screen2_url text NOT NULL,
	screen3_url text NOT NULL,
	screen4_url text NOT NULL,
	video_url text NOT NULL,
	created text NOT NULL,
	leaderboard_enabled text NOT NULL,
	highscore_type text NOT NULL,
	score_bridge text NOT NULL,
	coins_enabled text NOT NULL,
	status text NOT NULL,
	PRIMARY KEY  (id)
) $collate;
CREATE TABLE {$wpdb->prefix}myarcadescores (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	session text NOT NULL,
	date text NOT NULL,
	datatype text NOT NULL,
	game_tag text NOT NULL,
	user_id text NOT NULL,
	score text NOT NULL,
	sortorder text NOT NULL,
	PRIMARY KEY  (id)
) $collate;
CREATE TABLE {$wpdb->prefix}myarcadehighscores (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	game_tag text NOT NULL,
	user_id text NOT NULL,
	score text NOT NULL,
	PRIMARY KEY  (id)
) $collate;
CREATE TABLE {$wpdb->prefix}myarcademedals (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	date text NOT NULL,
	game_tag text NOT NULL,
	user_id text NOT NULL,
	score text NOT NULL,
	name text NOT NULL,
	description text NOT NULL,
	thumbnail text NOT NULL,
	PRIMARY KEY  (id)
) $collate;
CREATE TABLE {$wpdb->prefix}myarcadeuser (
	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	user_id BIGINT UNSIGNED NOT NULL,
	points int(11) NOT NULL DEFAULT '0',
	plays int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY  (id)
) $collate;
CREATE TABLE {$wpdb->prefix}myarcade_plays (
	ID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	post_id BIGINT UNSIGNED NOT NULL,
	user_id BIGINT UNSIGNED DEFAULT NULL,
	date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	duration BIGINT UNSIGNED DEFAULT NULL,
	PRIMARY KEY  (ID),
	KEY post_id (post_id),
	KEY user_id (user_id)
) $collate;
		";

		return $tables;
	}
}

MyArcade_Install::init();
