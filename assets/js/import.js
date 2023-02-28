jQuery(function($) {
  function showLoader(id) { $(id).show(); }
  function hideLoader(id) { $(id).hide(); }

  function showfileSelection( selected ) {
    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: "myarcade_get_filelist",
        type: selected
      },
      dataType: "html",
      success: function( response ) {
        $( "#" + selected + " .loadimg" ).hide();
        $( "#" + selected ).prepend(response);
        $( "#" + selected + " .cancelselection" ).show();
      },
      error: function( response ) {}
    });
  }

  function showResponseSWF(data, statusText, xhr, $form)  {
    hideLoader('#loadimgswf');

    // Check the status
    if (statusText == 'success' && data.error == '') {
      $('#filename').html('<strong>' + data.name + '</strong> - <i>' + data.info_dim + '</i>');
      $('#gamewidth').val(data.width);
      $('#gameheight').val(data.height);
      $('#gamename').val(data.realname);
      $('#importgame').val(data.location_url);
      $('#importtype').val(data.type);
    }
    else {
      if ( statusText != 'success' ) {
        alert( MyArcadeImport.error_string + ' ' + statusText );
      }
      else {
        alert( MyArcadeImport.error_string + ' ' + data.error );
      }
    }
  }

  function showResponseTHUMB(data, statusText, xhr, $form)  {
    hideLoader('#loadimgthumb');

    // Check the status
    if (statusText == 'success' && data.error == '') {
      $('#filenamethumb').html('<img src="' + data.thumb_url + '" alt=""  />');
      $('#importthumb').val(data.thumb_url);
    }
    else {
      if ( statusText != 'success' ) {
        alert( MyArcadeImport.error_string + ' ' + statusText );
      }
      else {
        alert( MyArcadeImport.error_string + ' ' + data.error );
      }
    }
  }

  function showResponseSCREEN(data, statusText, xhr, $form)  {
    var output_string = '';

    hideLoader('#loadimgscreen');

    // Check the status
    if (statusText == 'success' && data.error == '') {
      for (var i=0;  i<=3; i++) {
        if (data.screen_name[i] != '') {
          var x = i + 1;
          output_string += '<strong>Screen ' + x + ': ' + data.screen_name[i] + '</strong><br />';
          $('#importscreen' + x).val(data.screen_url[i]);
        }
        else {
          output_string += data.screen_error[i] + '<br />';
        }
      }
      $('#filenamescreen').html(output_string);
    }
    else {
      if ( statusText != 'success' ) {
        alert( MyArcadeImport.error_string + ' ' + statusText );
      }
      else {
        alert( MyArcadeImport.error_string + ' ' + data.error );
      }
    }
  }

  function showResponseHTML5(data, statusText, xhr, $form)  {
    hideLoader('#loadimghtml5');

    // Check the status
    if (statusText == 'success' && data.error == '') {
      $('#filenamehtml5').html('<strong>' + data.name + '</strong> - <i>' + data.info_dim + '</i>');
      $('#gamewidth').val(data.width);
      $('#gameheight').val(data.height);
      $('#gamename').val(data.realname);
      $('#importgame').val(data.location_url);
      $('#importtype').val(data.type);
    }
    else {
      if ( statusText != 'success' ) {
        alert( MyArcadeImport.error_string + ' ' + statusText );
      }
      else {
        alert( MyArcadeImport.error_string + ' ' + data.error );
      }
    }
  }

  function showResponseTAR(data, statusText, xhr, $form)  {
    hideLoader('#loadimgtar');

    if ( ! data ) {
      alert( MyArcadeImport.cannot_import );
      return false;
    }

    // Check the status
    if (statusText == 'success' && data.error == '') {
      var thumb = '<img src="'+data.thumbnail_url+'" alt="" />';
      $('#filenametar').html('<strong>' + data.name + '</strong> - <i>' + data.info_dim + '</i><br />' + thumb);
      $('#gamewidth').val(data.width);
      $('#gameheight').val(data.height);
      $('#gamename').val(data.realname);
      $('#importgame').val(data.location_url);
      $('#importthumb').val(data.thumbnail_url);
      $('#importtype').val(data.type);

      if ( 'true' === MyArcadeImport.rich_editing ) {
        tinymce.get('gamedescr').setContent( data.description );
        tinymce.get('gameinstr').setContent( data.instructions );
      }
      else {
        $("#gamedescr").val( data.description );
        $("#gameinstr").val( data.instructions );
      }

      if ( data.leaderboard_enabled ===  "1" ) {
        $('#lbenabled').prop('checked', true);
      }

      if (typeof data.categs !== 'undefined' ) {
        $('.gamecat' + data.categs).prop('checked', true);
      }

      $("select#highscoretype").val(data.highscore_type);
      $('#slug').val(data.slug);
    }
    else {
      if ( statusText != 'success' ) {
        alert( MyArcadeImport.error_string + ' ' + statusText );
      }
      else {
        alert( MyArcadeImport.error_string + ' ' + data.error );
      }
    }
  }

  function showResponseZIP(data, statusText, xhr, $form)  {
    hideLoader('#loadimgzip');

    // Check the status
    if (statusText === 'success' && data.error === '') {
      var thumb = '<img src="'+data.thumbnail_url+'" alt="" />';
      $('#filenamezip').html('<strong>' + data.name + '</strong> - <i>' + data.info_dim + '</i><br />' + thumb);
      $('#gamewidth').val(data.width);
      $('#gameheight').val(data.height);
      $('#gamename').val(data.realname);
      $('#importgame').val(data.location_url);
      $('#importthumb').val(data.thumbnail_url);
      $('#importtype').val(data.type);
      $('#slug').val(data.slug);

      if ( typeof data.description !== 'undefined' ) {
        if ( 'true' === MyArcadeImport.rich_editing ) {
          tinymce.get('gamedescr').setContent( data.description );
        }
        else {
          $('#gamedescr').val(data.description);
        }
      }

      if ( typeof data.instructions !== 'undefined' ) {
        if ( 'true' === MyArcadeImport.rich_editing ) {
          tinymce.get('gameinstr').setContent( data.instructions );
        }
        else {
          $('#gameinstr').val(data.instructions);
        }
      }
      if ( typeof data.leaderboard_enabled !== 'undefined' && data.leaderboard_enabled ===  "1" ) {
        $('#lbenabled').prop('checked', true);
      }
      if ( typeof data.highscore_type !== 'undefined' ) {
        $("select#highscoretype").val(data.highscore_type);
      }
      if ( data.type === 'mochi' ) {
        $('#gametags').val( data.tags );
        $('.gamecat' + data.categs).prop('checked', true);
        $('#importgametag').val( data.game_tag);
        if ( 'screen1_url' in data) { $('#importscreen1').val( data.screen1_url ); }
        if ( 'screen2_url' in data) { $('#importscreen2').val( data.screen2_url ); }
        if ( 'screen3_url' in data) { $('#importscreen3').val( data.screen3_url ); }
        if ( 'screen4_url' in data) { $('#importscreen4').val( data.screen4_url ); }
      }
    }
    else {
      if ( statusText !== 'success' ) {
        alert( MyArcadeImport.error_string + ' ' + statusText );
      }
      else {
        alert( MyArcadeImport.error_string + ' ' + data.error );
      }
    }
  }

  function showResponseUnity(data, statusText, xhr, $form)  {
    hideLoader('#loadimgunity');

    // Check the status
    if (statusText == 'success' && data.error == '') {
      $('#filenameunity').html('<strong>' + data.name + '</strong>');
      $('#gamename').val(data.realname);
      $('#importgame').val(data.location_url);
      $('#importtype').val(data.type);
    }
    else {
      if ( statusText != 'success' ) {
        alert( MyArcadeImport.error_string + ' ' + statusText );
      }
      else {
        alert( MyArcadeImport.error_string + ' ' + data.error );
      }
    }
  }

  function showResponseEMIF(data, statusText, xhr, $form)  {
    hideLoader('#loadimgemif');

    // Check the status
    if (statusText == 'success' && data.error == '') {
      $('#importtype').val(data.type);
      $('#importgame').val(data.importgame);
      $('#filenameemif').html('<strong>' + data.result + '</strong>');
    }
    else {
      if ( statusText != 'success' ) {
        alert( MyArcadeImport.error_string + ' ' + statusText );
      }
      else {
        alert( MyArcadeImport.error_string + ' ' + data.error );
      }
    }
  }

  function myarcade_chkImportCustom() {
    var editorContentDescription;

    if (document.FormCustomGame.importgame.value == "") {
      alert( MyArcadeImport.game_missing );
      return false;
    }

    if (document.FormCustomGame.importthumb.value == "") {
      alert( MyArcadeImport.thumb_missing );
      return false;
    }

    if (document.FormCustomGame.gamename.value == "") {
      alert( MyArcadeImport.name_missing );
      document.FormCustomGame.gamename.focus();
      return false;
    }

    if ( 'true' === MyArcadeImport.rich_editing ) {
      editorContentDescription = tinyMCE.get('gamedescr').getContent();
    }
    else {
      editorContentDescription = $("#gamedescr").val();
    }

    if ( editorContentDescription == '') {
      alert( MyArcadeImport.description_missing );
      document.FormCustomGame.gamedescr.focus();
      return false;
    }

    var categs = false;
    for( var i = 0; i < document.FormCustomGame.elements.length - 1; i++) {
      if( (document.FormCustomGame.elements[i].type == "checkbox") && (document.FormCustomGame.elements[i].checked == true)) {
        categs = true;
        break;
      }
    }

    if ( categs == false ) {
      alert( MyArcadeImport.category_missing );
      return false;
    }

    return true;
  }

  function myarcade_check_file_size( fileID, targetID ) {

    var iSizeBit  = $(fileID)[0].files[0].size;
    var iSize     = ( iSizeBit / 1024 );
    var unit      = "kB";

    if (iSize / 1024 > 1) {
      if (((iSize / 1024) / 1024) > 1) {
        iSize = (Math.round(((iSize / 1024) / 1024) * 100) / 100);
        unit = "GB";
      }
      else {
        iSize = (Math.round((iSize / 1024) * 100) / 100)
        unit = "MB";
      }
    }
    else {
      iSize = (Math.round(iSize * 100) / 100)
    }

    $(targetID).html("File Size: " + iSize  + unit);

    if ( iSizeBit > MyArcadeImport.allowed_size ) {
      alert( MyArcadeImport.max_filesize_exceeded );
    }
  }

  // SWF handler
  $('#uploadFormSWF').submit(function(e) {
    e.preventDefault();

    var options = {
      type: 'POST',
      url: ajaxurl,
      data: { action: "myarcade_import_handler" },
      dataType: 'json',
      beforeSubmit: function() { showLoader('#loadimgswf'); },
      success: showResponseSWF
    };

    $(this).ajaxSubmit(options);

    return false;
  });

  // Thumbnail handler
  $('#uploadFormTHUMB').submit(function(e) {
    e.preventDefault();

    var options = {
    type: 'POST',
    url: ajaxurl,
    data: { action: "myarcade_import_handler" },
    dataType: 'json',
    beforeSubmit: function() { showLoader('#loadimgthumb'); },
    success: showResponseTHUMB
    };
    $(this).ajaxSubmit(options);
    return false;
  });

  // Screenshot handler
  $('#uploadFormSCREEN').submit(function(e) {
    e.preventDefault();

    var options = {
    type: 'POST',
    url: ajaxurl,
    data: { action: "myarcade_import_handler" },
    dataType: 'json',
    beforeSubmit: function() { showLoader('#loadimgscreen'); },
    success: showResponseSCREEN
    };
    $(this).ajaxSubmit(options);
    return false;
  });

  // HTML5 handler
  $('#uploadFormhtml5').submit(function(e) {
    e.preventDefault();

    var options = {
    type: 'POST',
    url: ajaxurl,
    data: { action: "myarcade_import_handler" },
    dataType: 'json',
    beforeSubmit: function() { showLoader('#loadimghtml5'); },
    success: showResponseHTML5
    };
    $(this).ajaxSubmit(options);
    return false;
  });

  // TAR handler
  $('#uploadFormTAR').submit(function(e) {
    e.preventDefault();

    var options = {
    type: 'POST',
    url: ajaxurl,
    data: { action: "myarcade_import_handler" },
    dataType: 'json',
    beforeSubmit: function() { showLoader('#loadimgtar'); },
    success: showResponseTAR
    };
    $(this).ajaxSubmit(options);
    return false;
  });

  // ZIP handler
  $('#uploadFormZIP').submit(function(e) {
    e.preventDefault();

    var options = {
    type: 'POST',
    url: ajaxurl,
    data: { action: "myarcade_import_handler" },
    dataType: 'json',
    beforeSubmit: function() { showLoader('#loadimgzip'); },
    success: showResponseZIP
    };
    $(this).ajaxSubmit(options);
    return false;
  });

  // Unity handler
  $('#uploadFormUnity').submit(function(e) {
    e.preventDefault();
    var options = {
    type: 'POST',
    url: ajaxurl,
    data: { action: "myarcade_import_handler" },
    dataType: 'json',
    beforeSubmit: function() { showLoader('#loadimgunity'); },
    success: showResponseUnity
    };
    $(this).ajaxSubmit(options);
    return false;
  });

  // Embed handler
  $('#uploadFormEMIF').submit(function(e) {
    e.preventDefault();
    var options = {
    type: 'POST',
    url: ajaxurl,
    data: { action: "myarcade_import_handler" },
    dataType: 'json',
    beforeSubmit: function() { showLoader('#loadimgemif'); },
    success: showResponseEMIF
    };
    $(this).ajaxSubmit(options);
    return false;
  });

  // File Size check
  $("#gamefile").change(function ()  {
    myarcade_check_file_size("#gamefile", "#lblgamefile");
  });
  $("#tarfile").change(function ()  {
    myarcade_check_file_size("#tarfile", "#lbltarfile");
  });
  $("#zipfile").change(function ()  {
    myarcade_check_file_size("#zipfile", "#lblzipfile");
  });
  $("#html5file").change(function ()  {
    myarcade_check_file_size("#html5file", "#lblhtml5zipfile");
  });
  $("#unityfile").change(function ()  {
    myarcade_check_file_size("#unityfile", "#lblunityfile");
  });

  // Folder selection
  $(".fileselection").click( function() {
    var selected = $(this).closest('div').attr('id');
    $("#folder" + selected).hide();
    $("#" + selected + " .loadimg").show();
    showfileSelection( selected );
  });

  $(".cancelselection").click( function() {
    var selected = $(this).closest('div').attr('id');
    $("#fileselect" + selected).remove();
    $("#" + selected + " .cancelselection").hide();
    $("#" + selected + " .fileselection").show();
  });
});

/* Import method selection */
jQuery(document).ready(function($) {
  $('#importswfdcr').hide();
  $('#importibparcade').hide();
  $('#importphpbb').hide();
  $('#importunity').hide();
  $('#importembedif').hide();

  $('#importmethod').change( function() {
    $('#filename').html('');
    $('#filenametar').html('');
    $('#gamewidth').val('');
    $('#gameheight').val('');
    $('#gamename').val('');
    $('#importgame').val('');
    $('#importtype').val('');
    $('#importscreen1').val('');
    $('#importscreen2').val('');
    $('#importscreen3').val('');
    $('#importscreen4').val('');
    $('#slug').val('');

    switch (this.value) {
      case 'importhtml5': {
        $('#importswfdcr').hide();
        $('#importembedif').hide();
        $('#thumbform').fadeIn();
        $('#importphpbb').hide();
        $('#importunity').hide();
        $('#importibparcade').hide();
        $('#importhtml5').fadeIn();
      }
      break;

      case 'importibparcade': {
        $('#importswfdcr').hide();
        $('#importembedif').hide();
        $('#thumbform').hide();
        $('#importphpbb').hide();
        $('#importunity').hide();
        $('#importhtml5').hide();
        $('#importibparcade').fadeIn();
      }
      break;

      case 'importphpbb': {
        $('#importswfdcr').hide();
        $('#importembedif').hide();
        $('#thumbform').hide();
        $('#importphpbb').fadeIn();
        $('#importunity').hide();
        $('#importhtml5').hide();
        $('#importibparcade').hide();
      }
      break;

      case 'importswfdcr': {
        $('#importibparcade').hide();
        $('#importembedif').hide();
        $('#importphpbb').hide();
        $('#importunity').hide();
        $('#importhtml5').hide();
        $('#importswfdcr').fadeIn();
        $('#thumbform').fadeIn();
      }
      break;

      case 'importembedif': {
        $('#importibparcade').hide();
        $('#importswfdcr').hide();
        $('#importphpbb').hide();
        $('#importunity').hide();
        $('#importhtml5').hide();
        $('#importembedif').fadeIn();
        $('#thumbform').fadeIn();
      }
      break;

      case 'importunity': {
        $('#importibparcade').hide();
        $('#importswfdcr').hide();
        $('#importphpbb').hide();
        $('#importunity').fadeIn();
        $('#importhtml5').hide();
        $('#importembedif').hide();
        $('#thumbform').fadeIn();
      }
      break;
    }
  });

  $('#importmethod').change();
});