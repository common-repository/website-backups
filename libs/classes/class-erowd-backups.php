<?php
/**
 * Register a meta box using a class.
 */
class EROWD_Backups {

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
     * Meta box initialization.
     */
    public function init_actions() {
      add_action( 'admin_menu', array( $this, 'add_menupage'  )        );
      add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
      add_action( 'wp_ajax_ero_create_backup', [$this, 'process_backup'] );  // callback for backup of files
      add_action( 'wp_ajax_ero_delete_backup', [$this, 'deleteBackup'] );    // callback for deleting a backup for both database and files ( backups )
    }

    /**
     * Adds admin scripts
     */
    public function admin_enqueue_scripts(){
      $params = new \ero\websitebackups\params;
      wp_register_style( 'website-downloader-admin-css', $params::$ero_wd_base_url.'assets/css/admin-style.min.css' );
      wp_register_style( 'website-downloader-datatables', $params::$ero_wd_base_url.'assets/plugins/datatables/datatables.min.css' );
      wp_register_script( 'website-downloader-datatables-script', $params::$ero_wd_base_url.'assets/plugins/datatables/datatables.min.js' , array('jquery') );
      wp_register_script( 'website-downloader-admin-script', $params::$ero_wd_base_url.'assets/js/admin-scripts.js' , array('jquery') );
    }
    /**
     * Adds admin menu page
     */
    public function add_menupage() {
      add_menu_page( 'Backups', 'Backups', 'manage_options', 'ero_website_download', [$this,'renderHTML'], 'dashicons-download', 75  );
    }

    // this generates html for the newly added page in admin ( called Backups )
    public function renderHTML(){

      /****** Enqueues the styles and scripts to the this specific page. ****
      ***********************************************************************/

      wp_enqueue_style('website-downloader-admin-css');               // plugin admin css
      wp_enqueue_style('website-downloader-datatables');              // datatables css
      wp_enqueue_script('website-downloader-datatables-script');      // datatables js
      wp_enqueue_script('website-downloader-admin-script');           // plugins admin js


      ?>
      <div class="ero_wrapper">
        <h1>Backups Management</h1>
        <br>
        <?php

        /******** Files Backup table design and html generation starts here ********
        ********************************************************************************/


        $files_download_args = [
          'option'        => 'ero_website_download_files',
          'heading'       => __('Files Backups', 'website-backups'),
          'create-button' => [
            'name' => __('Create Files Backup', 'website-backups'),
            'attributes'  => [
              'class'              => 'ero_btn ero_blue trigger_backup_create',
              'state'              => 'normal',
              'ero-loading-data'   => __('Creating backup....', 'website-backups'),
              'ero-afterload-data' => __('Create Backup', 'website-backups'),
              'ero-ajax-action'    => 'ero_create_backup'
            ]
          ],
          'action-buttons' => [
            'download' => [
              'name' => __('Download'),
              'attributes' => [
                'class' => 'ero_btn ero_btn_success'
              ]
            ],
            'delete' => [
              'name'        => __('Delete', 'website-backups'),
              'attributes'  => [
                'class'              => 'ero_btn ero_btn_danger trigger_backup_delete',
                'state'              => 'normal',
                'ero-loading-data'   => __('Deleting....', 'website-backups'),
                'ero-afterload-data' => __('Delete', 'website-backups'),
                'ero-ajax-action'    => 'ero_delete_backup'
              ]
            ]
          ],
          'titles'        => [ __('#', 'website-backups'), __('Backup Name', 'website-backups'), __('Backup Size', 'website-backups'), __('Created On', 'website-backups'), __('Actions', 'website-backups')]
        ];
        $this->renderDownloadHTML($files_download_args);

        /******** Files backup table design and html generation ends here ********
        ********************************************************************************/




        /******** database backup table design and html generation starts here ********
        ********************************************************************************/

        $files_download_args = [
          'option'        => 'ero_website_download_db',
          'heading'       => __('DB Backups', 'website-backups'),
          'create-button' => [
            'name' => __('Create DB Backup', 'website-backups'),
            'attributes'  => [
              'class'              => 'ero_btn ero_blue trigger_backup_create',
              'state'              => 'normal',
              'ero-loading-data'   => __('Creating backup....', 'website-backups'),
              'ero-afterload-data' => __('Create DB Backup', 'website-backups'),
              'ero-ajax-action'    => 'ero_create_db_backup'
            ]
          ],
          'action-buttons' => [
            'download' => [
              'name' => __('Download'),
              'attributes' => [
                'class' => 'ero_btn ero_btn_success'
              ]
            ],
            'delete' => [
              'name'        => __('Delete', 'website-backups'),
              'attributes'  => [
                'class'              => 'ero_btn ero_btn_danger trigger_backup_delete',
                'state'              => 'normal',
                'ero-loading-data'   => __('Deleting....', 'website-backups'),
                'ero-afterload-data' => __('Delete', 'website-backups'),
                'ero-ajax-action'    => 'ero_delete_backup'
              ]
            ]
          ],
          'titles'        => [ __('#', 'website-backups'), __('Backup Name', 'website-backups'), __('Backup Size', 'website-backups'), __('Created On', 'website-backups'), __('Actions', 'website-backups')]
        ];
        $this->renderDownloadHTML($files_download_args);

        /******** database backup table design and html generation ends here ********
        ********************************************************************************/

        ?>
      </div>
      <?php
    }
    public function extract_attributes( $attr ) {
      $attr_html = ' ';
      if(is_array($attr)){
        foreach ($attr as $key => $value) {
          $attr_html .= $key.'="'.esc_attr($value).'" ';
        }
      }
      return $attr_html;
    }

    public function renderDownloadHTML( $args ){
      $params = new \ero\websitebackups\params;
      $prevOptions = get_option($args['option']);

      if(isset($prevOptions)){
        $prevArray = unserialize($prevOptions);
      }
        ?>
        <h2> <?= _e($args['heading'], 'website-backups'); ?>
          <button type="button" <?php echo $this->extract_attributes($args['create-button']['attributes']); ?>> <?php echo $args['create-button']['name']; ?></button>
        </h2>
        <table class="ero_tables_skin_one data-centered ero_datatables">
          <thead>
            <tr>
              <?php
              foreach ($args['titles'] as $key => $value) {
                echo '<th>'.$value.'</th>';
              }
              ?>
            </tr>
          </thead>
          <tbody>
            <?php if(isset($prevArray) && is_array($prevArray) ) : $sr = 1; ?>
              <?php foreach ($prevArray as $key => $backups) : ?>
                <tr>
                  <td><?php _e($sr++, 'website-backups'); ?></td>
                  <td><?php _e($backups['name'], 'website-backups'); ?></td>
                  <td><?php _e(round($backups['size'] / 1024 / 1024,4).' MB', 'website-backups'); ?></td>
                  <td><?php _e($this->time_elapsed_string($backups['created On']), 'website-backups'); ?></td>
                  <td>
                    <a target="_blank" href="<?php echo $params::$ero_wd_upload_url.$backups['name'] ;?>" <?php echo $this->extract_attributes($args['action-buttons']['download']['attributes']); ?>> <?php echo $args['action-buttons']['download']['name']; ?> </a>
                    <button type="button" file="<?php echo $backups['name']; ?>" <?php echo $this->extract_attributes($args['action-buttons']['delete']['attributes']); ?>> <?php echo $args['action-buttons']['delete']['name']; ?> </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
          <tfooter></tfooter>
        </table>
        <?php
    }

    public function deleteBackup() {

      if(!isset($_POST['file']))
        die(json_encode(['status' => false, 'message'=> 'Backup Delete Failed !!! ']));

      $file = $_POST['file'];
      $uploads_folder = wp_upload_dir();
      $backup_folder = $uploads_folder['basedir']. '/ero-website-backups';

      // Deletes the backup file here
      wp_delete_file($backup_folder.'/'.$file);

      if(end(explode('.', $file)) == 'zip') {
        $option = 'ero_website_download_files';
      } else {
        $option = 'ero_website_download_db';
      }
      $prevOptions = unserialize( get_option( $option ) );
      foreach ($prevOptions as $key => $value) {
        if( $value['name'] == $file ){
          $filteredKey = $key;
        }
      }

      // Deleting the file from wordpress options
      unset($prevOptions[$filteredKey]);
      update_option( $option, serialize($prevOptions)  );

      echo json_encode([
        'status' => true,
        'message'=> 'Backup Deleted successfully'
      ]);
      wp_die();
    }

    public function process_backup(){
      global $wpdb; // this is how you get access to the database

      ini_set('max_execution_time', 900);
      ini_set('memory_limit', '500M');
      $params = new \ero\websitebackups\params;
      $wordpress_install_folder = realpath( $params::$ero_wd_base_path . '../../../' );

      //echo $backup_folder;
      if ( ! file_exists( $params::$ero_wd_upload_path ) ) {
        $config = new \ero\websitebackups\config;
        $config->__create_backups_folder();
      }

      $zipRes = $this->pclzipdata( $wordpress_install_folder ,$params::$ero_wd_upload_path );
      if(is_string($zipRes)){
        echo json_encode([
          'status' => true,
          'message' => 'Backup Created Successfully'
        ]);
      }else{
        echo json_encode([
          'status' => false,
          'message' => 'Backup Failed !!!!'
        ]);
      }
      //wp_redirect(self::$params::$ero_wd_base_url.'backups/'.$zipRes);
      wp_die(); // this is required to terminate immediately and return a proper response
    }

    public function zipData($source, $destination) {
      $a = false;
    	if (extension_loaded('zip')) {
    		if (file_exists($source)) {
    			$zip = new ZipArchive();
    			if ($zip->open($destination, ZIPARCHIVE::CREATE)) {
    				$source = realpath($source);
    				if (is_dir($source)) {
    					$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    					foreach ($files as $file) {
    						$file = realpath($file);

    						if (is_dir($file)) {
                  $extractedFolders = explode('/',$file);
                  if($extractedFolders[count($extractedFolders)-1] == 'backups' && ($extractedFolders[count($extractedFolders)-2] == 'website-downloader') ){
                    $a = true;
                  }
                  else{
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                    $a = false;
                  }

    						} else if (is_file($file)) {
                  if($a == false ){
                      $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                  }
    						}
    					}
    				} else if (is_file($source)) {
    					$zip->addFromString(basename($source), file_get_contents($source));
    				}
    			}
    			return $zip->close();
    		}
    	}
    	return false;
    }

    public function pclzipdata( $source, $destination ){

      $backupName = md5(time()).'.zip';
      $archive = new EROWD_PclZip( $destination.$backupName );
      $v_list = $archive->add( $source,
                              PCLZIP_OPT_REMOVE_PATH, get_home_path());

      if ($v_list == 0) {
        return $archive->errorInfo(true);
      }

      $fileSize = filesize($destination.$backupName);
      $prevOptions = get_option('ero_website_download_files');
      if( $prevOptions ) {
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
      update_option( 'ero_website_download_files', serialize($fileInfo)  );

      return $backupName;
    }

    public function time_elapsed_string($datetime, $full = false) {
      $now = new DateTime;
      $ago = new DateTime($datetime);
      $diff = $now->diff($ago);

      $diff->w = floor($diff->d / 7);
      $diff->d -= $diff->w * 7;

      $string = array(
          'y' => 'year',
          'm' => 'month',
          'w' => 'week',
          'd' => 'day',
          'h' => 'hour',
          'i' => 'minute',
          's' => 'second',
      );
      foreach ($string as $k => &$v) {
          if ($diff->$k) {
              $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
          } else {
              unset($string[$k]);
          }
      }

      if (!$full) $string = array_slice($string, 0, 1);
      return $string ? implode(', ', $string) . ' ago' : 'just now';
  }

}
