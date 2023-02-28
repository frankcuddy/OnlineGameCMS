<?php
/**
 * Displays the settings page on backend
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 */

// No direct access.
if( !defined( 'ABSPATH' ) ) {
  die();
}

/**
 * Settings page
 *
 * @version 5.30.0
 * @access  public
 * @return  void
 */
function myarcade_settings() {

	$distributors = MyArcade()->distributors();

  myarcade_header();

  ?>
  <div id="icon-tools" class="icon32"><br /></div>
  <h2><?php _e("Settings"); ?></h2>
  <?php

  if ( "unknown" == get_option( 'myarcade_allow_tracking' ) ) {
    myarcade_tracking_message();
  }

  $action = filter_input( INPUT_POST, 'feedaction' );

  if ( 'save' == $action ) {

    myarcade_check_settings_nonce();

    // Remove the settings update notice if set
    if ( get_transient('myarcade_settings_update_notice') == true  ) {
      delete_transient('myarcade_settings_update_notice');
    }

    $general = array();
    if ( isset($_POST['leaderboardenable'])) $general['scores'] = true; else $general['scores'] = false;
    if ( isset($_POST['onlyhighscores'])) $general['highscores'] = true; else $general['highscores'] = false;
    if ( isset($_POST['game_count'])) $general['posts'] = intval($_POST['game_count']); else $general['posts'] = '';
    if ( isset($_POST['publishstatus'])) $general['status'] = $_POST['publishstatus']; else $general['status'] = 'publish';
    if ( isset($_POST['schedtime'])) $general['schedule'] = intval( $_POST['schedtime']); else $general['schedule'] = 0;
    if ( isset($_POST['downloadgames'])) $general['down_games'] = true; else $general['down_games'] = false;
    if ( isset($_POST['downscreens'])) $general['down_screens'] = true; else $general['down_screens'] = false;

    $general['folder_structure'] = (isset($_POST['folder_structure'])) ? $_POST['folder_structure'] : false;
    $general['automated_fetching']  = (isset($_POST['automated_fetching'])) ? true : false;
    $general['interval_fetching']   = $_POST['interval_fetching'];
    $general['automated_publishing']  = (isset($_POST['automated_publishing'])) ? true : false;
    $general['interval_publishing']   = $_POST['interval_publishing'];
    $general['cron_publish_limit'] = !empty($_POST['general_cron_publish_limit']) ? $_POST['general_cron_publish_limit'] : 1;
    $general['types'] = filter_input( INPUT_POST, 'general_types' );
    $general['handle_ssl'] = (isset($_POST['handle_ssl'])) ? true : false;;

    if ( isset($_POST['createcats'])) $general['create_cats'] = true; else $general['create_cats'] = false;
    if ( isset($_POST['parentcatid'])) $general['parent'] = $_POST['parentcatid']; else $general['parent'] = '';
    if ( isset($_POST['firstcat'])) $general['firstcat'] = true; else $general['firstcat'] = false;
    if ( isset($_POST['maxwidth'])) $general['max_width'] = intval($_POST['maxwidth']); else $general['max_width'] = '';
    if ( isset($_POST['singlecat'])) $general['single'] = true; else $general['single'] = false;
    if ( isset($_POST['singlecatid'])) $general['singlecat'] = $_POST['singlecatid']; else $general['singlecat'] = '';
    if ( isset($_POST['embedflashcode'])) $general['embed'] = $_POST['embedflashcode']; else $general['embed'] = 'manually';
    if ( isset($_POST['usetemplate'])) $general['use_template'] = true; else $general['use_template'] = false;
    if ( isset($_POST['post_template'])) $general['template'] = stripslashes($_POST['post_template']); else $general['template'] = '';
    if ( isset($_POST['allow_user'])) $general['allow_user'] = true; else $general['allow_user'] = false;
    if ( isset($_POST['limitplays'])) $general['limit_plays'] = intval($_POST['limitplays']); else $general['limit_plays'] = 0;
    if ( isset($_POST['limitmessage'])) $general['limit_message'] = stripslashes($_POST['limitmessage']); else $general['limit_message'] = '';
    if ( isset($_POST['posttype'])) $general['post_type'] = $_POST['posttype']; else $general['post_type'] = 'post';

    $general['play_delay'] = isset($_POST['play_delay']) ? $_POST['play_delay'] : '30';
    $general['translation'] = $_POST['translation'];
    $general['azure_key'] = sanitize_text_field( filter_input( INPUT_POST, 'azure_key' ) );
    $general['translate_to'] = isset($_POST['translate_to']) ? $_POST['translate_to'] : 'en';
    $general['translate_fields'] = isset($_POST['translate_fields']) ? $_POST['translate_fields'] : array();
    $general['translate_games'] = isset($_POST['translate_games']) ? $_POST['translate_games'] : array();
    $general['google_id'] = isset($_POST['google_id']) ? sanitize_text_field($_POST['google_id']) : '';
    $general['google_translate_to'] = $_POST['google_translate_to'];
    $general['yandex_key'] = isset($_POST['yandex_key']) ? $_POST['yandex_key'] : '';
    $general['yandex_translate_to'] = $_POST['yandex_translate_to'];

    // Custom taxonomies
    $general['custom_category'] =  isset($_POST['customtaxcat']) ? $_POST['customtaxcat'] : '';
    $general['custom_tags'] = isset($_POST['customtaxtag']) ? $_POST['customtaxtag'] : '';
    // Default CSS / JS Styles
    //$general['styles'] = isset($_POST['styles']) ? true : false;
    $general['disable_game_tags'] = isset( $_POST['disable_game_tags'] ) ? true : false;

    // Update Settings
    update_option('myarcade_general', $general);

    // Update distributor settings dynamically
		foreach ($distributors as $key => $name) {
      // Generate save settings function name
      $settings_update_function = 'myarcade_save_settings_' . $key;

      // Get distributor integration file
			MyArcade()->load_distributor( $key );

      // Check if function exists
      if ( function_exists( $settings_update_function ) ) {
        // Update settings
        $settings_update_function();
      }
    } // end foreach

    // END Settings Updates
    //_________________________________________________________________________

    //
    // Create Game Categories
    //
    if ( isset($_POST['gamecats'])) $categories_post = $_POST['gamecats']; else $categories_post = array();

    // Get current settings
    $feedcategories = get_option('myarcade_categories');

    // count checked categories
    $cat_count = 0;
    $feedcat_count = count($feedcategories);
    $categories_array = array();

    for ($i = 0; $i < $feedcat_count; $i++) {
      if( in_array( $feedcategories[$i]['Slug'], $categories_post ) ) {
        $cat_count++;
        $feedcategories[$i]['Status'] = 'checked';
        $categories_array[] = $feedcategories[$i]['Name'];
      }
      else {
        $feedcategories[$i]['Status'] = '';
      }
    }

    // Update categories
    update_option('myarcade_categories', $feedcategories);

    if ( $cat_count ) {
      // Create categories if activated
      if ( true == $general['create_cats'] && ! empty( $categories_array ) ) {
        myarcade_create_categories( $categories_array );
      }
    }

    echo '<p class="mabp_info mabp_800">'.__("Your settings have been updated!", 'myarcadeplugin').'</p>';
  } // END - if action

  if ( isset($_POST['loaddefaults']) && isset($_POST['checkdefaults']) && $_POST['checkdefaults'] == 'yes' ) {
    myarcade_load_default_settings();
    echo '<p class="mabp_info mabp_800">'.__("Default settings have been restored!", 'myarcadeplugin').'</p>';
  }

  // Get settings
  $general    = get_option('myarcade_general');
  $categories = get_option('myarcade_categories');

  if ( empty( $categories ) ) {
    echo '<p class="mabp_error mabp_800">'.__("You will not be able to fetch games!", 'myarcadeplugin').' '.__('Go to "General Settings" and activate some game categories!', 'myarcadeplugin').'</p>';
  }

  // Load settings dynamically
	foreach ($distributors as $key => $name) {
		$$key = MyArcade()->get_settings( $key );
  }

  // Create directories
	MyArcade_Install::create_directories();

	$upload_dir = MyArcade()->upload_dir();

  if ( $general['down_games'] ) {
    if ( !is_writable( $upload_dir['gamesdir'] ) ) {
      echo '<p class="mabp_error mabp_800">'.sprintf(__("The games directory '%s' must be writable (chmod 777) in order to download games.", 'myarcadeplugin'), $upload_dir['gamesdir'] ).'</p>';
    }
  }

  if ( $general['down_screens'] ) {
    if ( !is_writable( $upload_dir['thumbsdir'] ) ) {
      echo '<p class="mabp_error mabp_800">'.sprintf(__("The images directory '%s' must be writable (chmod 777) in order to download game images.", 'myarcadeplugin'), $upload_dir['thumbsdir'] ).'</p>';
    }
  }

  // Check ID for Microsoft Translator
  if ( ($general['translation'] == 'microsoft') && empty( $general['azure_key'] ) ) {
    echo '<p class="mabp_error mabp_800">'.__("You have activated the Microsoft Translator but not entered your Azure Key. In this case the translator will not work!", 'myarcadeplugin').'</p>';
  }
  if ( ($general['translation'] == 'google') && empty( $general['google_id'] ) ) {
    echo '<p class="mabp_error mabp_800">'.__("You have activated the Google Translator but not entered your Google API Key. In this case the translator will not work!", 'myarcadeplugin').'</p>';
  }

  // Get all categories
  if ( $general['post_type'] == 'post') {
    $categs_ids_tmp = get_terms( 'category', array('fields' => 'ids', 'get' => 'all') );
    $categs_tmp = array();

    foreach ($categs_ids_tmp as $categ_id_tmp) {
      $categs_tmp[$categ_id_tmp] = get_cat_name($categ_id_tmp);
    }
  }
  else {
    $categs_tmp = array();

    if (taxonomy_exists($general['custom_category']) ) {
      $taxonomies = get_terms($general['custom_category'], array('hide_empty' => false));

      foreach ($taxonomies as $taxonomy) {
        $categs_tmp[$taxonomy->term_id] = $taxonomy->name;
      }
    }
  }

  // Create an array with all available cron intervals
	$myarcade_cron_intervals = MyArcade_Install::get_schedules();

  $default_crones = array(
    'hourly'    => array('display' => __('Hourly')),
    'twicedaily'=> array('display' => __('Twice Daily')),
    'daily'     => array('display' => __('Daily')),
  );

  $crons =array_merge($myarcade_cron_intervals,$default_crones);
  ?>
    <br />

    <div id="myarcade_settings">
      <form method="post" name="editsettings">
        <?php wp_nonce_field( 'myarcade_save_settings', 'myarcade_save_settings_nonce' ); ?>
        <input type="hidden" name="feedaction" value="save">

        <?php
        //----------------------------------------------------------------------
        // General Settings
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><?php _e("General Settings", 'myarcadeplugin'); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <table class="optiontable" width="100%">

              <tr><td colspan="2"><h3><?php _e("Save User Scores", 'myarcadeplugin'); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="checkbox" name="leaderboardenable" value="true" <?php myarcade_checked($general['scores'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?></label>
                </td>
                <td><i><?php _e("Check this if you want to collect user scores. Only scores submitted by IBPArcade games will be collected.", 'myarcadeplugin'); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Save Only Highscores", 'myarcadeplugin'); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="onlyhighscores" value="true" <?php myarcade_checked($general['highscores'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?></label>
                </td>
                <td><i><?php _e("Check this if you want to only save a user's highest score. Otherwise all submitted scores are saved.", 'myarcadeplugin'); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Game Types", 'myarcadeplugin'); ?></h3></td></tr>

              <tr>
                <td>
                  <select size="1" name="general_types" id="general_types">
                    <option value="all" <?php myarcade_selected($general['types'], 'all'); ?> ><?php _e('All', 'myarcadeplugin'); ?></option>
                    <option value="mobile" <?php myarcade_selected($general['types'], 'mobile'); ?> ><?php _e('Mobile only', 'myarcadeplugin'); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Select a game type you want to fetch.", 'myarcadeplugin'); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Publish Games", 'myarcadeplugin'); ?></h3></td></tr>
              <tr>
                <td>
									<input type="text" size="10"  name="game_count" value="<?php echo esc_attr( $general['posts'] ); ?>" />
                </td>
                <td><i><?php _e('How many games should be published when clicking on "Publish Games"?', 'myarcadeplugin'); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Post Status", 'myarcadeplugin'); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="radio" name="publishstatus" value="publish" <?php myarcade_checked($general['status'], 'publish'); ?> /><label class="opt">&nbsp;<?php _e("Publish", 'myarcadeplugin'); ?></label>
                  <input type="radio" name="publishstatus" value="future" <?php myarcade_checked($general['status'], 'future'); ?> /><label class="opt">&nbsp;<?php _e("Scheduled", 'myarcadeplugin'); ?></label>
                  <input type="radio" name="publishstatus" value="draft" <?php myarcade_checked($general['status'], 'draft'); ?> /><label class="opt">&nbsp;<?php _e("Draft", 'myarcadeplugin'); ?></label>
                  <br /><br />
									<?php _e("Schedule Time", 'myarcadeplugin'); ?>: <input type="text" size="10" name="schedtime" value="<?php echo esc_attr( $general['schedule'] ); ?>">
                </td>
                <td><i><?php _e("Choose the post status for new game publication. If you whish to schedule new game publication, indicate an interval between publications in minutes.", 'myarcadeplugin'); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Download Games", 'myarcadeplugin'); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="downloadgames" value="true"  <?php myarcade_checked($general['down_games'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?></label>
                </td>
                <td><i><?php _e("Activate this in order to download Flash (SWF) games to your server. Keep in mind that HMTL5 games can't be downloaded.", 'myarcadeplugin'); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Download Screenshots", 'myarcadeplugin'); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="downscreens" value="true"  <?php myarcade_checked($general['down_screens'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?></label>
                </td>
                <td><i><?php _e("Should the game screenshots be imported and stored on your web server? For this to work properly, the thumb directory (wp-content/uploads/thumbs/) must be  writable.", 'myarcadeplugin'); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Folder Organization Structure", 'myarcadeplugin'); ?></h3></td></tr>

              <tr>
                <td>
									<input type="text" size="40"  name="folder_structure" value="<?php echo esc_attr( $general['folder_structure'] ); ?>" />
                </td>
                <td><i><?php _e('Define the folder structure for file downloads. Available variables are %game_type% and %alphabetical%. You can combine those variables like this: %game_type%/%alphabetical%/.', 'myarcadeplugin'); ?><br />
                    <?php _e('That means, for each game type a new folder will be created and files will be organized in sub folders. Example: "/games/fog/A/awesome_game.swf.', 'myarcadeplugin'); ?><br />
                    <?php _e('Leave blank if you want to save all files in a single folder."', 'myarcadeplugin'); ?></i></td>
              </tr>


              <tr><td colspan="2"><h3><?php _e("Automation / Cron Settings", 'myarcadeplugin'); ?></h3></td></tr>
              <tr><td colspan="2"><p><?php _e("Global automation settings allows you to enable and setup automated fetching and publishing globally. You can enable/disable automated fetching and publishing for each game distributor separately when you click on distributors settings.", 'myarcadeplugin'); ?></p></td></tr>

              <tr><td colspan="2"><h4><?php _e("Automated Game Fetching", 'myarcadeplugin'); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="automated_fetching" value="true" <?php myarcade_checked($general['automated_fetching'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?></label>
                </td>
                <td><i><?php _e("This option will activate automated game fetching globally. If activated the cron job will be triggered by WordPress.", 'myarcadeplugin'); ?></i></td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("Game Fetching Interval", 'myarcadeplugin'); ?></h4></td></tr>

              <tr>
                <td>
                  <select size="1" name="interval_fetching" id="interval_fetching">
                    <?php
                    foreach($crons as $cron => $val) {
                      ?>
											<option value="<?php echo esc_attr( $cron ); ?>" <?php myarcade_selected($general['interval_fetching'], $cron); ?> ><?php echo esc_html( $val['display'] ); ?></option>
                      <?php
                    }
                    ?>
                  </select>
                </td>
                <td><i><?php _e("Select a frequency for fetching new games. Games are fetched per the scheduled frequency, pending a user visiting your site (which triggers the function).", 'myarcadeplugin'); ?></i></td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("Automated Game Publishing", 'myarcadeplugin'); ?></h4></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="automated_publishing" value="true" <?php myarcade_checked($general['automated_publishing'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?></label>
                </td>
                <td><i><?php _e("This option will activate automated game publishing globally. If activated the cron job will be triggered by WordPress.", 'myarcadeplugin'); ?></i></td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("Game Publishing Interval", 'myarcadeplugin'); ?></h4></td></tr>

              <tr>
                <td>
                  <select size="1" name="interval_publishing" id="interval_publishing">
                    <?php
                    foreach($crons as $cron => $val) {
                      ?>
											<option value="<?php echo esc_attr( $cron ); ?>" <?php myarcade_selected($general['interval_publishing'], $cron); ?> ><?php echo esc_html( $val['display'] ); ?></option>
                      <?php
                    }
                    ?>
                  </select>
                </td>
                <td><i><?php _e("Select a frequency for publishing new games. Games are published per the scheduled frequency, pending a user visiting your site (which triggers the function).", 'myarcadeplugin'); ?></i></td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("Publish Games (Manually Imported Games)", 'myarcadeplugin'); ?></h4></td></tr>

              <tr>
                <td>
									<input type="text" size="10"  name="general_cron_publish_limit" value="<?php echo esc_attr( $general['cron_publish_limit'] ); ?>" />
                </td>
                <td><i><?php _e("How many games should be published on every cron trigger? This setting affects only manually imported games.", 'myarcadeplugin'); ?></i></td>



              <tr><td colspan="2"><h3><?php _e("Game Categories", 'myarcadeplugin'); ?></h3></td></tr>

              <tr>
                <td>
                <?php
                  foreach ($categories as $feedcat) {
                    echo '<input type="checkbox" name="gamecats[]" value="'.$feedcat['Slug'].'" '.$feedcat['Status'].' /><label class="opt">&nbsp;'.$feedcat['Name'].'</label><br />';
                  }
                ?>
                </td>
                <td><i><?php _e("Select game categories that should be fetched.", 'myarcadeplugin'); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Create Categories", 'myarcadeplugin'); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="createcats" value="true" <?php myarcade_checked($general['create_cats'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?></label>
                </td>
                <td><i><?php _e("Check this if you want to create selected categories on your blog.", 'myarcadeplugin'); ?></i></td>
              </tr>


              <tr><td colspan="2"><h3><?php _e("Parent Category", 'myarcadeplugin'); ?></h3></td></tr>
                <tr>
                  <td>
                    <select size="1" name="parentcatid" id="parentcatid">
                    <option value=''>--- <?php _e("No Parent Category", 'myarcadeplugin'); ?> ---</option>
                    <?php
                      // Get selected category
                      foreach ($categs_tmp as $cat_tmp_id => $cat_tmp_name) {
                        if ($cat_tmp_id == $general['parent']) { $cat_selected = 'selected'; } else { $cat_selected = ''; }
                        echo '<option value="'.$cat_tmp_id.'" '.$cat_selected.'>'.$cat_tmp_name.'</option>';
                      }
                    ?>
                    </select>
                  </td>
                  <td><i><?php _e("This option will create game categories as subcategories in the selected category.", 'myarcadeplugin'); ?> <?php _e(" This option is useful if you have a mixed site and not only a pure arcade site.", 'myarcadeplugin'); ?></i></td>
                </tr>

                <?php // Use only the first category ?>
                <tr>
                  <td colspan="2">
                    <h3><?php _e("Use Only The First Category", 'myarcadeplugin'); ?></h3>
                  </td>
                </tr>
                <tr>
                  <td>
                    <input type="checkbox" name="firstcat" value="true" <?php myarcade_checked($general['firstcat'], true); ?> />&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?>
                  </td>
                  <td><i><?php _e("Many game developers tag their games to more than one category to get more downloads. Thereby the gamess will be added to several categories. Activate this option to avoid game publishing in more than one category.", 'myarcadeplugin'); ?></i></td>
                </tr>

              <tr><td colspan="2"><h3><?php _e("Max. Game Width (optional)", 'myarcadeplugin'); ?></h3></td></tr>

              <tr>
                <td>
									<input type="text" size="10" name="maxwidth" value="<?php echo esc_attr( $general['max_width'] ); ?>" />
                </td>
                <td><i><?php _e("Maximum allowed game width in px. If set, the get_game function will create output code with adjusted game dimensions.", 'myarcadeplugin'); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Publish In A Single Category", 'myarcadeplugin'); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="singlecat" value="true" <?php myarcade_checked($general['single'], true); ?> />&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?>
                  <select size="1" name="singlecatid" id="singlecatid">
                  <?php
                    // Get selected category
                    foreach ($categs_tmp as $cat_tmp_id => $cat_tmp_name) {
                      if ($cat_tmp_id == $general['singlecat']) { $cat_selected = 'selected'; } else { $cat_selected = ''; }
                      echo '<option value="'.$cat_tmp_id.'" '.$cat_selected.'/>'.$cat_tmp_name.'</option>';
                    }
                  ?>
                  </select>
                </td>
                <td><i><?php _e("This option will publish all games only in the selected category.", 'myarcadeplugin'); ?> <?php _e("This option is useful if you have a mixed site and not only a pure arcade site.", 'myarcadeplugin'); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Embed Game Code", 'myarcadeplugin'); ?></h3></td></tr>

              <tr>
                <td>
                <select size="1" name="embedflashcode" id="embedflashcode">
                  <option value="manually" <?php myarcade_selected($general['embed'], 'manually'); ?> ><?php _e("Manually", 'myarcadeplugin'); ?></option>
                  <option value="top" <?php myarcade_selected($general['embed'], 'top'); ?> ><?php _e("At The Top Of A Game Post", 'myarcadeplugin'); ?></option>
                  <option value="bottom" <?php myarcade_selected($general['embed'], 'bottom'); ?> ><?php _e("At The Bottom Of A Game Post", 'myarcadeplugin'); ?></option>
                </select>
                </td>
                <td><i><?php _e("Select if MyArcadePlugin should auto embed the game code in your game posts (only if you don't use FunGames theme).", 'myarcadeplugin'); ?></i></td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("Game Post Template", 'myarcadeplugin'); ?></h3></td></tr>

              <tr>
                <td>
                  <input type="checkbox" name="usetemplate" value="true" <?php myarcade_checked($general['use_template'], true); ?> /><label class="opt">&nbsp;<?php _e("Activate Post Template", 'myarcadeplugin'); ?></label>
                  <br /><br />
                  <textarea rows="12" cols="40" id="post_template" name="post_template"><?php echo htmlspecialchars(stripslashes($general['template'])); ?></textarea>
                </td>
                <td><i>
                    <?php _e("Use this template to style the output of MyArcadePlugin when creating game posts.", 'myarcadeplugin'); ?>
                    <br />
                     <strong><?php _e("Available Variables", 'myarcadeplugin'); ?>:</strong><br />
                    %TITLE% - <?php _e("Show the game title", 'myarcadeplugin'); ?><br />
                    %DESCRIPTION% - <?php _e("Show game description", 'myarcadeplugin'); ?><br />
                    %INSTRUCTIONS% - <?php _e("Show game instructions if available", 'myarcadeplugin'); ?><br />
                    %TAGS% - <?php _e("Show all game tags", 'myarcadeplugin'); ?><br />
                    %THUMB% - <?php _e("Show the game thumbnail", 'myarcadeplugin'); ?><br />
                    %THUMB_URL% - <?php _e("Show game thumbnail URL", 'myarcadeplugin'); ?><br />
                    %SWF_URL% - <?php _e("Show game SWF URL / Embed Code", 'myarcadeplugin'); ?><br />
                    %WIDTH% - <?php _e("Show game width", 'myarcadeplugin'); ?><br />
                    %HEIGHT% - <?php _e("Show game height", 'myarcadeplugin'); ?><br />
                  </i></td>
              </tr>

              <?php // Disable game tags ?>
              <tr>
                <td colspan="2">
                  <h3><?php _e("Disable Game Tags", 'myarcadeplugin'); ?></h3>
                </td>
              </tr>
              <tr>
                <td>
                  <input type="checkbox" name="disable_game_tags" value="true" <?php myarcade_checked($general['disable_game_tags'], true); ?> />&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?>
                </td>
                <td><i><?php _e("Check this if you want to prevent MyArcadePlugin from adding tags to WordPress posts (not recommended).", 'myarcadeplugin'); ?></i></td>
              </tr>

              <?php // Allow users to post games?>
              <tr>
                <td colspan="2">
                  <h3><?php _e("Allow Users To Post Games", 'myarcadeplugin'); ?></h3>
                </td>
              </tr>
              <tr>
                <td>
                  <input type="checkbox" name="allow_user" value="true" <?php myarcade_checked($general['allow_user'], true); ?> />&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?>
                </td>
                <td><i><?php _e("Activate this if you want to give your users access to import games. WordPress supports following user roles: Contributor, Author and Editor. Games added by Contributors will be saved as drafts! Authors and Editors will be able to publish games.", 'myarcadeplugin'); ?></i></td>
              </tr>

              <?php // Force guests to register after x plays ?>
              <tr>
                <td colspan="2">
                  <h3><?php _e("Guest Plays", 'myarcadeplugin'); ?></h3>
                </td>
              </tr>
              <tr>
                <td>
									<input type="text" size="10" name="limitplays" value="<?php echo esc_attr( $general['limit_plays'] ); ?>" />
                </td>
                <td><i><?php _e("Set how many games a guest can play before he/she needs to register. Set to 0 to deactivate the game play check.", 'myarcadeplugin'); ?></i></td>
              </tr>

              <?php // Message ?>
              <tr>
                <td colspan="2">
                  <h3><?php _e("Guest Message", 'myarcadeplugin'); ?></h3>
                </td>
              </tr>
              <tr>
                <td>
                  <textarea rows="12" cols="40" id="limitmessage" name="limitmessage"><?php echo htmlspecialchars(stripslashes($general['limit_message'])); ?></textarea>
                </td>
                <td><i><?php _e("Enter the message here that you want a guest to see after 'X' number of plays (HTML allowed)", 'myarcadeplugin'); ?></i></td>
              </tr>

              <?php // Game play delay ?>
              <tr>
                <td colspan="2">
                  <h3><?php _e("Game Play Delay", 'myarcadeplugin'); ?></h3>
                </td>
              </tr>
              <tr>
                <td>
									<input type="text" size="10" name="play_delay" value="<?php echo esc_attr( $general['play_delay'] ); ?>" />
                </td>
                <td><i><?php _e("Game play delay is responsible for play and contest counter of a user. MyArcadePlugin will only count game plays when the delay time between two game plays is expired. Default value: 30 [time in seconds].", 'myarcadeplugin'); ?></i></td>
              </tr>

              <?php // Post Type ?>
              <tr>
                <td colspan="2">
                  <h3><?php _e("Post Type", 'myarcadeplugin'); ?></h3>
                </td>
              </tr>
              <tr>
                <td>
                  <?php
                  $types = get_post_types();
                  $exclude = array('attachment', 'revision', 'nav_menu_item', 'page');
                  $types = array_diff($types, $exclude);
                  ?>
                  <select size="1" name="posttype" id="posttype">
                    <?php
                    foreach($types as $type) {
                      ?>
											<option value="<?php echo esc_attr( $type ); ?>" <?php myarcade_selected($general['post_type'], $type); ?>>
												<?php echo esc_html( $type ); ?>
                      </option>
                    <?php } ?>
                  </select>
                </td>
                <td><i><?php _e("Select a post type you want to use with MyArcadePlugin. If you want to use a custom post type then you will need to create it before you can make a selection. The easiest way to create a custom post type is to use a plugin like 'Custom Post Type UI'.", 'myarcadeplugin'); ?></i></td>
              </tr>

              <tr>
                <td colspan="2">
                  <h3><?php _e("Custom Taxonomies", 'myarcadeplugin'); ?></h3>
                </td>
              </tr>
              <tr>
                <td colspan="2">
                  <?php
                  $custom_taxonomies = get_taxonomies(array('public'   => true,'_builtin' => false));
                  if ( !is_array($custom_taxonomies) || empty($custom_taxonomies)) {
                    ?>
                    <i><?php _e('No custom taxonomies found..', 'myarcadeplugin'); ?></i>
                    <?php
                  } else {
                    ?>
                    <table>
                      <tr>
											<td><?php esc_html_e( 'Game Categories:', 'myarcadeplugin' ); ?></td>
                      <td>
                    <?php if ( is_array($custom_taxonomies) && !empty($custom_taxonomies)) : ?>
                      <select size="1" name="customtaxcat" id="customtaxcat">
												<option value=""><?php esc_html_e( '-- select a taxonomy --', 'myarcadeplugin' ); ?></option>
                        <?php foreach( $custom_taxonomies as $taxonomy) : ?>
												<option value="<?php echo esc_attr( $taxonomy ); ?>" <?php myarcade_selected($taxonomy , $general['custom_category']); ?>><?php echo esc_html( $taxonomy ); ?></option>
                        <?php endforeach; ?>
                      </select>
                    <?php endif; ?>
                      </td>
                      <td><?php _e("Select a custom taxonomy that should be used for game categories.", 'myarcadeplugin'); ?></td>
                      </tr>
                      <tr>
											<td><?php esc_html_e( 'Game Tags:', 'myarcadeplugin' ); ?></td>
                      <td>
                    <?php if ( is_array($custom_taxonomies) && !empty($custom_taxonomies)) : ?>
                      <select size="1" name="customtaxtag" id="customtaxtag">
												<option value=""><?php esc_html_e( '-- select a taxonomy --', 'myarcadeplugin' ); ?></option>
                        <?php foreach( $custom_taxonomies as $taxonomy) : ?>
												<option value="<?php echo esc_attr( $taxonomy ); ?>" <?php myarcade_selected($taxonomy , $general['custom_tags']); ?>><?php echo esc_html( $taxonomy ); ?></option>
                        <?php endforeach; ?>
                      </select>
                    <?php endif; ?>
                      </td>
                      <td><?php _e("Select a custom taxonomy that should be used for game tags.", 'myarcadeplugin'); ?></td>
                      </tr>
                    </table>
                    <?php
                  }
                  ?>
                </td>
              </tr>

              <tr><td colspan="2"><h3><?php _e("HTTP to HTTPS", 'myarcadeplugin'); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="checkbox" name="handle_ssl" value="true" <?php myarcade_checked($general['handle_ssl'], true); ?> /><label class="opt">&nbsp;<?php _e("Yes", 'myarcadeplugin'); ?></label>
                </td>
                <td><i><?php _e("Enable this if you have switched your site URL from HTTP to HTTPS. This option will replace HTTP with HTTPS in your game URLs. Keep in mind that now every game distributor supports SSL. Some games will not work with SSL.", 'myarcadeplugin'); ?></i></td>
              </tr>

            </table>
            <input class="button button-primary" id="submit" type="submit" name="submit" value="<?php _e("Save Settings", 'myarcadeplugin'); ?>" />
          </div>
        </div>

       <?php
        //----------------------------------------------------------------------
        // Translation Settings
        //----------------------------------------------------------------------
        ?>

        <?php include_once(MYARCADE_CORE_DIR.'/languages.php'); ?>

        <h2 class="trigger"><?php myarcade_premium_span(); _e("Translation Settings", 'myarcadeplugin'); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <?php myarcade_premium_message(); ?>
            <table class="optiontable" width="100%" cellpadding="5" cellspacing="5">
              <tr>
                <td colspan="2">
                  <i>
                    <?php _e( 'Translate games automatically to your language using the Microsoft Translator, Google Translate, or Yandex Translation. The translation is triggered during the game publishing process.', 'myarcadeplugin' ); ?>
                  </i>
                </td>
              </tr>

              <?php // Enable Translator ?>
              <tr>
                <td colspan="2">
                  <h3><?php _e("Select Translation Service", 'myarcadeplugin'); ?></h3>
                </td>
              </tr>
              <tr>
                <td>
                  <select name="translation">
                    <option value="none" <?php myarcade_selected($general['translation'], 'none'); ?>><?php _e("Disable Translations", 'myarcadeplugin'); ?></option>
                    <option value="microsoft" <?php myarcade_selected($general['translation'], 'microsoft'); ?>><?php _e("Microsoft Translator", 'myarcadeplugin'); ?></option>
                    <option value="google" <?php myarcade_selected($general['translation'], 'google'); ?>><?php _e("Google Translator", 'myarcadeplugin'); ?></option>
                    <option value="yandex" <?php myarcade_selected($general['translation'], 'yandex'); ?>><?php _e("Yandex Translator", 'myarcadeplugin'); ?></option>
                  </select>
                </td>
                <td><i><?php _e("Check this if you want to enable the translator.", 'myarcadeplugin'); ?></i></td>
              </tr>

              <?php // Fields to translate ?>
              <tr><td colspan="2"><h3><?php _e("Game Fields To Translate", 'myarcadeplugin'); ?></h3></td></tr>
              <tr>
                <td>
                  <input type="checkbox" name="translate_fields[]" value="name" <?php myarcade_checked_array($general['translate_fields'], 'name'); ?> />&nbsp;<?php _e("Name", 'myarcadeplugin'); ?><br />
                  <input type="checkbox" name="translate_fields[]" value="description" <?php myarcade_checked_array($general['translate_fields'], 'description'); ?> />&nbsp;<?php _e("Description", 'myarcadeplugin'); ?><br />
                  <input type="checkbox" name="translate_fields[]" value="instructions" <?php myarcade_checked_array($general['translate_fields'], 'instructions'); ?> />&nbsp;<?php _e("Instructions", 'myarcadeplugin'); ?><br />
                  <input type="checkbox" name="translate_fields[]" value="tags" <?php myarcade_checked_array($general['translate_fields'], 'tags'); ?> />&nbsp;<?php _e("Tags", 'myarcadeplugin'); ?>
                </td>
                <td><i><?php _e("Select game fields that you want to translate.", 'myarcadeplugin'); ?></i></td>
              </tr>

              <?php // Games to translate ?>
              <tr><td colspan="2"><h3><?php _e("Game Types To Translate", 'myarcadeplugin'); ?></h3></td></tr>
              <tr>
                <td>
									<?php foreach ( $distributors as $distr_slug => $distr_name) : ?>
									<input type="checkbox" name="translate_games[]" value="<?php echo esc_attr( $distr_slug );?>" <?php myarcade_checked_array($general['translate_games'], $distr_slug); ?> />&nbsp;<?php echo esc_html( $distr_name ); ?><br />
                  <?php endforeach; ?>
                </td>
                <td><i><?php _e("Select game types you want to translate.", 'myarcadeplugin'); ?></i></td>
              </tr>

              <?php // Microsoft Translator API ?>
              <tr>
                <td colspan="2">
                  <h3><?php _e("Microsoft Translator Settings", 'myarcadeplugin'); ?></h3>
                </td>
              </tr>
              <tr><td colspan="2"><i><?php _e( "To be able to use Microsoft Translator you will need to register at <a href='https://azure.microsoft.com/' target='_blank'>Microsoft Azure.</a>.", 'myarcadeplugin' ); ?></i></td></tr>

              <tr><td colspan="2"><h4><?php _e("Azure Key (Key 1)", 'myarcadeplugin'); ?></h4></td></tr>
              <tr>
                <td>
									<input type="text" size="40" name="azure_key" value="<?php echo esc_attr( $general['azure_key'] ); ?>" />
                </td>
                <td><i><?php _e("Enter your Microsoft Azure subscription key.", 'myarcadeplugin' );?></i></td>
              </tr>

              <?php // Target Language ?>
              <tr><td colspan="2"><h4><?php _e("Target Language", 'myarcadeplugin'); ?></h4></td></tr>
              <tr>
                <td>
                  <?php
                  if (isset($languages_microsoft) ) {
                    ?><select size="1" name="translate_to" id="translate_to"><?php
                    foreach ($languages_microsoft as $code => $lang) {
											?><option value="<?php echo esc_attr( $code ); ?>" <?php myarcade_selected($general['translate_to'], $code); ?>><?php echo esc_html( $lang ); ?></option><?php
                    }
                    ?></select><?php
                  }
                  else {
                    _e("ERROR: Can't find bing language file!", 'myarcadeplugin');
                  }
                  ?>
                </td>
                <td><i><?php _e("Select the target language.", 'myarcadeplugin'); ?></i></td>
              </tr>


              <?php // Google Translator API ?>
              <tr>
                <td colspan="2">
                  <h3><?php _e("Google Translator Settings", 'myarcadeplugin'); ?></h3>
                </td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("API Key", 'myarcadeplugin'); ?></h4></td></tr>

              <tr>
                <td>
									<input type="text" size="40" name="google_id" value="<?php echo esc_attr( $general['google_id'] ); ?>" />
                </td>
                <td><i><?php _e('To be able to use Google Translation API v2 you will need to enter your API Key. Google Translator API is a payed service: <a href="https://cloud.google.com/translate/" target="_blank">Google Translate API</a>', 'myarcadeplugin'); ?></i></td>
              </tr>

              <?php // Target Language ?>
              <tr><td colspan="2"><h4><?php _e("Target Language", 'myarcadeplugin'); ?></h4></td></tr>
              <tr>
                <td>
                  <?php
                  if (isset($languages_google) ) {
                    ?><select size="1" name="google_translate_to" id="google_translate_to"><?php
                    foreach ($languages_google as $code => $lang) {
											?><option value="<?php echo esc_attr( $code ); ?>" <?php myarcade_selected($general['google_translate_to'], $code); ?>><?php echo esc_html( $lang ); ?></option><?php
                    }
                    ?></select><?php
                  }
                  else {
                    _e("ERROR: Can't find google language file!", 'myarcadeplugin');
                  }
                  ?>
                </td>
                <td><i><?php _e("Select the target language.", 'myarcadeplugin'); ?></i></td>
              </tr>

              <?php // Yandex Translator API ?>
              <tr>
                <td colspan="2">
                  <h3><?php _e("Yandex Translator Settings", 'myarcadeplugin'); ?></h3>
                </td>
              </tr>

              <tr><td colspan="2"><h4><?php _e("API Key", 'myarcadeplugin'); ?></h4></td></tr>

              <tr>
                <td>
									<input type="text" size="40" name="yandex_key" value="<?php echo esc_attr( $general['yandex_key'] ); ?>" />
                </td>
                <td><i><?php _e('To be able to use Yandex Translator you will need to enter your API Key. Click here to get an API key: <a href="https://tech.yandex.com/translate/" target="_blank">Yandex Translator</a>', 'myarcadeplugin'); ?></i></td>
              </tr>

              <?php // Target Language ?>
              <tr><td colspan="2"><h4><?php _e("Target Language", 'myarcadeplugin'); ?></h4></td></tr>
              <tr>
                <td>
                  <?php
                  if (isset($languages_yandex) ) {
                    ?><select size="1" name="yandex_translate_to" id="yandex_translate_to"><?php
                    foreach ($languages_yandex as $code => $lang) {
											?><option value="<?php echo esc_attr( $code ); ?>" <?php myarcade_selected($general['yandex_translate_to'], $code); ?>><?php echo esc_html( $lang ); ?></option><?php
                    }
                    ?></select><?php
                  }
                  else {
                    _e("ERROR: Can't find google language file!", 'myarcadeplugin');
                  }
                  ?>
                </td>
                <td><i><?php _e("Select the target language.", 'myarcadeplugin'); ?></i></td>
              </tr>

            </table>
            <input class="button button-primary" id="submit" type="submit" name="submit" value="<?php _e("Save Settings", 'myarcadeplugin'); ?>" />
          </div>
        </div>

        <?php
        //----------------------------------------------------------------------
        // Category Mapping
        //----------------------------------------------------------------------
        ?>
        <h2 class="trigger"><?php myarcade_premium_span(); _e("Category Mapping", 'myarcadeplugin'); ?></h2>
        <div class="toggle_container">
          <div class="block">
            <?php myarcade_premium_message(); ?>
            <script type="text/javascript">
              function myabp_add_map(category, section) {
                if( typeof(section) === 'undefined' ) {
                  section = "general";
                }

                var selection = jQuery("#" + section +"_cat_" + category).val();
                jQuery('#'+section+'_load_'+ category).html('<div class=\'gload\'> </div>');

                jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>',
                  {
                    action:'myarcade_handler',
                    func:'addmap',
                    feedcat:category,
                    mapcat:selection,
                    section:section
                  },
                  function(data) {
                    jQuery('#' + section + '_map_' + category).append(data);
                    jQuery('#' + section + '_load_' + category).html('');
                  }
                );
              }

              function myabp_del_map(mapid, category, section) {
                if( typeof(section) === 'undefined' ) {
                  section = "general";
                }
                jQuery('#' + section + '_load_' + category).html('<div class=\'gload\'> </div>');

                jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>',
                  {
                    action:'myarcade_handler',
                    func:'delmap',
                    feedcat:category,
                    mapcat:mapid,
                    section:section
                  },
                  function(data){
                    jQuery('#' + section + '_delmap_'+mapid+'_'+category).fadeOut('fast', function() {
                      jQuery('#' + section + '_delmap_'+mapid+'_'+category).remove();
                    });
                    jQuery('#' + section + '_load_'+category).html('');
                  }
                );
              }
            </script>

            <table class="optiontable" width="100%">
              <tr>
                <td colspan="4">
                  <i>
                    <?php _e("Map default categories to your own category names. This feature allows you to publish games in translated or summarized categories instead of using the predefined category names. (optional)", 'myarcadeplugin'); ?>
                  </i>
                  <br /><br />
                </td>
              </tr>
              <tr>
                <td width="20%"><a name="mapcats"></a><strong><?php _e("Feed Category", 'myarcadeplugin'); ?></strong></td>
                <td width="20%"><strong><?php _e("Category", 'myarcadeplugin'); ?></strong></td>
                <td width="20%"><strong><?php _e("Add Mapping", 'myarcadeplugin'); ?></strong></td>
                <td><strong><?php _e("Current Mappings", 'myarcadeplugin'); ?></strong></td>
              </tr>
              <?php foreach ($categories as $feedcat) : ?>
              <tr>
								<td><?php echo esc_html( $feedcat['Name'] ); ?></td>
                <td>
                  <?php
                  $output  = '<select id="general_cat_'.$feedcat['Slug'].'">';
                  $output .=  '<option value="0">---Select---</option>';
                  foreach ($categs_tmp as $cat_tmp_id => $cat_tmp_val) {
										$output .= '<option value="' . esc_attr($cat_tmp_id) . '" />' . esc_html( $cat_tmp_val ) . '</option>';
                  }
                  $output .= '</select>';
									echo $output; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
                  ?>
                </td>
                <td>
                  <div style="width:100px">
									<div class="button-secondary" style="float:left;width:60px;text-align:center;" onclick="myabp_add_map('<?php echo esc_attr( $feedcat['Slug'] ); ?>', 'general');">
                    Add
                  </div>
									<div style="float:right;" id="general_load_<?php echo esc_attr( $feedcat['Slug'] ); ?>"> </div>
                  </div>
                </td>
                <td>
                  <?php if ( !empty($feedcat['Mapping']) ) { ?>
										<div class="tagchecklist" id="general_map_<?php echo esc_attr( $feedcat['Slug'] ); ?>">
                      <?php
                      $map_cat_ids = explode(',', $feedcat['Mapping']);
											$category_name = '';

											foreach ( $map_cat_ids as $map_cat_id ) {
												if ( $custom_cat_taxonomy ) {
													$custom_cat_tax = get_term_by( 'id', $map_cat_id, $general['custom_category'] );

													if ( $custom_cat_tax ) {
														$category_name = $custom_cat_tax->name;
													}
												} else {
													$category_name = get_cat_name( $map_cat_id );
												}
												?>
												<span id="general_delmap_<?php echo esc_attr( $map_cat_id ); ?>_<?php echo esc_attr( $feedcat['Slug'] ); ?>" class="remove_map">
													<img style="float:left;top:4px;position:relative;" src="<?php echo MYARCADE_URL; ?>/assets/images/remove.png" alt="UnMap" onclick="myabp_del_map('<?php echo esc_attr( $map_cat_id ); ?>', '<?php echo esc_attr( $feedcat['Slug'] ); ?>', 'general');" />&nbsp;<?php echo esc_html( $category_name ); ?>
                        </span>
                        <?php
                      }
                      ?>
                    </div>
                    <?php
                  }
                  else {
                    ?>
										<div class="tagchecklist" id="general_map_<?php echo esc_attr( $feedcat['Slug'] ); ?>"></div>
                    <?php
                  }
                  ?>
                </td>
              </tr>
              <tr>
                <td colspan="4">
                  <HR />
                </td>
              </tr>
              <?php endforeach; ?>

              <tr>
              <td colspan="4">
                <i>
                  <?php _e("The changes in this section are saved automatically.", 'myarcadeplugin'); ?>
                </i>
                <br /><br />
              </td>
            </tr>
            </table>
          </div>
        </div>

        <?php
        //----------------------------------------------------------------------
        // Advanced Features
        //----------------------------------------------------------------------
        ?>

        <script type="text/javascript">
          /* <![CDATA[ */
          function confirmDeleteGames() {
            if ( confirm("Are you sure you want to delete all fetched games?") ) {
              jQuery('#del_response').html('<div class=\'gload\'> </div>');
              jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {action:'myarcade_handler',func:'delgames'},function(data){jQuery('#del_response').html(data);});
            }
          }
          function confirmDeleteScores() {
            if ( confirm("Are you sure you want to delete all scores?") ) {
              jQuery('#score_response').html('<div class=\'gload\'> </div>');
              jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {action:'myarcade_handler',func:'delscores'},function(data){jQuery('#score_response').html(data);});
            }
          }
          /* ]]> */
        </script>

        <h2 class="trigger"><?php _e("Advanced Features", 'myarcadeplugin'); ?></h2>
        <div class="toggle_container" id="advanced_settings">
          <div class="block">
            <table class="optiontable" width="100%">
              <tr>
                <td colspan="3">
                  <p class="mabp_error" style="padding:10px">
                    <?php _e("Please, use this only if you know what you do!", 'myarcadeplugin'); ?>
                  </p>
                  <br />
                </td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Delete All Feeded Games", 'myarcadeplugin'); ?></h3></td></tr>

              <tr>
                <td width="160">
                  <div class="button-secondary" style="float:left;text-align:center;" onclick="return confirmDeleteGames();">
                    <?php _e("Reset Feeded Games", 'myarcadeplugin'); ?>
                  </div>
                </td>
                <td width="30"><div id="del_response"></div></td>
                <td><i><?php _e("Attention! All feeded or imported games will be deleted from the games database! Published posts will not be touched. After this score submitting of publiished games will stop working!", 'myarcadeplugin'); ?></i></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Remove Games Marked as 'deleted'", 'myarcadeplugin'); ?></h3></td></tr>

              <tr>
                <td>
                  <div class="button-secondary" style="float:left;text-align:center;" onclick="jQuery('#remove_response').html('<div class=\'gload\'> </div>');jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {action:'myarcade_handler',func:'remgames'},function(data){jQuery('#remove_response').html(data);});">
                    <?php _e("Remove 'deleted' Games", 'myarcadeplugin'); ?>
                  </div>
                </td>
                <td width="30"><div id="remove_response"></div></td>
                <td><i><?php _e("Attention! All games marked as 'deleted' will be removed from the database!", 'myarcadeplugin'); ?></i></td>
              </tr>


              <tr><td colspan="3"><h3><?php _e("Delete Blank / Zero Scores", 'myarcadeplugin'); ?></h3></td></tr>

              <tr>
                <td>
                  <div class="button-secondary" style="float:left;text-align:center;" onclick="jQuery('#zero_response').html('<div class=\'gload\'> </div>');jQuery.post('<?php echo admin_url('admin-ajax.php');  ?>', {action:'myarcade_handler',func:'zeroscores'},function(data){jQuery('#zero_response').html(data);});">
                    <?php _e("Delete Zero Scores", 'myarcadeplugin'); ?>
                  </div>
                </td>
                <td width="30"><div id="zero_response"></div></td>
                <td><i><?php _e("Clean your scores table. This will delete all zero and empty scores if present in your database.", 'myarcadeplugin'); ?></i></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Delete All Scores", 'myarcadeplugin'); ?></h3></td></tr>

              <tr>
                <td>
                  <div class="button-secondary" style="float:left;text-align:center;" onclick="return confirmDeleteScores();">
                    <?php _e("Delete All Scores", 'myarcadeplugin'); ?>
                  </div>
                </td>
                <td width="30"><div id="score_response"></div></td>
                <td><i><?php _e("Attention! All saved scores will be deleted!", 'myarcadeplugin'); ?></i></td>
              </tr>

              <tr><td colspan="3"><h3><?php _e("Load Default Settings", 'myarcadeplugin'); ?></h3></td></tr>

              <tr>
                <td colspan="3">
                  <p class="mabp_info" style="padding:10px"><?php _e("Attention! All MyArcadePlugin settings will be reset to default!", 'myarcadeplugin'); ?></p>
                  <form method="post" name="defaultsettings">
                    <input type="hidden" name="loaddefaults" id="loaddefaults" value="yes" />
										<input type="checkbox" name="checkdefaults" id="checkdefaults" value="yes" /> <?php esc_html_e( 'Yes, I want to load default settings', 'myarcadeplugin' ); ?> <input id="submitdefaults" type="submit" name="submitdefaults" class="button-secondary" value="<?php _e("Load Default Settings", 'myarcadeplugin'); ?>" disabled />
                  </form>
                  <script type="text/javascript">
                    /* <![CDATA[ */
                    jQuery("#checkdefaults").click(function() {
                      var checked_status = this.checked;
                      if (checked_status === true) {
                        jQuery("#submitdefaults").removeAttr("disabled");
                      } else {
                        jQuery("#submitdefaults").attr("disabled", "disabled");
                      }
                    });
                    /* ]]> */
                  </script>
                  <br />
                </td>
              </tr>

            </table>
          </div>
        </div>

        <?php
        //----------------------------------------------------------------------
        //----------------------------------------------------------------------
        // GAME FEEDS
        //----------------------------------------------------------------------
        ?>
        <div class="clear"></div>
        <hr />
        <h2><?php _e("Game Feeds", 'myarcadeplugin'); ?> <span style="float:right;font-size:16px;"><i><?php _e("ordered alphabetically", 'myarcadeplugin'); ?></i></span></h2>
        <hr />

        <?php
        // Load settings page dynamically for each distributor
				foreach ($distributors as $key => $name) {
          $settings_function = 'myarcade_settings_' . $key;

          // Get distributor integration file
					MyArcade()->load_distributor( $key );

          // Check if settings function exists
          if ( function_exists( $settings_function ) ) {
            $settings_function();
          }
          else {
            // Settings function doesn't exist. Maybe there are no settings available for this distributor.
          }
        }
        ?>
      </form>

      <div style="clear:both"></div>
    </div><?php // end id myarcade_settings ?>

   <div class="clear"></div>
  <?php

  myarcade_footer();
}

/**
 * Create categories from the given array
 *
 * @version 5.13.0
 * @access  public
 * @param   array $categories Categories to create
 * @return  void
 */
function myarcade_create_categories( $categories ) {

  if ( ! is_array( $categories ) ) {
    echo '<p class="mabp_error mabp_800">'.__("Can't create categories - Category array expected!", 'myarcadeplugin').'</p>';
    return;
  }

  $general = get_option( 'myarcade_general' );

  if ( $general['post_type'] == 'post' ) {
    foreach ($categories as $key => $name ) {
      // Get Cat ID
      $cat_id = get_cat_ID( htmlspecialchars( $name ) );

      if ( 0 == $cat_id ) {
        // Create Category
        $args = array(
          'cat_name'              => $name,
          'category_description'  => $name,
          'category_nicename'     => myarcade_make_slug( $name ),
          'category_parent'       => $general['parent']
        );

        if ( ! wp_insert_category( $args ) ) {
          echo '<p class="mabp_error mabp_800">'.__("Failed to create category:", 'myarcadeplugin').' '. $name .'</p>';
        }
      }
    }
  }
  else {
    // We have a custom post type... Check if custom taxonomy is selected
    if ( post_type_exists( $general['post_type'] ) ) {
      if ( !empty($general['custom_category']) && taxonomy_exists($general['custom_category']) ) {
        foreach ($categories as $key => $name ) {
          if ( !term_exists( $name, $general['custom_category'] ) ) {
            // Add custom taxonomy
            $result = wp_insert_term ( $name, $general['custom_category'], array( 'description' => $name, 'slug' => myarcade_make_slug( $name ) ) );

            if ( is_wp_error($result) ) {
              echo '<p class="mabp_error mabp_800">'.__("Failed to create category:", 'myarcadeplugin').' '.$result->get_error_message().'</p>';
            }
          }
        }
      }
    }
  }
}

/**
 * Load default MyArcadePlugin settings
 *
 * @version 5.19.0
 * @access  public
 * @return  void
 */
function myarcade_load_default_settings() {

	$distributors = MyArcade()->distributors();

  $default_settings = MYARCADE_CORE_DIR.'/settings.php';

  if ( file_exists($default_settings) ) {
    @include_once($default_settings);
  }
  else {
    wp_die('Required configuration file not found!', 'Error: MyArcadePlugin Activation');
  }

  update_option('myarcade_general', $myarcade_general_default);

	foreach ( $distributors as $key => $name ) {
    // Generate save settings function name
    $settings_default_function = 'myarcade_default_settings_' . $key;

    // Get distributor integration file
		MyArcade()->load_distributor( $key );

    // Check if function exists
    if ( function_exists( $settings_default_function ) ) {
      // Update settings
      $settings = $settings_default_function();

      update_option( 'myarcade_' . $key, $settings );
    }
  } // end foreach

  // Include the feed game categories
  $catfile = MYARCADE_CORE_DIR.'/feedcats.php';

  if ( file_exists($catfile) ) {
    @include_once($catfile);
  }
  else {
    wp_die('Required configuration file not found!', 'Error: MyArcadePlugin Activation');
  }

  update_option('myarcade_categories', $feedcategories);
}

/**
 * Check settings nonce
 *
 * @version 5.19.0
 * @since   5.19.0
 * @access  public
 * @return  [type] [description]
 */
function myarcade_check_settings_nonce() {
  $myarcade_nonce = filter_input( INPUT_POST, 'myarcade_save_settings_nonce');

  if ( ! $myarcade_nonce || ! wp_verify_nonce( $myarcade_nonce, 'myarcade_save_settings' ) ) {
    // Security check failed .. don't update settings
    wp_die( __('Security check failed. Please refresh the page and retry to submit settings again.', 'myarcadeplugin') );
  }
}
