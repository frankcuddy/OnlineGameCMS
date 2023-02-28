<?php
/**
 * Count game plays and play duration
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

class MyArcade_Stats_Aggregator {

  /**
   * Init
   *
   */
  public static function init() {
    add_action( 'wp_enqueue_scripts', array( __CLASS__, 'laod_scripts' ) );
  }

  /**
   * Register/Queue tracking scripts
   *
   * @version 5.30.0
   * @since   5.30.0
   * @access  public
   * @return  void
   */
  public static function laod_scripts() {
    global $post;

    // Load on single pages and only if time on game tracking is active
    if ( ! is_single() || ! is_game() ) {
      return;
    }

    $id = self::track_game_play();

    if ( ! $id ) {
      // Don't track
      return;
    }

    $frontend_script_path = str_replace( array( 'http:', 'https:' ), '', MYARCADE_URL . '/assets/' );

    wp_register_script( 'myarcade-stats-frontend', $frontend_script_path . 'js/myarcade-stats-frontend.js', array( 'jquery' ), MYARCADE_VERSION, true );
    wp_enqueue_script( 'myarcade-stats-frontend' );

    wp_localize_script( 'myarcade-stats-frontend', 'myarcade_stats_i18n', array(
      'ajaxurl' => admin_url( 'admin-ajax.php' ),
      'nonce'   => wp_create_nonce( 'myarcade_stats_ajax_nonce' ),
      'slug'    => $post->post_name,
      'token'   => $id
    ));
  }

  /**
   * Track a game play
   *
   * @version 5.30.0
   * @since   5.30.0
   * @access  public
   * @return  void
   */
  public static function track_game_play() {
    global $wpdb, $post;

    $who = self::who_is_it();

    // Don't track bots (0) and admins (1). Track only visitors and users
    if ( $who['ID'] > 1 ) {
      $data = array(
        'post_id' => $post->ID,
        'user_id' => $who['user_id'],
        'date'    => gmdate('Y-m-d H:i:s', ( time() + ( get_option( 'gmt_offset' ) * 3600 ) ) ),
        'duration'=> 0,
      );

			$wpdb->insert( "{$wpdb->prefix}myarcade_plays", $data );

      // Get the iserted ID
      $id = $wpdb->insert_id;

      $data['token'] = $id;
      set_transient( 'myarcade_stats_' . $id, $data, 60*60*2 ); // 2 hours

      return $id;
  }

    return false;
  }

  /**
   * Get a play token by row id
   *
   * @version 5.30.0
   * @since   5.30.0
   * @static
   * @access  public
   * @param   int $row_id database table ID
   * @return  array
   */
  public static function get_token_data( $row_id ) {
    return get_transient( 'myarcade_stats_' . $row_id );
  }

  /**
   * Detect if the current visitor is an admin, user, visitor or bot
   *
   * @version 5.30.0
   * @since   5.30.0
   * @access  public
   * @return  string admin|user|bot|visitor
   */
  public static function who_is_it() {

    $who = array();

    // ID : 0 => 'bot',
    //      1 => 'admin',
    //      2 => 'user',
    //      3 => 'visitor',

    if ( is_user_logged_in() ) {
      $who['user_id'] = get_current_user_id();

      if ( current_user_can( 'manage_options' ) ) {
        $who['ID'] = 1; //admin
      }
      else {
        $who['ID'] = 2; // user
      }
    }
    else {
      if ( self::is_bot() ) {
        $who['ID'] = 0; // bot
        $who['user_id']   = NULL;
      }
      else {
        $who['ID'] = 3; // visitor
        $who['user_id']   = NULL;
      }
    }

    return $who;
  }

  /**
   * Check if this is a bot
   *
   * @version 5.30.0
   * @since   5.30.0
   * @access  public
   * @return  boolean True if it's a bot
   */
  public static function is_bot() {

    $user_agent = empty( $_SERVER['HTTP_USER_AGENT'] ) ? false : $_SERVER['HTTP_USER_AGENT'];

    if ( ! $user_agent ) {
      // No user agent.. This could be a bot
      return true;
    }

    $bots = array(
      '360Spider' => '360spider',
      'AddThis' => 'addthis',
      'Adsbot' => 'Adsbot',
      'AdScanner' => 'adscanner',
      'AHC' => 'AHC',
      'Ahrefs' => 'ahrefsbot',
      'Alex' => 'ia_archiver',
      'AllTheWeb' => 'fast-webcrawler',
      'Altavista' => 'scooter',
      'Amazon' => 'amazonaws.com',
      'Anders Pink' => 'anderspinkbot',
      'Apple' => 'applebot',
      'Archive.org' => 'archive.org_bot',
      'Ask Jeeves' => 'jeeves',
      'Aspiegel' => 'AspiegelBot',
      'Axios' => 'axios',
      'Baidu' => 'baidu',
      'Become.com' => 'become.com',
      'Bing' => 'bingbot',
      'Bing Preview' => 'bingpreview',
      'Blackboard' => 'Blackboard',
      'BLEXBot' => 'blexbot',
      'Bloglines' => 'bloglines',
      'Blog Search Engine' => 'blogsearch',
      'BUbiNG' => 'bubing',
      'Buck' => 'Buck',
      'CCBot' => 'ccbot',
      'CFNetwork' => 'cfnetwork',
      'CheckMarkNetwork' => 'CheckMarkNetwork',
      'Cliqzbot' => 'cliqzbot',
      'Coccoc' => 'coccocbot',
      'Crawl' => 'crawl',
      'Curl' => 'Curl',
      'Cyotek' => 'Cyotek',
      'Daum' => 'Daum',
      'Dispatch' => 'Dispatch',
      'DomainCrawler' => 'domaincrawler',
      'DotBot' => 'dotbot',
      'DuckDuckGo' => 'duckduckbot',
      'EveryoneSocialBot' => 'everyonesocialbot',
      'Exalead' => 'exabot',
      'Facebook' => 'facebook',
      'Facebook Preview' => 'facebookexternalhit',
      'faceBot' => 'facebot',
      'Feedfetcher' => 'Feedfetcher',
      'Findexa' => 'findexa',
      'Flipboard Preview' => 'FlipboardProxy',
      'Gais' => 'gaisbo',
      'Gigabot' => 'gigabot',
      'Gluten Free' => 'gluten free crawler',
      'Go-http-client' => 'Go-http-client',
      'Goforit' => 'GOFORITBOT',
      'Google' => 'google',
      'Grid' => 'gridbot',
      'GroupHigh' => 'grouphigh',
      'Heritrix' => 'heritrix',
      'IA Archiver' => 'ia_archiver',
      'Inktomi' => 'slurp@inktomi',
      'IPS Agent' => 'ips-agent',
      'James' => 'james bot',
      'Jobboerse' => 'Jobboerse',
      'KomodiaBot' => 'komodiabot',
      'Konqueror' => 'konqueror',
      'Lindex' => 'linkdexbot',
      'Linguee' => 'Linguee',
      'Linkfluence' => 'linkfluence',
      'Lycos' => 'lycos',
      'Maui' => 'mauibot',
      'Mediatoolkit' => 'mediatoolkitbot',
      'MegaIndex' => 'MegaIndex',
      'MetaFeedly' => 'MetaFeedly',
      'MetaURI' => 'metauri',
      'MJ12bot' => 'mj12bot',
      'MojeekBot' => 'mojeekBot',
      'Moreover' => 'moreover',
      'MSN' => 'msnbot',
      'NBot' => 'nbot',
      'Node-Fetch' => 'node-fetch',
      'oBot' => 'oBot',
      'NextLinks' => 'findlinks',
      'Panscient' => 'panscient.com',
      'PaperLiBot' => 'paperliBot',
      'PetalBot' => 'PetalBot',
      'PhantomJS' => 'phantomjs',
      'Picsearch' => 'picsearch',
      'Proximic' => 'proximic',
      'PubSub' => 'pubsub',
      'Radian6' => 'radian6',
      'RadioUserland' => 'userland',
      'RyteBot' => 'RyteBot',
      'Moz' => 'rogerbot',
      'Qwantify' => 'Qwantify',
      'Scoutjet' => 'Scoutjet',
      'Screaming Frog SEO Spider' => 'Screaming Frog SEO Spider',
      'SEOkicks' => 'seokicks-robot',
      'Semanticbot' => 'Semanticbot',
      'SemrushBot' => 'semrushbot',
      'SerendeputyBot' => 'serendeputybot',
      'Seznam' => 'seznam',
      'SirdataBot ' => 'SirdataBot ',
      'SiteExplorer' => 'siteexplorer',
      'Sixtrix' => 'SIXTRIX',
      'Slurp' => 'slurp',
      'SMTBot' => 'SMTBot',
      'Sogou' => 'Sogou',
      'OpenLinkProfiler.org' => 'spbot',
      'SurveyBot' => 'surveybot',
      'Syndic8' => 'syndic8',
      'Technorati' => 'technorati',
      'TelegramBot' => 'telegrambot',
      'Thither' => 'thither',
      'TraceMyFile' => 'tracemyfile',
      'Trendsmap' => 'trendsmap',
      'Turnitin.com' => 'turnitinbot',
      'The Tweeted Times' => 'tweetedtimes',
      'TweetmemeBot' => 'tweetmemeBot',
      'Twingly' => 'twingly',
      'Twitter' => 'twitterbot',
      'VoilaBot' => 'VoilaBot',
      'Wget' => 'wget',
      'WhatsApp' => 'whatsapp',
      'WhoisSource' => 'surveybot',
      'WiseNut' => 'zyborg',
      'Wotbox' => 'wotbox',
      'Xenu Link Sleuth' => 'xenu link sleuth',
      'XoviBot' => 'xoviBot',
      'Yahoo' => 'yahoo',
      'Yandex' => 'yandex',
      'YisouSpider' => 'yisouspider'
    );

    $result = false;

    foreach ( $bots as $name => $lookfor ) {
      if ( stristr( $user_agent, $lookfor ) !== false ) {
        $result = true;
        break;
      }
    }

    return $result;
  }
} // END Class

MyArcade_Stats_Aggregator::init();