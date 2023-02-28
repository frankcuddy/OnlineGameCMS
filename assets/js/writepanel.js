jQuery( function($) {
  $('ul.tabs').show();
  $('div.panel-wrap').each(function() {
    $('div.panel:not(div.panel:first)', this).hide();
  });
  $('ul.tabs a').click(function(){
    var panel_wrap =  $(this).closest('div.panel-wrap');
    $('ul.tabs li', panel_wrap).removeClass('active');
    $(this).parent().addClass('active');
    $('div.panel', panel_wrap).hide();
    $( $(this).attr('href') ).show();
    return false;
  });
});