<?php

class EROWD_Database_Backup {
  private static $__instance = null;
  private $__DB_CONNECTION = null;
  private $DB_HOST = DB_HOST;
  private $DB_USER = DB_USER;
  private $DB_PASSWORD = DB_PASSWORD;
  private $DB_NAME = DB_NAME;
  private $BACKUPS_DIR = '';
  private $BACKUP_QUERY_ARRAY = [];

  /**
  * getting instance of this class
  *
  * @return object
  */
  public static function get_instance() {
    // checking $__instance is null
    if ( is_null( self::$__instance ) ) {
      // if $__instance is null create instance of self and store in it
      self::$__instance = new self();
    }
    // returning the instance of self
    return self::$__instance;
  }

  /**
  * creating database connection
  *
  * @return object
  */
  public function connection() {
    // checking $__DB_CONNECTION is null
    if ( is_null( $this->__DB_CONNECTION ) ) {
      // if $__DB_CONNECTION is null create connection and store in it
      $this->__DB_CONNECTION = new mysqli(
        $this->DB_HOST,
        $this->DB_USER,
        $this->DB_PASSWORD,
        $this->DB_NAME
      );

      // check if database connection have error
      if( $this->__DB_CONNECTION->connect_error ) {
        // if database connection have error stop php execution and print error
        die( $this->__DB_CONNECTION->connect_error );
      }
    }
    // returning database connection instance
    return $this->__DB_CONNECTION;
  }

  /**
  * Getting All The Tables Of Database
  *
  * @return boolean|array
  */
  public function get_tables() {
    // Fetching the tables of database and check if database have tables
    if ( $result = $this->connection()->query( 'SHOW TABLES' ) ) {
      $tables = $result->fetch_all( MYSQLI_NUM );
      $tables = array_map( function ( $table ) {
        if( !empty( $table[0] ) ) return $table[0];
      }, $tables );
      // returning the array of database tables
      return $tables;
    }
    return false;
  }

  /**
  * create backup of wordpress database
  *
  * @return boolean|array
  */
  public function backup_tables() {
    ini_set("memory_limit", "-1");
    // getting the tables for backup
    $tables = $this->get_tables();

    // check if database have tables
    if( ! $tables ) {
      return false;
    }
    foreach( $tables as $table ) {
      $table_data = $this->connection()->query( "SELECT * FROM {$table}" );
      $table_data_count = $this->connection()->affected_rows;
      $table_fields_count = $table_data->field_count;
      $table_create_query = $this->connection()->query("SHOW CREATE TABLE {$table}");
      $table_create_query_result = $table_create_query->fetch_row();
      $this->BACKUP_QUERY_ARRAY[] = "/********** Table {$table} *************/\n\n\n";
      $this->BACKUP_QUERY_ARRAY[] = "DROP TABLE IF EXISTS {$table};";
      $this->BACKUP_QUERY_ARRAY[] = "\n\n{$table_create_query_result[1]};\n\n";

      // creating table insert queries
      $this->create_table_inserts( $table, $table_data, $table_data_count, $table_fields_count );

      $this->BACKUP_QUERY_ARRAY[] = "/********** Table {$table} *************/\n\n\n";
    }

    // creating database backup file
    return $this->create_backup_file( true );
  }

  /**
  * create table insert queries
  * @param $table
  * @param $table_data
  * @param $table_data_count
  * @param $table_fields_count
  *
  * @return null
  */
  public function create_table_inserts(
    $table,
    $table_data,
    $table_data_count,
    $table_fields_count
  ) {
    for ( $i = 0; $i < $table_fields_count; $i++ ) {
			while( $row = $table_data->fetch_row() ) {
				$query = "INSERT INTO {$table} VALUES(";
				for( $j = 0; $j < $table_fields_count; $j++ ) {
					$row[$j] = addslashes($row[$j]);
					$row[$j] = preg_replace("/\n/","\\n",$row[$j]);
					if (isset($row[$j])) {
            $query .= "\"{$row[$j]}\"";
          } else {
            $query .= '""';
          }
					if ($j < ($table_fields_count-1)) {
            $query .= ',';
          }
				}
				$query .= ");\n";
				$this->BACKUP_QUERY_ARRAY[] = $query;
			}
		}
		$this->BACKUP_QUERY_ARRAY[] = "\n\n\n";
  }

  /**
  * create backup file of database
  * @param $compress
  *
  * @return boolean|array
  */
  public function create_backup_file( $compress = false ) {
    $params = new \ero\websitebackups\params;
    $this->BACKUPS_DIR = $params::$ero_wd_upload_path;
    if ( ! file_exists( $this->BACKUPS_DIR ) ) {
      $config = new \ero\websitebackups\config;
      $config->__create_backups_folder();
    }

    $time = time();
    $file_name = "db-{$this->DB_NAME}-backup-{$time}.sql";
    $file_name_dir = "{$this->BACKUPS_DIR}/{$file_name}";
    $file = fopen( $file_name_dir, 'w' );
    fwrite( $file, implode( '', $this->BACKUP_QUERY_ARRAY ) );
    fclose( $file );

    // checking if requested of compressed file
    if( $compress ) {
      // compressing database backup file
      $this->compress_backup( $file_name_dir );
      $file_name .= '.gz';
      unlink( $file_name_dir );
    }

    if( file_exists( $file_name_dir ) ) {
      return false;
    }

    return [
      'name' => $file_name,
      'path' => $file_name_dir
    ];
  }

  /**
  * create .gz file of an sql file
  * @param $file_name
  *
  * @return null
  */
  public function compress_backup( $file_name ) {
    $fp = gzopen( $file_name . '.gz', 'w9' );
    gzwrite( $fp, file_get_contents( $file_name ) );
    gzclose($fp);
  }
}
