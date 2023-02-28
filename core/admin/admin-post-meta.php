<?php
/**
 * Adds Meta Boxes to WordPress post
 *
 * @author Daniel Bakovic <contact@myarcadeplugin.com>
 */

// No direct Access
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Add meta box to a game post.
 *
 */
function myarcade_add_meta_box_conditionally() {
  // Get Post ID
  $post_id = filter_input( INPUT_GET, 'post', FILTER_VALIDATE_INT );
  if ( ! $post_id ) {
    $post_id = filter_input( INPUT_POST, 'post_ID', FILTER_VALIDATE_INT );

    if ( ! $post_id ) {
      return;
    }
  }

  $general = get_option( 'myarcade_general' );

  if ( is_game( $post_id ) || $general['post_type'] == get_post_type( $post_id ) ) {
    add_action('add_meta_boxes', 'myarcade_game_details_meta_box');
    add_action('save_post', 'myarcade_meta_box_save', 1);

    // Add scores meta box
    if ( is_leaderboard_game( $post_id ) ) {
      add_action('add_meta_boxes', 'myarcade_game_leaderboard_meta_box');
    }
  }
}
add_action( 'admin_init', 'myarcade_add_meta_box_conditionally' );

/**
 * Add MyArcade Game Details Meta Box
 *
 */
function myarcade_game_details_meta_box() {
	add_meta_box('myarcade-game-data', __('MyArcadePlugin Game Details', 'myarcadeplugin'), 'myarcade_game_data_box', MyArcade()->get_post_type(), 'normal', 'high');
}

function myarcade_game_leaderboard_meta_box() {
	add_meta_box('myarcade-game-scores', __('MyArcadePlugin Game Scores', 'myarcadeplugin'), 'myarcade_game_scores_box', MyArcade()->get_post_type(), 'normal', 'high');
}

/**
 * Displays the MyArcade Meta Box
 *
 */
function myarcade_game_data_box() {
	global $post, $postID;

  $postID = $post->ID;

  // Check if this post is a game
  $check_type = get_post_meta($postID, 'mabp_game_type', true);

  if( ! $check_type ) {
    ?>
    <p>
      <?php _e("This post is not a game.", 'myarcadeplugin'); ?>
    </p>
    <?php
    return;
  }

	$distributors     = MyArcade()->distributors();
	$custom_game_type = MyArcade()->custom_game_types();
	$game_types       = array_merge( $distributors, $custom_game_type );

  wp_nonce_field( 'myarcade_save_data', 'myarcade_meta_nonce' );
  ?>
  <div class="panel-wrap myarcade_game_data">
    <ul class="myarcade_data_tabs tabs" style="display:none;">
      <li class="active"><a href="#myarcade_game_data"><?php _e('Game Details', 'myarcadeplugin'); ?></a></li>
      <li class="files_tab"><a href="#myarcade_game_files"><?php _e('Game Files', 'myarcadeplugin'); ?></a></li>
    </ul>

    <?php // Display game details ?>
    <div id="myarcade_game_data" class="panel myarcade_game_panel">
      <div class="options_group">
        <?php
        myarcade_wp_textarea_input ( array (
            'id' => 'mabp_description',
            'label' => __('Game Description', 'myarcadeplugin')
        ));

        myarcade_wp_textarea_input ( array (
            'id' => 'mabp_instructions',
            'label' => __('Game Instructions', 'myarcadeplugin')
        ));

        myarcade_wp_text_input( array(
            'id' => 'mabp_height',
            'label' => __('Height', 'myarcadeplugin'),
            'description' => __('Game height in pixel (px)', 'myarcadeplugin')
        ));

        myarcade_wp_text_input( array(
            'id' => 'mabp_width',
            'label' => __('Width', 'myarcadeplugin'),
            'description' => __('Game width in pixel (px)', 'myarcadeplugin')
        ));

        myarcade_wp_select( array(
            'id' => 'mabp_game_type',
            'label' => __('Game Type', 'myarcadeplugin'),
						'options' => $game_types
        ));

        myarcade_wp_select( array(
            'id' => 'mabp_leaderboard',
            'label' => __('Score Support', 'myarcadeplugin'),
            'description' => __('Select if this game supports score submitting (Only IBPArcade games).'),
            'options' => array( '' => 'No', '1' => 'Yes')
        ));

        myarcade_wp_select( array(
            'id' => 'mabp_score_order',
            'label' => __('Score Order', 'myarcadeplugin'),
            'description' => __('How should MyArcadePlugin order scores for this game.'),
            'options' => array( 'DESC' => 'DESC (High to Low)', 'ASC' => 'ASC (Low to High)')
        ));
        ?>
      </div>
    </div>

    <?php // Display game files ?>
    <div id="myarcade_game_files" class="panel myarcade_game_panel">
      <div class="options_group">
        <?php
        // Game File
        $file_path = get_post_meta($post->ID, 'mabp_swf_url', true);
        $game_type = get_post_meta($post->ID, 'mabp_game_type', true);

        $embed_method = myarcade_get_embed_type( $game_type );

        if ( $embed_method == 'embed' || $embed_method == 'iframe' ) {
          $field = array( 'id' => 'mabp_swf_url', 'label' => __('Embed Code', 'myarcadeplugin') );
                  echo '<p class="myarcade-form-field"><label for="'.$field['id'].'">'.$field['label'].':</label>
          <textarea name="'.$field['id'].'" id="'.$field['id'].'">'.$file_path.'</textarea>
        </p>';
        }
        else {
          $field = array( 'id' => 'mabp_swf_url', 'label' => __('Game File', 'myarcadeplugin') );
          echo '<p class="myarcade-form-field"><label for="'.$field['id'].'">'.$field['label'].':</label>
          <input type="text" class="game_path" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$file_path.'" placeholder="'.__('File path / URL / Embed Code', 'myarcadeplugin').'" />
            <input type="button"  class="upload_game_button button" value="'.__('Upload a file', 'myarcadeplugin').'" />
        </p>';
        }

        $file_path = get_post_meta($post->ID, 'mabp_thumbnail_url', true);
        $field = array( 'id' => 'mabp_thumbnail_url', 'label' => __('Game Thumbnail', 'myarcadeplugin') );

        echo '<p class="myarcade-form-field"><label for="'.$field['id'].'">'.$field['label'].':</label>
          <input type="text" class="thumbnail_path" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$file_path.'" placeholder="'.__('File path / URL', 'myarcadeplugin').'" />
          <input type="button"  class="upload_thumbnail_button button" value="'.__('Upload a file', 'myarcadeplugin').'" />
        </p>';

        $file_path = get_post_meta($post->ID, 'mabp_screen1_url', true);
        $field = array( 'id' => 'mabp_screen1_url', 'label' => __('Game Screenshot No. 1', 'myarcadeplugin') );

        echo '<p class="myarcade-form-field"><label for="'.$field['id'].'">'.$field['label'].':</label>
          <input type="text" class="screen1_path" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$file_path.'" placeholder="'.__('File path / URL', 'myarcadeplugin').'" />
          <input type="button"  class="upload_screen1_button button" value="'.__('Upload a file', 'myarcadeplugin').'" />
        </p>';

        $file_path = get_post_meta($post->ID, 'mabp_screen2_url', true);
        $field = array( 'id' => 'mabp_screen2_url', 'label' => __('Game Screenshot No. 2', 'myarcadeplugin') );

        echo '<p class="myarcade-form-field"><label for="'.$field['id'].'">'.$field['label'].':</label>
          <input type="text" class="screen2_path" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$file_path.'" placeholder="'.__('File path / URL', 'myarcadeplugin').'" />
          <input type="button"  class="upload_screen2_button button" value="'.__('Upload a file', 'myarcadeplugin').'" />
        </p>';

        $file_path = get_post_meta($post->ID, 'mabp_screen3_url', true);
        $field = array( 'id' => 'mabp_screen3_url', 'label' => __('Game Screenshot No. 3', 'myarcadeplugin') );

        echo '<p class="myarcade-form-field"><label for="'.$field['id'].'">'.$field['label'].':</label>
          <input type="text" class="screen3_path" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$file_path.'" placeholder="'.__('File path / URL', 'myarcadeplugin').'" />
          <input type="button"  class="upload_screen3_button button" value="'.__('Upload a file', 'myarcadeplugin').'" />
        </p>';

        $file_path = get_post_meta($post->ID, 'mabp_screen4_url', true);
        $field = array( 'id' => 'mabp_screen4_url', 'label' => __('Game Screenshot No. 4', 'myarcadeplugin') );

        echo '<p class="myarcade-form-field"><label for="'.$field['id'].'">'.$field['label'].':</label>
          <input type="text" class="screen4_path" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$file_path.'" placeholder="'.__('File path / URL', 'myarcadeplugin').'" />
          <input type="button"  class="upload_screen4_button button" value="'.__('Upload a file', 'myarcadeplugin').'" />
        </p>';

        myarcade_wp_text_input( array(
            'id' => 'mabp_video_url',
            'label' => __('Video URL', 'myarcadeplugin'),
            'description' => __('Paste a game play video URL (YouTube Link) here.', 'myarcadeplugin')
        ));
        ?>
      </div>
    </div>
  </div>
  <script type="text/javascript">
    // Uploading files
    var file_path_field;
    window.send_to_editor_default = window.send_to_editor;

    jQuery('.upload_thumbnail_button').on('click', function(event){
      event.preventDefault();
      var custom_uploader = wp.media.frames.file_frame = wp.media({ multiple: false });
      custom_uploader.on('select', function() {
        var attachment = custom_uploader.state().get("selection").first().toJSON();
        $('#thumbnail_path').val(attachment.url);
      });
      custom_uploader.open();
    });

    jQuery('.upload_game_button').on('click', function(event){
      event.preventDefault();
      var custom_uploader = wp.media.frames.file_frame = wp.media({ multiple: false });
      custom_uploader.on('select', function() {
        var attachment = custom_uploader.state().get("selection").first().toJSON();
        $('#game_path').val(attachment.url);
      });
      custom_uploader.open();
    });

    jQuery('.upload_screen1_button').on('click', function(event){
      event.preventDefault();
      var custom_uploader = wp.media.frames.file_frame = wp.media({ multiple: false });
      custom_uploader.on('select', function() {
        var attachment = custom_uploader.state().get("selection").first().toJSON();
        $('#mabp_screen1_url').val(attachment.url);
      });
      custom_uploader.open();
    });

    jQuery('.upload_screen2_button').on('click', function(event){
      event.preventDefault();
      var custom_uploader = wp.media.frames.file_frame = wp.media({ multiple: false });
      custom_uploader.on('select', function() {
        var attachment = custom_uploader.state().get("selection").first().toJSON();
        $('#mabp_screen2_url').val(attachment.url);
      });
      custom_uploader.open();
    });

    jQuery('.upload_screen3_button').on('click', function(event){
      event.preventDefault();
      var custom_uploader = wp.media.frames.file_frame = wp.media({ multiple: false });
      custom_uploader.on('select', function() {
        var attachment = custom_uploader.state().get("selection").first().toJSON();
        $('#mabp_screen3_url').val(attachment.url);
      });
      custom_uploader.open();
    });

    jQuery('.upload_screen4_button').on('click', function(event){
      event.preventDefault();
      var custom_uploader = wp.media.frames.file_frame = wp.media({ multiple: false });
      custom_uploader.on('select', function() {
        var attachment = custom_uploader.state().get("selection").first().toJSON();
        $('#mabp_screen4_url').val(attachment.url);
      });
      custom_uploader.open();
    });
  </script>
  <?php
}

/**
 * Display the MyArcade Scores Meta Box
 *
 */
function myarcade_game_scores_box() {
  global $post, $wpdb;

  if ( ! isset( $post->ID ) ) {
    _e( "ERROR: Post ID not found!", 'myarcadeplugin' );
    return;
  }

  $game_tag = get_post_meta( $post->ID, 'mabp_game_tag', true );
  $order    = get_post_meta( $post->ID, 'mabp_score_order', true );

  if ( ! $order ) {
    $order = "DESC";
  }

	$scores = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}myarcadescores WHERE game_tag = %s ORDER BY score+0 {$order}", $game_tag ) );

  if ( $scores ) {
    ?>
    <script type="text/javascript">
      /* <![CDATA[ */
      function myarcade_confirm_delete() {
        if ( confirm( "<?php _e("Are you sure you want to delete all game scores?", 'myarcadeplugin'); ?>" ) ) {
          jQuery('#delete_game_scores').css('display', 'inline');
          jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', {
            action:'myarcade_handler',
						game_tag: <?php echo esc_attr( $game_tag ); ?>,
            func:'delete_game_scores'
          },
          function() {
            jQuery('#game_scores').fadeOut('slow');
            jQuery('#delete_game_scores').removeAttr('style');
            jQuery('#myarcade-game-scores .inside').html( "<?php _e( "No scores available.", 'myarcadeplugin' ); ?>" );
          });
        }
        return false;
      }
      /* ]]> */
    </script>

    <p>
      <button class="button-primary" onclick="return myarcade_confirm_delete();"><?php _e("Delete All Scores", 'myarcadeplugin') ?></button><span id="delete_game_scores" class="spinner"></span>
    </p>

    <table id="game_scores" class="widefat fixed">
      <thead>
      <tr>
        <th scope="col">User</th>
        <th scope="col">Date</th>
        <th scope="col">Score</th>
        <th scope="col">Action</th>
      </tr>
      </thead>
      <tbody>
          <?php
				foreach ( $scores as $score ):
          $user = get_user_by('id', $score->user_id);

          $edit_url = MYARCADE_URL.'/core/editscore.php?scoreid='.$score->id;
          ?>
					<tr id="scorerow_<?php echo esc_attr( $score->id ); ?>">
						<td><?php echo esc_html( $user->display_name ); ?></td>
						<td><?php echo esc_html( $score->date ); ?></td>
						<td id="scoreval_<?php echo esc_attr( $score->id ); ?>"><?php echo esc_html( $score->score ); ?></td>
						<td>
							<a href="<?php echo esc_url( $edit_url ); ?>&keepThis=true&TB_iframe=1&height=300&width=500" class="button-secondary thickbox edit" title="<?php esc_html_e( 'Edit Score', 'myarcadeplugin' ); ?>"><?php esc_html_e( 'Edit', 'myarcadeplugin' ); ?></a>
							<button class="button-secondary" onclick="jQuery('#delete_game_scores').css('display', 'inline');jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>',{action:'myarcade_handler',gameid: false, scoreid: '<?php echo esc_attr( $score->id ); ?>',func:'delete_score'},function(){jQuery('#scorerow_<?php echo esc_attr( $score->id ); ?>').fadeOut('slow');jQuery('#delete_game_scores').removeAttr('style');});return false;"><?php echo esc_html__( 'Delete', 'myarcadeplugin' ); ?></button>
							<span id="score_<?php echo esc_attr( $score->id ); ?>"></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php
  }
  else {
    _e( "No scores available.", 'myarcadeplugin' );
  }
}

/**
 * Update MyArcade Meta Box values
 *
 * @param   int $post_id    Post ID
 */
function myarcade_meta_box_save($post_id) {

  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
    return $post_id;
  }

  $nonce = filter_input( INPUT_POST, 'myarcade_meta_nonce' );

  if ( ! $nonce || ! wp_verify_nonce( $nonce, 'myarcade_save_data' ) ) {
    return $post_id;
  }

  if ( !current_user_can( 'edit_post', $post_id )) {
    return $post_id;
  }

  update_post_meta( $post_id, 'mabp_game_type', filter_input( INPUT_POST, 'mabp_game_type' ) );
  update_post_meta( $post_id, 'mabp_height', filter_input( INPUT_POST, 'mabp_height' ) );
  update_post_meta( $post_id, 'mabp_width', filter_input( INPUT_POST, 'mabp_width' ) );
  update_post_meta( $post_id, 'mabp_description',  filter_input( INPUT_POST, 'mabp_description' ) );
  update_post_meta( $post_id, 'mabp_instructions', filter_input( INPUT_POST, 'mabp_instructions' ) );
  update_post_meta( $post_id, 'mabp_leaderboard', filter_input( INPUT_POST, 'mabp_leaderboard' ) );
  update_post_meta( $post_id, 'mabp_score_order', filter_input( INPUT_POST, 'mabp_score_order' ) );
  update_post_meta( $post_id, 'mabp_score_bridge', filter_input( INPUT_POST, 'mabp_score_bridge' ) );
  update_post_meta($post_id, 'mabp_thumbnail_url', filter_input( INPUT_POST, 'mabp_thumbnail_url' ) );
  update_post_meta($post_id, 'mabp_swf_url', filter_input( INPUT_POST, 'mabp_swf_url' ) );
  update_post_meta($post_id, 'mabp_video_url', filter_input( INPUT_POST, 'mabp_video_url' ) );

  for ( $i = 1; $i <= 4; $i++ ) {
    $fieled = "mabp_screen{$i}_url";
    update_post_meta( $post_id, $fieled, filter_input( INPUT_POST, $fieled ) );
  }
}

/**
 * Generate a text input field
 *
 * @param   array $field Field params
 */
function myarcade_wp_text_input( $field ) {
  global $postID, $post;

  if ( !$postID ) {
    $postID = $post->ID;
  }

  if (!isset($field['placeholder'])) {
    $field['placeholder'] = '';
  }

  if (!isset($field['class'])) {
    $field['class'] = 'short';
  }

  if (!isset($field['value'])) {
    $field['value'] = get_post_meta($postID, $field['id'], true);
  }

  echo '<p class="myarcade-form-field '.$field['id'].'_field"><label for="'.$field['id'].'">'.$field['label'].'</label><input type="text" class="'.$field['class'].'" name="'.$field['id'].'" id="'.$field['id'].'" value="'.esc_attr( $field['value'] ).'" placeholder="'.$field['placeholder'].'" /> ';

  if (isset($field['description'])) {
    echo '<span class="description">' .$field['description'] . '</span>';
  }

  echo '</p>';
}

/**
 * Generate a text area field
 *
 * @param   array $field Field params
 */
function myarcade_wp_textarea_input( $field ) {
  global $postID, $post;

  if (!$postID) {
    $postID = $post->ID;
  }

  if (!isset($field['placeholder'])) {
    $field['placeholder'] = '';
  }

  if (!isset($field['class'])) {
    $field['class'] = 'short';
  }

  if (!isset($field['value'])) {
    $field['value'] = get_post_meta($postID, $field['id'], true);
  }

  echo '<p class="myarcade-form-field"><label for="'.$field['id'].'">'.$field['label'].'</label>';
  wp_editor( $field['value'], $field['id'], array( 'editor_height' => 200, 'media_buttons' => false, 'teeny' => true, ) );

  if ( isset( $field['description'] ) && $field['description'] ) {
      echo '<span class="description">' . $field['description'] . '</span>';
  }
  echo '</p>';
}

/**
 * Generate a select field
 *
 * @param   array $field Field params
 */
function myarcade_wp_select( $field ) {
  global $postID, $post;

  if (!$postID) {
    $postID = $post->ID;
  }

  if (!isset($field['class'])) {
    $field['class'] = 'select short';
  }

  if (!isset($field['value'])) {
    $field['value'] = get_post_meta($postID, $field['id'], true);
  }

  echo '<p class="myarcade-form-field '.$field['id'].'_field"><label for="'.$field['id'].'">'.$field['label'].'</label><select id="'.$field['id'].'" name="'.$field['id'].'" class="'.$field['class'].'">';

  foreach ($field['options'] as $key => $value) {
    echo '<option value="'.$key.'" ';
    selected($field['value'], $key);
    echo '>'.$value.'</option>';
  }

  echo '</select> ';

  if ( isset( $field['description'] ) && $field['description'] ) {
    echo '<span class="description">' . $field['description'] . '</span>';
  }

  echo '</p>';
}

/**
 * Generate a checkbox input field
 *
 * @param   array $field Field params
 */
function myarcade_wp_checkbox( $field ) {
  global $postID, $post;

  $postID                 = empty( $postID ) ? $post->ID : $postID;
  $field['class']         = isset( $field['class'] ) ? $field['class'] : 'checkbox';
  $field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $postID, $field['id'], true );
  $field['cbvalue']       = isset( $field['cbvalue'] ) ? $field['cbvalue'] : 'yes';
  $field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

  echo '<p class="myarcade-form-field ' . esc_attr( $field['id'] ) . '_field"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><input type="checkbox" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['cbvalue'] ) . '" ' . checked( $field['value'], $field['cbvalue'], false ) . ' /> ';

  if ( ! empty( $field['description'] ) ) echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';

  echo '</p>';
}
