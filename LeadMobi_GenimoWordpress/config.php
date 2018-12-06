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
const BASE_IMAGES = "https://ruteimoveis.com/wp-content/uploads/";
const RELATIVE_PT = "genimo/";
const REST_MEDIA_URL = "https://ruteimoveis.com/wp-json/wp/v2/media";
//Lead mobbsss
//Lead Mobi
const MAUTIC_PUBLIC_KEY = "545h4a5zslc0404cs0c08sw0w8oowokgs4gg8k8kgccs4wcgwg";
const MAUTIC_SECRET_KEY = "3dd6uhl1ujms4g800s0004go08wkwwswg00cwoscs4scgsgw40";
const MAUTIC_INSTANCE_URL = "https://inbound.citywatch.com.br";
const MAUTIC_INSTANCE_API = "https://inbound.citywatch.com.br/api/";
const MAUTIC_CALLBACK_URL = "https://ruteimoveis.com/synchronize/leadmobi/";
const MAUTIC_USER = "leadmobi";
const MAUTIC_PASS = "leadmobi";
const CD_IMOBILIARIA = 41;
//Segmentos
const MAUTIC_S_NAME_C = "Corretores";
const MAUTIC_S_SLUG_C = "Corretores-Genimo-Wordpress";
const MAUTIC_S_DESC_C = "Corretores Leads Genimo Wordpress";
//Clientes
const MAUTIC_S_NAME_L = "Imobiliaria Jorge Floriani";
const MAUTIC_S_SLUG_L = "jorge-floriani";
const MAUTIC_S_DESC_L = "Leads Jorge Floriani";
?>