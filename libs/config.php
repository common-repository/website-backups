<?php
namespace ero\websitebackups ;


class config {

  public function __construct() {
    $params = new \ero\websitebackups\params;

    $this->__scanClasses( $params::$ero_wd_libs . '/*' );
  }

  private function __scanClasses( $dir ){
    //echo $dir;
    //die();

    foreach (glob($dir) as $path) {

      (preg_match('/(class\-(.*)\.php)$/', $path)) ? (
          require_once $path
        ) : (
          (preg_match('/\.autoload\.php$/', $path)) ? (
            $this->__autoload($path)
            ) : (
              (is_dir($path)) ? (
                $this->__scanClasses($path . '/*')
                ) : ('')
              )
        );
  	}
  }

  private function __autoload( $path ){
    require_once $path;
    $className = basename( $path, '.autoload.php' ) ;
    global $$className;
    $$className = new $className();
  }
  public function __activation(){
    $this->__create_backups_folder();
  }

  public function __create_backups_folder(){
    $params = new \ero\websitebackups\params;
    // creating backup diractory if not exists
    if( ! file_exists( $params::$ero_wd_upload_path ) ) {
      wp_mkdir_p( $params::$ero_wd_upload_path );
      fopen($params::$ero_wd_upload_path . "/index.php", "w");
    }
  }
}
?>
