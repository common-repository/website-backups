<?php

namespace ero\websitebackups ;

class params {

  static $ero_wd_base_path = null;
  static $ero_wd_libs = null;
  static $ero_wd_base_url = null;
  static $ero_wd_upload_path = null;
  static $ero_wd_upload_url = null;


  public function __construct() {
    if ( self::$ero_wd_base_path === null ) {
      self::$ero_wd_base_path = plugin_dir_path(__FILE__).'../';
      self::$ero_wd_libs = self::$ero_wd_base_path . 'libs/classes';
      self::$ero_wd_base_url = plugin_dir_url(__FILE__).'../';
      $uploads_folder = wp_upload_dir();
      self::$ero_wd_upload_path = $uploads_folder['basedir'] . '/ero-website-backups/';
      self::$ero_wd_upload_url = $uploads_folder['baseurl'] . '/ero-website-backups/';
    }
  }
}
?>
