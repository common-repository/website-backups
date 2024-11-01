<?php
/**
 * Register a meta box using a class.
 */
class EROWD_DBexport {
  static $params = null;
  /**
   * Constructor.
   */
  public function __construct() {
    if ( is_admin() ) {
      $this->init_actions();
    }
    if(self::$params === null ){
        self::$params = new \ero\websitebackups\params;
    }

  }

  /**
   * Initialize database backup ajax callback.
   */
  public function init_actions() {
    add_action( 'wp_ajax_ero_create_db_backup', [$this, 'process_db_backup'] );
  }

  /**
  * Function called when clicked on create db backup
  */
  public function process_db_backup(){
   $res = $this->create_db_backup();
   echo json_encode($res);     // gives json response to jquery ajax call
   wp_die();
  }

  public function create_db_backup(){
   $EDB = new EROWD_Database_Backup();
   $response = $EDB->backup_tables();

   if ( ! $response ) {
     return false;
   }
   $backupName = $response['name'];
   $params = new \ero\websitebackups\params;
   $fileSize = filesize( $params::$ero_wd_upload_path.$backupName );
   $prevOptions = get_option('ero_website_download_db');
   if($prevOptions){
     $fileInfo = array(
         'name' => $backupName,
         'created On' => date('Y-m-d h:i:sa'),
         'size' => $fileSize
       );

     $prevArray = unserialize($prevOptions);
     array_push($prevArray, $fileInfo);
   } else {
     $fileInfo = array (
       array(
         'name' => $backupName,
         'created On' => date('Y-m-d h:i:sa'),
         'size' => $fileSize
       )
     );
   }
   $fileInfo = (isset($prevArray)) ? $prevArray :  $fileInfo;
   if(update_option( 'ero_website_download_db', serialize($fileInfo)  )){
     return [
       'status' => true,
       'message' => 'Backup Created Successfully '
     ];
   }else{
     return [
       'status' => false,
       'message' => 'Backup Failed !!!!'
     ];
   }
  }
}
