/**
 * Handle the loading of widget content
 *
 * @version 5.30.0
 */
function myarcade_stats_get_widget_content( widget, container_id ) {
  var data = {
    'action': 'myarcade_stats_get_widget_content',
    'widget': widget
  }

  container = jQuery("#" + container_id);

  if ( container.is(':visible') ) {
    jQuery.ajax({
      url: ajaxurl,
      type: 'post',
      data: data,
      datatype: 'json',
    })
    .always( function(result) {
      // Take the returned result and add it to the DOM.
      jQuery("#" + container_id).html("").html(result);
    })
    .fail( function(result) {
      // If we fail for some reason, like a timeout, try again.
      container.html(wp_statistics_loading_image);
      myarcade_stats_get_widget_content(widget, container_id);
    });
  }
}