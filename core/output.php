<?php
/**
 * Game output functions
 *
 * @package MyArcadePlugin/Game/Output
 */

// No direct access.
if( !defined( 'ABSPATH' ) ) {
  die();
}

/**
 * [myarcade_get_embed_type description]
 *
 * @param  string $game_type Game type.
 * @return string            Game type.
 */
function myarcade_get_embed_type( $game_type ) {
  $embdtype_function = 'myarcade_embedtype_' . $game_type;

	// Get distributor integration file.
	MyArcade()->load_distributor( $game_type );

  if ( function_exists( $embdtype_function ) ) {
    $game_type = $embdtype_function();
  }

  return $game_type;
}

/**
 * Show a game depended on the game type
 *
 * @param  int     $game_id    Post ID.
 * @param  boolean $fullsize   TRUE to display the game with origial dimensions.
 * @param  boolean $preview    TRUE if this is a game preview (used only on backend).
 * @param  boolean $fullscreen TRUE to display the game in fullscreen mode.
 * @return string              Game embed code.
 */
function get_game( $game_id = false, $fullsize = false, $preview = false, $fullscreen = false ) {
  global $wpdb, $mypostid, $post;

  if ( ! $game_id ) {
    if ( ! empty( $post->ID ) ) {
      $game_id = $post->ID;
		} else {
			// Can't find a game ID.
      return;
    }
  }

  $mypostid = $game_id;

	if ( false === $preview ) {
		if ( false === $fullscreen ) {
			$gamewidth  = apply_filters( 'myarcade_game_width', get_post_meta( $game_id, 'mabp_width', true ) );
			$gameheight = apply_filters( 'myarcade_game_height', get_post_meta( $game_id, 'mabp_height', true ) );
		} else {
      $gamewidth  = apply_filters('myarcade_fullscreen_width',  '100%');
      $gameheight = apply_filters('myarcade_fullscreen_height', '100%');
    }

		$game_url     = apply_filters( 'myarcade_swf_url', get_post_meta( $game_id, 'mabp_swf_url', true ) );
		$game_variant = get_post_meta( $game_id, 'mabp_game_type', true );
		$game_uuid    = get_post_meta( $game_id, 'mabp_game_uuid', true );
	} else {
		$game         = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}myarcadegames WHERE id = %d", $game_id ) );
    $game_url = $game->swf_url;
    $game_variant =  $game->game_type;
    $game_uuid = $game->uuid;
		$gamewidth    = $game->width;
		$gameheight   = $game->height;
  }

	// Check if this is spilgame and if player api is activated.
	if ( strpos( $game_uuid, '_spilgames' ) !== false ) {
		// This is a spilgames game.
    $spilgames = get_option( 'myarcade_spilgames' );
    if ( $spilgames['player_api'] ) {
      $game_player_api_id = strtok( $game_uuid, '_spilgames' );
      if ( $game_player_api_id ) {
				// Overwrite game type.
        $game_variant = 'spilgames_player_api';
      }
    }
  }

  $general = get_option('myarcade_general');
  $maxwidth = intval($general['max_width']);

	// Should the game be resized.
	if ( ! $fullsize && $maxwidth && $gamewidth && $gameheight && false !== strpos( $gamewidth, '%' ) ) {
    if ($gamewidth > $maxwidth) {
			// Adjust the game dimensions.
      $ratio      = $maxwidth / $gamewidth;
      $gamewidth  = $maxwidth;
      $gameheight = $gameheight * $ratio;
    }
  }

	// Modify the URL depending on the game type.
  switch ( $game_variant ) {
		case 'gamepix':
      $gamepix = get_option( 'myarcade_gamepix' );
      $gamepix['site_id'] = '20015';

      $game_url = add_query_arg( array( 'sid' => $gamepix['site_id'] ), $game_url );
			break;

		case 'softgames':
			$softgames = get_option( 'myarcade_softgames' );

			if ( empty( $softgames['publisher_id'] ) ) {
				$softgames['publisher_id'] = 'pub-10477-18399';
  }

			$game_url = add_query_arg( array( 'p' => $softgames['publisher_id'] ), $game_url );
			break;
	}

	// Do some actions when this is not a game preview.
	if ( true === $preview ) {
    $show_game = true;
	} else {
    $show_game = myarcade_play_check();
  }

	// Init game code.
  $code = '';

	if ( true === $show_game ) {
		// Embed game code.
      $embed_type = myarcade_get_embed_type( $game_variant );

      switch ( $embed_type ) {

				case 'embed':
					// Embed or Iframe code.
          $code = stripcslashes($game_url);
					break;

				case 'dcr':
					// DCR File.
          $code = '<object classid="clsid:166B1BCA-3F9C-11CF-8075-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/director/sw.cab" width="'.$gamewidth.'" height="'.$gameheight.'">
                    <param name="src" value="'.$game_url.'">
                    <param name="wmode" value="transparent">
                    <param name="wmode" value="opaque" />
										<embed src="' . $game_url . '" width="' . $gamewidth . '"  height="' . $gameheight . ' pluginspage="http://www.macromedia.com/shockwave/download/" type="application/x-director">
                  </object>';
					break;

        case 'iframe':
        case 'html5':
					$gamewidth = ( ! empty( $gamewidth ) ) ? 'width="' . $gamewidth . '"' : '100%';
					$gameheight = ( ! empty( $gameheight ) ) ? 'height="' . $gameheight . '"' : '100%';
          $parameters = apply_filters( 'myarcade_iframe_parameters', 'frameborder="0" scrolling="no" allowfullscreen="true"', $game_id );
          $code = '<iframe id="playframe" '.$gamewidth.' '.$gameheight.' '.$parameters.' src="'.$game_url.'" ></iframe>';
					break;

        case 'unity':
          $code  = '<script type="text/javascript" src="http://webplayer.unity3d.com/download_webplayer-3.x/3.0/uo/UnityObject.js"></script>' . "\n";
          $code .= '<script type="text/javascript">' . "\n";
          $code .= 'unityObject.embedUnity("unityPlayer", "'.$game_url.'", "'.$gamewidth.'", "'.$gameheight.'");' . "\n";
          $code .= '</script>' . "\n";
          $code .= '<div id="unityPlayer"><div class="missing"><a href="http://unity3d.com/webplayer/" title="Unity Web Player. Install now!"><img alt="Unity Web Player. Install now!" src="http://webplayer.unity3d.com/installation/getunity.png" width="193" height="63" /></a></div></div>';
					break;

				case 'spilgames_player_api':
          $code = '<div class="gameplayer" data-sub="cdn" data-width="'.$gamewidth.'" data-height="'.$gameheight.'" data-gid="'.$game_player_api_id.'" data-source="MyArcadePlugin"></div>';
          $code .= '<script>(function(d, s, id) { var js, fjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src="http://cdn.gameplayer.io/api/js/publisher.js";fjs.parentNode.insertBefore(js, fjs);}(document, "script", "gameplayer-publisher"));</script>';
					break;

        case 'flash':
				default:
          $embed_parameters = apply_filters( 'myarcade_embed_parameters', 'wmode="direct" menu="false" quality="high"', $game_id );
          $code = '<embed src="'.$game_url.'" '.$embed_parameters.' width="'.$gamewidth.'" height="'.$gameheight.'" allowscriptaccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />';

					$code = apply_filters( 'myarcade_flash_output', $code, $game_url, $gamewidth, $gameheight, $embed_parameters );
					break;
			}
  }

  return $code;
}

/**
 * Get game embed code which can be displayed on text areas.
 *
 * @param  boolean $post_id Post ID.
 * @return string           Embed code.
 */
function get_game_code( $post_id = false ) {
  global $post;

  if ( !$post_id && isset($post->ID) ) {
    $post_id = $post->ID;
	} else {
		return false;
  }

	$game_variant = get_post_meta( $post_id, 'mabp_game_type', true );
	$gamewidth    = intval( get_post_meta( $post_id, 'mabp_width', true ) );
	$gameheight   = intval( get_post_meta( $post_id, 'mabp_height', true ) );
	$game_url     = get_post_meta( $post_id, 'mabp_swf_url', true );
  $embed_type = myarcade_get_embed_type( $game_variant );

  switch ( $embed_type ) {
		case 'embed':
			// Embed or Iframe code.
      $code = stripcslashes($game_url);
			break;

		case 'dcr':
			// DCR File.
      $code = '<object classid="clsid:166B1BCA-3F9C-11CF-8075-444553540000" codebase="http://active.macromedia.com/director/cabs/sw.cab" width="'.$gamewidth.'" height="'.$gameheight.'">
          <param name="src" value="'.$game_url.'">
          <param name="wmode" value="transparent">
          <param name="wmode" value="opaque" />
          <embed src="'.$game_url.'" width="'.$gamewidth.'"  height="'.$gameheight.'">
        </object>';
			break;

    case 'iframe':
      $gamewidth = ( !empty($gamewidth) ) ? 'width="'.$gamewidth.'"' : '';
      $gameheight = ( !empty($gameheight) ) ? 'height="'.$gameheight.'"' : '';
      $code = '<iframe id="playframe" '.$gamewidth.' '.$gameheight.' src="'.$game_url.'" frameborder="0" scrolling="no"></iframe>';
			break;

    case 'unity':
      $code  = '<script type="text/javascript" src="http://webplayer.unity3d.com/download_webplayer-3.x/3.0/uo/UnityObject.js"></script>' . "\n";
      $code .= '<script type="text/javascript">' . "\n";
      $code .= 'unityObject.embedUnity("unityPlayer", "'.$game_url.'", "'.$gamewidth.'", "'.$gameheight.'");' . "\n";
      $code .= '</script>' . "\n";
      $code .= '<div id="unityPlayer"><div class="missing"><a href="http://unity3d.com/webplayer/" title="Unity Web Player. Install now!"><img alt="Unity Web Player. Install now!" src="http://webplayer.unity3d.com/installation/getunity.png" width="193" height="63" /></a></div></div>';
			break;

    case 'flash':
    default:
      $embed_parameters = apply_filters( 'myarcade_embed_parameters', 'wmode="direct" menu="false" quality="high"', $post_id );
        $code = '<embed src="'.$game_url.'" '.$embed_parameters.' width="'.$gamewidth.'" height="'.$gameheight.'" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />';
			break;
  }

  return $code;
}

/**
 * Generates Leaderboard bridge codes
 */
function myarcade_get_leaderboard_code() {
  return;
}
