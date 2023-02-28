<?php
/**
 * Displays the manage games page on backend
 *
 * @package MyArcadePlugin/Admin/Game/Manage
 */

// No direct access.
if( !defined( 'ABSPATH' ) ) {
  die();
}

/**
 * Manage Games.
 */
function myarcade_manage_games() {
	global $wpdb;

  myarcade_header();
  ?>
  <div id="icon-options-general" class="icon32"><br /></div>
	<h2><?php esc_html_e( 'Manage Games', 'myarcadeplugin' ); ?></h2>
  <br />
  <script type="text/javascript">
    function checkSeachForm() {
      if ( document.searchForm.q.value === "") {
				alert("<?php esc_html_e( 'Search term was not entered!', 'myarcadeplugin' ); ?>");
        document.searchForm.q.focus();
        return false;
      }
    }
  </script>
  <?php

  $feedcategories = get_option('myarcade_categories');

	$game_type          = filter_input( INPUT_POST, 'distr', FILTER_UNSAFE_RAW, array( 'options' => array( 'default' => 'all' ) ) );
	$leaderboard        = filter_input( INPUT_POST, 'leaderboard', FILTER_UNSAFE_RAW );
	$status             = filter_input( INPUT_POST, 'status', FILTER_UNSAFE_RAW, array( 'options' => array( 'default' => 'all' ) ) );
	$search             = filter_input( INPUT_POST, 'q' );
	$order              = filter_input( INPUT_POST, 'order', FILTER_UNSAFE_RAW, array( 'options' => array( 'default' => 'ASC' ) ) );
	$orderby            = filter_input( INPUT_POST, 'orderby', FILTER_UNSAFE_RAW, array( 'options' => array( 'default' => 'id' ) ) );
	$cat                = filter_input( INPUT_POST, 'category', FILTER_UNSAFE_RAW, array( 'options' => array( 'default' => 'all' ) ) );
	$games              = filter_input( INPUT_POST, 'games', FILTER_SANITIZE_NUMBER_INT, array( 'options' => array( 'default' => '50' ) ) );
	$offset             = filter_input( INPUT_POST, 'offset', FILTER_UNSAFE_RAW, array( 'options' => array( 'default' => '0' ) ) );
  $enable_delete = filter_input( INPUT_POST, 'enable_delete' );
  $bulk_delete_button = filter_input( INPUT_POST, 'bulk_delete_button' );
	$action             = filter_input( INPUT_POST, 'action' );
  $results = false;

	if ( 'search' === $action ) {
    $query_array = array();

    if ($search) {
      $query_array[] = "(name LIKE '%".$search."%' OR description LIKE '%".$search."%')";
    }

    if ( $game_type != 'all' ) {
      $query_array[] = "game_type = '".$game_type."'";
    }

    if ( $leaderboard ) {
      $query_array[] = "leaderboard_enabled = '1'";
    }

    if ( $status != 'all' ) {
      $query_array[] = "status = '".$status."'";
    }

    if ( $cat != 'all' ) {
      foreach ($feedcategories as $category) {
        if ($category['Slug'] == $cat) {
          $query_array[] = "categories LIKE '%".$category['Name']."%'";
          break;
        }
      }
    }

    $count = count($query_array);
    $query_string = '';

    if ( $count > 1) {
      for($i=0; $i < $count; $i++) {
        $query_string .= $query_array[$i];
        if ( $i < ($count - 1) ) {
          $query_string .= ' AND ';
        }
      }
    }
    else {
      if ( $count ) {
        $query_string = $query_array[0];
      }
    }

    if ( !empty($query_string) ) {
      $query_string = " WHERE ".$query_string;
    }

		if ( 'yes' === $enable_delete && $bulk_delete_button ) {
      // Delete published posts first
			if ( 'published' === $status || 'all' === $status ) {
				$post_ids = $wpdb->get_results( "SELECT postid FROM {$wpdb->prefix}myarcadegames {$query_string}" );
        if ( $post_ids ) {
          foreach ( $post_ids as $key => $value ) {
            if ( isset( $value->postid ) ) {
              wp_delete_post( intval( $value->postid ), true );
            }
          }
        }
      }

			// Now delete fetched games.
			$wpdb->query( "DELETE FROM {$wpdb->prefix}myarcadegames {$query_string}" );
    }
    else {
      // Generate the query
			$query       = "SELECT * FROM {$wpdb->prefix}myarcadegames {$query_string} ORDER BY {$orderby} {$order} limit {$offset}, {$games}";
			$results     = $wpdb->get_results( $query );
			$query_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}myarcadegames {$query_string}" );


      if (!$results) {
				echo '<div class="mabp_error" style="width:685px">' . esc_html__( 'Nothing found!', 'myarcadeplugin' ) . '</strong></div>';
      }
    }
  }

	$distributors = MyArcade()->distributors();
  ?>
  <form method="post" action="" class="myarcade_form" name="searchForm">
    <input type="hidden" name="action" value="search" />
    <div class="myarcade_border grey" style="width:680px">
      <?php _e("Search for", 'myarcadeplugin'); ?>
			<input type="text" size="40" name="q" value="<?php echo esc_attr( $search ); ?>" />

      <p class="myarcade_hr">&nbsp;</p>

      <div class="myarcade_border white" style="width:300px;float:left;height:30px;">
        <?php _e("Type", 'myarcadeplugin'); ?>:
        <select name="distr" id="distr">
					<option value="all" <?php myarcade_selected($game_type, 'all'); ?>><?php _e( 'All', 'myarcadeplugin' ); ?></option>
          <optgroup label="Game Distributors">
						<?php foreach ( $distributors as $slug => $name) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php myarcade_selected($game_type, $slug); ?>><?php echo esc_html( $name ); ?></option>
            <?php endforeach; ?>
          </optgroup>
          <optgroup label="Imported Games">
            <option value="html5" <?php myarcade_selected($game_type, 'html5'); ?>><?php _e("HTML5 Games", 'myarcadeplugin');?></option>
            <option value="embed" <?php myarcade_selected($game_type, 'embed'); ?>><?php _e("Embed Codes", 'myarcadeplugin'); ?></option>
            <option value="iframe" <?php myarcade_selected($game_type, 'iframe'); ?>><?php _e("Iframe (URL)", 'myarcadeplugin'); ?></option>
            <option value="ibparcade" <?php myarcade_selected($game_type, 'ibparcade'); ?>><?php _e("IBPArcade Games", 'myarcadeplugin'); ?></option>
            <option value="phpbb" <?php myarcade_selected($game_type, 'phpbb'); ?>><?php _e("PHPBB Games", 'myarcadeplugin'); ?></option>
            <option value="dcr" <?php myarcade_selected($game_type, 'dcr'); ?>><?php _e("Shockwave Games (DCR)", 'myarcadeplugin'); ?></option>
            <option value="custom" <?php myarcade_selected($game_type, 'custom'); ?>><?php _e("Flash Games (SWF)", 'myarcadeplugin'); ?></option>
          </optgroup>
        </select>
      </div>

      <div class="myarcade_border white" style="width:300px;float:left;margin-left:20px;height:25px;padding-top: 15px">
        <?php _e('Leaderboard Enabled', 'myarcadeplugin'); ?>:
        <input type="checkbox" name="leaderboard" value="1" <?php myarcade_checked($leaderboard, '1'); ?> />&nbsp;&nbsp;&nbsp;
      </div>

      <div class="clear"> </div>

      <div class="myarcade_border white" style="width:300px;height:30px;float:left;">
        <?php _e("Game Status", 'myarcadeplugin'); ?>:
        <select name="status" id="status">
					<option value="all" <?php myarcade_selected($status, 'all'); ?>><?php _e( 'All', 'myarcadeplugin' ); ?></option>
					<option value="new" <?php myarcade_selected($status, 'new'); ?>><?php _e( 'New', 'myarcadeplugin' ); ?></option>
					<option value="published" <?php myarcade_selected($status, 'published'); ?>><?php _e( 'Published', 'myarcadeplugin' ); ?></option>
					<option value="deleted" <?php myarcade_selected($status, 'deleted'); ?>><?php _e( 'Deleted', 'myarcadeplugin' ); ?></option>
        </select>
      </div>

      <div class="myarcade_border white" style="width:300px;height:30px;float:left;margin-left:20px;">
        <?php _e("Order", 'myarcadeplugin'); ?>:
        <select name="order" id="order">
					<option value="ASC" <?php myarcade_selected($order, 'ASC'); ?>><?php _e( 'ASC', 'myarcadeplugin' ); ?></option>
					<option value="DESC" <?php myarcade_selected($order, 'DESC'); ?>><?php _e( 'DESC', 'myarcadeplugin' ); ?></option>
        </select>
        <?php _e("by", 'myarcadeplugin');?>:
        <select name="orderby" id="orderby">
					<option value="id" <?php myarcade_selected($orderby, 'id'); ?>><?php _e( 'ID', 'myarcadeplugin' ); ?></option>
					<option value="name" <?php myarcade_selected($orderby, 'name'); ?>><?php _e( 'Name', 'myarcadeplugin' ); ?></option>
					<option value="slug" <?php myarcade_selected($orderby, 'slug'); ?>><?php _e( 'Slug', 'myarcadeplugin' ); ?></option>
					<option value="game_type" <?php myarcade_selected($orderby, 'game_type'); ?>><?php _e( 'Game Type', 'myarcadeplugin' ); ?></option>
					<option value="status" <?php myarcade_selected($orderby, 'status'); ?>><?php _e( 'Status', 'myarcadeplugin' ); ?></option>
        </select>
      </div>

      <div class="clear"> </div>

      <div class="myarcade_border white" style="width:300px;height:30px;float:left;">
        <?php _e("Game Category", 'myarcadeplugin'); ?>:
        <select name="category" id="category">
          <option value="all" <?php myarcade_selected($cat, 'all'); ?>>All</option>
          <?php
            foreach ( $feedcategories as $category) {
							?><option value="<?php echo esc_attr( $category['Slug'] ); ?>" <?php myarcade_selected($cat, $category['Slug']); ?>><?php echo esc_html( $category['Name'] ); ?></option><?php
            }
          ?>
        </select>
      </div>

      <div class="myarcade_border white" style="width:300px;height:30px;float:left;margin-left:20px;">
        <?php _e("Display", 'myarcadeplugin'); ?>
				<input type="text" size="3" name="games" value="<?php echo esc_attr( $games ); ?>" />
        <?php _e("games from offset", 'myarcadeplugin'); ?>
				<input type="text" size="3" name="offset" value="<?php echo esc_attr( $offset ); ?>" />
      </div>

      <div style="padding: 10px;width:300px;height:30px;float:left;">
        <input class="button-primary" id="submit" type="submit" name="submit" value="Search" />
      </div>

      <div class="myarcade_border bulk-delete" style="width:300px;height:25px;margin-right:15px;padding-top: 15px">
        <input type="checkbox" name="enable_delete" id="enable_delete" value="yes" /> <?php _e("Yes, delete games", 'myarcadeplugin' ) ?>
        <input type="submit" id="bulk_delete_button" name="bulk_delete_button" class="button-secondary" style="float:right;margin-top:-4px" onclick="return confirmBulkDelete();" disabled value="<?php _e("Bulk Delete", 'myarcadeplugin' ) ?>" />
      </div>

      <div class="clear"></div>
    </div>
  </form>

  <?php
  if ( "yes" == $enable_delete && $bulk_delete_button ) {
    echo '<div class="mabp_info" style="width:685px">'.__("Bulk Delete executed successfully!", 'myarcadeplugin').'</strong></div>';
  }
  elseif ( $results ) {
    echo '<div class="mabp_info" style="width:685px">'.sprintf(__("Results found: <strong>%s</strong>. Displaying results from <strong>%s</strong> to <strong>%s</strong>.", 'myarcadeplugin'), $query_count, $offset, $games + $offset).'</div>';

    foreach ($results as $game) {
      myarcade_show_game($game);
    }
	} else {
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}myarcadegames" );

    if ( $count ) {

      /* Begin Pagination */

      // This is the number of results displayed per page
      $page_rows = 50;

      // This tells us the page number of our last page
      $last = ceil($count / $page_rows);

      // This makes sure the page number isn't below one, or more than our maximum pages
      $pagenum = 1;

      if ( isset($_GET['pagenum']) ) {
        $pagenum = $_GET['pagenum'];
      }

      if ($pagenum < 1)  {
        $pagenum = 1;
      }
      elseif ($pagenum > $last)  {
        $pagenum = $last;
      }

      // This sets the range to display in our query
      $range = 'limit ' .($pagenum - 1) * $page_rows .',' .$page_rows;

      // Calculate counts for next and previous
      if ( $pagenum != $last) {
        $next = $pagenum + 1;
      }

      if ($pagenum > 1) {
        $previous = $pagenum - 1;
      }

      // Generate from .. to
      $from = 1 + ($pagenum - 1) * $page_rows;

      if ($pagenum < $last) {
        $to = $from + $page_rows - 1;
      }
      else {
        $to = $count;
      }

      $from_to = $from.' - '.$to;

      /* End Paginagion */

      // Last feeded games
			$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}myarcadegames ORDER BY ID DESC {$range}" );

      if ($results) {
        echo '<h3>'.__("Browser Your Game Catalog", 'myarcadeplugin').'</h3>';
        ?>
        <!-- Print pagination -->
        <div class="tablenav" style="float: left;">
          <div class="tablenav-pages">
						<span class="displaying-num"><?php printf( esc_html__( 'Displaying %d of %d', 'myarcadeplugin'), $from_to, $count ); ?></span>
          <?php if ($pagenum > 1) : ?>
							<a class='page-numbers' href='<?php echo esc_attr( $_SERVER['PHP_SELF'] );?>?page=myarcade-manage-games&pagenum=1'><?php esc_html_e( 'First', 'myarcadeplugin' ); ?></a>
							<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=myarcade-manage-games&pagenum=<?php echo esc_attr( $previous ); ?>'><?php esc_html_e( 'Previous', 'myarcadeplugin' ); ?></a>
            <?php endif; ?>
						<span class='page-numbers current'><?php echo esc_html( $pagenum ); ?></span>
            <?php if ($pagenum != $last) : ?>
							<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=myarcade-manage-games&pagenum=<?php echo esc_attr( $next ); ?>'><?php esc_html_e( 'Next', 'myarcadeplugin' ); ?></a>
							<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=myarcade-manage-games&pagenum=<?php echo esc_attr( $last ); ?>'><?php esc_html_e( 'Last', 'myarcadeplugin' ); ?></a>
            <?php endif; ?>
          </div>
        </div>

        <?php
        foreach ($results as $game) {
          myarcade_show_game($game);
        }
        ?>

        <!-- Print pagination -->
        <div class="tablenav" style="float: left;">
          <div class="tablenav-pages">
						<span class="displaying-num"><?php printf( esc_html__( 'Displaying %d of %d', 'myarcadeplugin' ), $from_to, $count ); ?></span>
          <?php if ($pagenum > 1) : ?>
							<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=myarcade-manage-games&pagenum=1'><?php esc_html_e( 'First', 'myarcadeplugin' ); ?></a>
							<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=myarcade-manage-games&pagenum=<?php echo esc_attr( $previous ); ?>'><?php esc_html_e( 'Previous', 'myarcadeplugin' ); ?></a>
            <?php endif; ?>
						<span class='page-numbers current'><?php echo esc_html( $pagenum ); ?></span>
            <?php if ($pagenum != $last) : ?>
							<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=myarcade-manage-games&pagenum=<?php echo esc_attr( $next ); ?>'><?php _e( 'Next', 'myarcadeplugin' ); ?></a>
							<a class='page-numbers' href='<?php echo $_SERVER['PHP_SELF'];?>?page=myarcade-manage-games&pagenum=<?php echo esc_attr( $last ); ?>'><?php _e( 'Last', 'myarcadeplugin' ); ?></a>
            <?php endif; ?>
          </div>
        </div>

        <div style="clear:both;"></div>
        <?php
      }
    }
    else {
      _e("No games found", 'myarcadeplugin');
    }

		$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}myarcadegames WHERE status = 'deleted' ORDER BY created DESC limit 10" );

    if ($results) {
      echo '<h3>'.__("10 Last Deleted Games", 'myarcadeplugin').'</h3>';
      foreach ($results as $game) {
        myarcade_show_game($game);
      }
      ?>
      <div style="clear:both;"></div>
      <?php
    }
  }

  ?>
  <script type="text/javascript">
    function thickboxResize() {
      var boundHeight = 800; // minimum height
      var viewportHeight = (self.innerHeight || (document.documentElement.clientHeight || (document.body.clientHeight || 0)));

      jQuery('a.thickbox').each(function(){
        var text = jQuery(this).attr("href");

        if ( viewportHeight < boundHeight )
        {
          // adjust the height
          text = text.replace(/height=[0-9]*/,'height=' + Math.round(viewportHeight * .8));
        }
        else
        {
          // constrain the height by defined bounds
          text = text.replace(/height=[0-9]*/,'height=' + boundHeight);
        }

        jQuery(this).attr("href", text);
      });
    }

    jQuery(window).bind('load', thickboxResize );
    jQuery(window).bind('resize', thickboxResize );

    jQuery("#enable_delete").click(function() {
      var checked_status = this.checked;
      if (checked_status === true) {
        jQuery("#bulk_delete_button").removeAttr("disabled");
      } else {
        jQuery("#bulk_delete_button").attr("disabled", "disabled");
      }
    });

    function confirmBulkDelete() {
      if ( confirm("<?php _e("Are you really sure you want to run a bulk game delete?", 'myarcadeplugin'); ?>") ) {
        return true;
      }
      return false;
    }
  </script>
  <?php
  myarcade_footer();
}
