<?php

# Start sessions
//session_start();

# Defines the function apache_request_headers if it does not already exist
if (!function_exists('apache_request_headers')) {
    eval('
        function apache_request_headers() {
            foreach($_SERVER as $key=>$value) {
                if (substr($key,0,5)=="HTTP_") {
                    $key=str_replace(" ","-",ucwords(strtolower(str_replace("_"," ",substr($key,5)))));
                    $out[$key]=$value;
                }
            }
            return $out;
        }
    ');
}

# Defines
require_once("defines.inc.php");
require_once("classes/Api.php");
require_once("classes/Request.php");
require_once("classes/Response.php");
require_once("classes/Transaction.php");
require_once("classes/ResourcePath.php");
require_once("xml2json/xml2json.php");

# Global Error Holder
$GLOBALS['ERROR'] = Array();

# DB Connection
$GLOBALS['db'] = new PDO("pgsql:host=".DB_HOST.";port=5432;dbname=".DB_NAME.";user=".DB_USER.";password=".DB_PASS);
$GLOBALS['transaction'] = new Transaction($GLOBALS['db']);
 

?>
