<?php
/**
 * @package Ero_Website_Backups
 * @version 1.0
 */
/*
Plugin Name: Ero Website Backups
Plugin URI: https://wordpress.org/plugins/website-backups/
Description: This is a plugin for downloading the website file and database backups
Author: Khushwant
Version: 1.0
Author URI: http://erosteps.com
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2016-2018 Erosteps
*/

// Limit the direct access
( ! defined( 'ABSPATH' ) ) ? ( exit ) : ( '' ) ;

// Basic values


//define('ERO_WD_BASE_PATH', plugin_dir_path(__FILE__));
//define('ERO_WD_BASE_URL', plugin_dir_url(__FILE__));
//define('ERO_WD_LIBS', ERO_WD_BASE_PATH . '/libs');


require_once 'libs/params.php' ;
require_once 'libs/config.php' ;
$config = new \ero\websitebackups\config;
new EROWD_Backups;
new EROWD_DBexport;

// Registring Plugin Activation Hook
register_activation_hook( __FILE__, array( $config, '__activation' ) ) ;
?>
