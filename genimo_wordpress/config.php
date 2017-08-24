<?php

/*
 *
 * @ Basic configuration setup
 * 
 * 
 *  */

include_once('../wp-config.php');
//Database configuration
DB::$user = DB_USER;
DB::$password = DB_PASSWORD;
DB::$dbName = DB_NAME;
DB::$host = DB_HOST;
DB::$port = '3306';
DB::$error_handler = 'my_error_handler';

const PATH_UPLOAD = "/var/www/frute.genimo.com.br/public_html/wp-content/uploads/";
const BASE_IMAGES = "http://frute.genimo.com.br/wp-content/uploads/";
const RELATIVE_PT = "genimo/";
const REST_MEDIA_URL = "http://frute.genimo.com.br/wp-json/wp/v2/media";
?>