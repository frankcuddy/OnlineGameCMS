jQuery(document).ready( function($) {

  /**
   * Distributor selection function for fetch games page.
   * If a distributor is selected, the corresponding options panel will be displayed
   */
  function myarcade_distributor_select() {
    var selected = $("#distr").find(":selected").val();

    $("#"+selected).slideDown("fast");

    $("#distr option").each( function() {
      var val = $(this).val();
      if ( val !== selected ) {
        $("#"+val).slideUp("fast");
      }
    });
  }

  // Trigger when a new distributor has been selected
  $("#distr").change( function() {
    myarcade_distributor_select();
  });

  // Trigger once site has been fully loaded
  myarcade_distributor_select();

  $(".myarcade_form :radio").click( function() {
    var name = $(this).attr("name");
    var fetching_method_class = name.match(/^fetchmethod([\w-]*)\b/);

    // Get clicked class
    if ( fetching_method_class !== null ) {
      var distributor_id = fetching_method_class[1];

      if ( 'offset' === $(this).val() ) {
        $("#offs"+distributor_id).fadeIn("fast");
      }
      else {
        $("#offs"+distributor_id).fadeOut("fast").prop('required',false);
      }
    }
  });

  // Initial offset check
  $(".myarcade_form :radio").each( function() {
    var name = $(this).attr("name");
    var fetching_method_class = name.match(/^fetchmethod([\w-]*)\b/);

    if ( fetching_method_class !== null ) {
      var distributor_id = fetching_method_class[1];

      if ( "offset" === $("input:radio[name=fetchmethod"+distributor_id+"]:checked").val() ) {
        $("#offs"+distributor_id).removeClass('hide');
      }
    }
  });
});