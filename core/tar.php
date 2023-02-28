<?php
/**
 * Tar Module for MyArcadePlugin Pro
 *
 * This code is based on the tar handlig code from Matthew Mecham
 */

/*
+--------------------------------------------------------------------------
|   Invision Board v1.1
|   ========================================
|   by Matthew Mecham
|   (c) 2001,2002 Invision Power Services
|   http://www.ibforums.com
|   ========================================
|   Web: http://www.ibforums.com
|   Email: phpboards@ibforums.com
|   Licence Info: phpib-licence@ibforums.com
+--------------------------------------------------------------------------
|
| LICENCE OF USE (THIS MODULE ONLY)
|
| This module has been created and released under the GNU licence and may be
| freely used and distributed. If you find some space to credit us in your source
| code, it'll be appreciated.
|
+--------------------------------------------------------------------------
*/

/*************************************************************
|
| EXTRACTION USAGE:
|
| $tar = new tar();
| $tar->new_tar("/foo/bar", "myTar.tar");
| $files = $tar->list_files();
| $tar->extract_files( "/extract/to/here/dir" );
|
| CREATION USAGE:
|
| $tar = new tar();
| $tar->new_tar("/foo/bar" , "myNewTar.tar");
| $tar->current_dir("/foo" );  //Optional - tells the script which dir we are in
|                                to syncronise file creation from the tarball
| $tar->add_files( $file_names_with_path_array );
| (or $tar->add_directory( "/foo/bar/myDir" ); to archive a complete dir)
| $tar->write_tar();
|
*************************************************************/

// Turn off all error reporting
error_reporting(0);

// Check user rights
if ( function_exists('current_user_can') && !current_user_can('edit_posts') ) {
  die();
}

class tar {

  var $tar_header_length = '512';
  var $tar_unpack_header = 'a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8chksum/a1typeflag/a100linkname/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor/a155';
  var $tar_pack_header   = 'A100 A8 A8 A8 A12 A12 A8 A1 A100 A6 A2 A32 A32 A8 A8 A155';
  var $current_dir       = "";
  var $unpack_dir        = "";
  var $pack_dir          = "";
  var $error             = "";
  var $work_dir          = array();
  var $tar_in_mem        = array();
  var $tar_filename      = "";
  var $filehandle        = "";
  var $warnings          = array();
  var $attributes        = array();
  var $tarfile_name      = "";
  var $tarfile_path      = "";
  var $tarfile_path_name = "";
  var $workfiles         = array();

  //-----------------------------------------
  // CONSTRUCTOR: Attempt to guess the current working dir.
  //-----------------------------------------

  function tar_init()
  {
    $this->current_dir = dirname(__FILE__);

    // Set some attributes, these can be overriden later

    $this->attributes = array(  'over_write_existing'   => 0,
                    'over_write_newer'      => 0,
                  'remove_tar_file'       => 0,
                  'remove_original_files' => 0,
                 );
  }

  //-----------------------------------------
  // Set the tarname. If we are extracting a tarball, then it must be the
  // path to the tarball, and it's name (eg: $tar->new_tar("/foo/bar" ,'myTar.tar')
  // or if we are creating a tar, then it must be the path and name of the tar file
  // to create.
  //-----------------------------------------

  function new_tar($tarpath, $tarname) {

    $this->tarfile_name = $tarname;
    $this->tarfile_path = $tarpath;

    // Make sure there isn't a trailing slash on the path

    $this->tarfile_path = preg_replace( "#[/\\\]$#" , "" , $this->tarfile_path );

    $this->tarfile_path_name = $this->tarfile_path .'/'. $this->tarfile_name;
  }


  //-----------------------------------------
  // Easy way to overwrite defaults
  //-----------------------------------------

  function over_write_existing() {
    $this->attributes['over_write_existing'] = 1;
  }
  function over_write_newer() {
    $this->attributes['over_write_newer'] = 1;
  }
  function remove_tar_file() {
    $this->attributes['remove_tar_file'] = 1;
  }
  function remove_original_files() {
    $this->attributes['remove_original_files'] = 1;
  }



  //-----------------------------------------
  // User assigns the root directory for the tar ball creation/extraction
  //-----------------------------------------

  function current_dir($dir = "") {

    $this->current_dir = $dir;

  }

  //-----------------------------------------
  // list files: returns an array with all the filenames in the tar file
  //-----------------------------------------

  function list_files($advanced="") {

    // $advanced == "" - return name only
    // $advanced == 1  - return name, size, mtime, mode

    $data = $this->read_tar();

    $final = array();

    foreach($data as $d)
    {
      if ($advanced == 1)
      {
        $final[] = array ( 'name'  => $d['name'],
                   'size'  => $d['size'],
                   'mtime' => $d['mtime'],
                   'mode'  => substr(decoct( $d['mode'] ), -4),
                 );
      }
      else
      {
        $final[] = $d['name'];
      }
    }

    return $final;
  }


  //-----------------------------------------
  // Extract the tarball
  // $tar->extract_files( str(TO DIRECTORY), [ array( FILENAMES )  ] )
  //    Can be used in the following methods.
  //    $tar->extract( "/foo/bar" , $files );
  //    This will seek out the files in the user array and extract them
  //    $tar->extract( "/foo/bar" );
  //    Will extract the complete tar file into the user specified directory
  //-----------------------------------------

  function extract_files( $to_dir, $files="" ) {

    $this->error = "";

    // Make sure the $to_dir is pointing to a valid dir, or we error
    // and return

    if (! is_dir($to_dir) )
    {
      $this->error = "Extract files error: Destination directory ($to_dir) does not exist";
      return;
    }

    //-----------------------------------------
    // change into the directory chosen by the user.
    //-----------------------------------------

    chdir($to_dir);
    $cur_dir = getcwd();

    //-----------------------------------------
    // Get the file info from the tar
    //-----------------------------------------

    $in_files = $this->read_tar();

    if ($this->error != "") {
      return;
    }

    foreach ($in_files as $file)
    {
      //-----------------------------------------
      // Stop any potential file traversal issues
      //-----------------------------------------

      $file['name'] = str_replace( '..', '', $file['name'] );

      //-----------------------------------------
      // Are we choosing which files to extract?
      //-----------------------------------------

      if ( is_array($files) )
      {
        if (! in_array($file['name'], $files) )
        {
          continue;
        }
      }

      chdir($cur_dir);

      //-----------------------------------------
      // GNU TAR format dictates that all paths *must* be in the *nix
      // format - if this is not the case, blame the tar vendor, not me!
      //-----------------------------------------

      if ( preg_match("#/#", $file['name']) )
      {
        $path_info = explode( "/" , $file['name'] );
        $file_name = array_pop($path_info);
      }
      else
      {
        $path_info = array();
        $file_name = $file['name'];
      }

      //-----------------------------------------
      // If we have a path, then we must build the directory tree
      //-----------------------------------------


      if (count($path_info) > 0)
      {
        foreach($path_info as $dir_component)
        {
          if ($dir_component == "")
          {
            continue;
          }
          if ( (file_exists($dir_component)) && (! is_dir($dir_component)) )
          {
            $this->warnings[] = "WARNING: $dir_component exists, but is not a directory";
            continue;
          }
          if (! is_dir($dir_component))
          {
            mkdir( $dir_component, 0777);
            chmod( $dir_component, 0777);
          }

          if (! @chdir($dir_component))
          {
            $this->warnings[] = "ERROR: CHDIR to $dir_component FAILED!";
          }
        }
      }

      //-----------------------------------------
      // check the typeflags, and work accordingly
      //-----------------------------------------

      if (($file['typeflag'] == 0) or (!$file['typeflag']) or ($file['typeflag'] == ""))
      {
        $FH = fopen($file_name, "wb");

        if ( $FH )
        {
          fputs( $FH, $file['data'], strlen($file['data']) );
          fclose($FH);
        }
        else
        {
          $this->warnings[] = "Could not write data to $file_name";
        }
      }
      else if ($file['typeflag'] == 5)
      {
        if ( (file_exists($file_name)) && (! is_dir($file_name)) )
        {
          $this->warnings[] = "$file_name exists, but is not a directory";
          continue;
        }
        if (! is_dir($file_name))
        {
          @mkdir( $file_name, 0777);
        }
      }
      else if ($file['typeflag'] == 6)
      {
        $this->warnings[] = "Cannot handle named pipes";
        continue;
      }
      else if ($file['typeflag'] == 1)
      {
        $this->warnings[] = "Cannot handle system links";
      }
      else if ($file['typeflag'] == 4)
      {
        $this->warnings[] = "Cannot handle device files";
      }
      else if ($file['typeflag'] == 3)
      {
        $this->warnings[] = "Cannot handle device files";
      }
      else
      {
        $this->warnings[] = "Unknown typeflag found";
      }

      if (! @chmod( $file_name, $file['mode'] ) )
      {
        $this->warnings[] = "ERROR: CHMOD $mode on $file_name FAILED!";
      }

      @touch( $file_name, $file['mtime'] );

    }

    // Return to the "real" directory the scripts are in

    @chdir($this->current_dir);

  }


  //-----------------------------------------
  // Read the tarball - builds an associative array
  //-----------------------------------------

  function read_tar() {

    $filename = $this->tarfile_path_name;

    if ($filename == "") {
      $this->error = 'No filename specified when attempting to read a tar file';
      return array();
    }

    if (! file_exists($filename) ) {
      $this->error = 'Cannot locate the file '.$filename;
      return array();
    }

    $tar_info = array();

    $this->tar_filename = $filename;

    // Open up the tar file and start the loop

    if (! $FH = fopen( $filename , 'rb' ) ) {
      $this->error = "Cannot open $filename for reading";
      return array();
    }

    // Grrr, perl allows spaces, PHP doesn't. Pack strings are hard to read without
    // them, so to save my sanity, I'll create them with spaces and remove them here

    $this->tar_unpack_header = preg_replace( "/\s/", "" , $this->tar_unpack_header);

    while (!feof($FH)) {

      $buffer = fread( $FH , $this->tar_header_length );

      // check the block

      $checksum = 0;

      for ($i = 0 ; $i < 148 ; $i++) {
        $checksum += ord( substr($buffer, $i, 1) );
      }
      for ($i = 148 ; $i < 156 ; $i++) {
        $checksum += ord(' ');
      }
      for ($i = 156 ; $i < 512 ; $i++) {
        $checksum += ord( substr($buffer, $i, 1) );
      }

      $fa = unpack( $this->tar_unpack_header, $buffer);

      $name     = trim($fa['filename']);
      $mode     = OctDec(trim($fa['mode']));
      $uid      = OctDec(trim($fa['uid']));
      $gid      = OctDec(trim($fa['gid']));
      $size     = OctDec(trim($fa['size']));
      $mtime    = OctDec(trim($fa['mtime']));
      $chksum   = OctDec(trim($fa['chksum']));
      $typeflag = trim($fa['typeflag']);
      $linkname = trim($fa['linkname']);
      $magic    = trim($fa['magic']);
      $version  = trim($fa['version']);
      $uname    = trim($fa['uname']);
      $gname    = trim($fa['gname']);
      $devmajor = OctDec(trim($fa['devmajor']));
      $devminor = OctDec(trim($fa['devminor']));
      if (isset($fa['prefix'])) $prefix   = trim($fa['prefix']); else $prefix = false;

      if ( ($checksum == 256) && ($chksum == 0) ) {
        //EOF!
        break;
      }

      if ($prefix) {
        $name = $prefix.'/'.$name;
      }

      // Some broken tars don't set the type flag
      // correctly for directories, so we assume that
      // if it ends in / it's a directory...

      if ( (preg_match( "#/$#" , $name)) and (! $name) ) {
        $typeflag = 5;
      }

      // If it's the end of the tarball...
      $test = $this->internal_build_string( '\0' , 512 );
      if ($buffer == $test) {
        break;
      }

      // Read the next chunk

      #Test fix to ignore files with that have 0 bytes
      $nobtyes = 0;
      if( $size ) {

      $data = fread( $FH, $size );

      } else  {

      $nobtyes = 1;

      }



      if (isset($data) && (strlen($data) != $size) && !$nobtyes) {
        $this->error = "Read error on tar file";
        fclose( $FH );
        return array();
      }

      $diff = $size % 512;

      if ($diff != 0) {
        // Padding, throw away
        $crap = fread( $FH, (512-$diff) );
      }

      // Protect against tarfiles with garbage at the end

      if ($name == "") {
        break;
      }

      $tar_info[] = array (
                  'name'     => $name,
                  'mode'     => $mode,
                  'uid'      => $uid,
                  'gid'      => $gid,
                  'size'     => $size,
                  'mtime'    => $mtime,
                  'chksum'   => $chksum,
                  'typeflag' => $typeflag,
                  'linkname' => $linkname,
                  'magic'    => $magic,
                  'version'  => $version,
                  'uname'    => $uname,
                  'gname'    => $gname,
                  'devmajor' => $devmajor,
                  'devminor' => $devminor,
                  'prefix'   => $prefix,
                  'data'     => $data
                 );
    }

    fclose($FH);

    return $tar_info;
  }


//-----------------------------------------
// INTERNAL FUNCTIONS - These should NOT be called outside this module
//+------------------------------------------------------------------------------

  //-----------------------------------------
  // build_string: Builds a repititive string
  //-----------------------------------------

  function internal_build_string($string="", $times=0) {

    $return = "";
    for ($i=0 ; $i < $times ; ++$i ) {
      $return .= $string;
    }

    return $return;
  }

} // class tar
?>