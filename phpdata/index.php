<?php
	#############################
	#DOWNLOADABLE CODE FOR MODULES
	#
	#############################
	
	require_once '../../config.php';
	require_once CLASS_FOLDER.'/autoload.php';
	Autoload::init();
	set_exception_handler('exceptionHandler');
	error_reporting(-1);
	ini_set('display_errors', 'stdout');

    define('INTERNAL_OK', true); // For security purpose
    
  
    if(isset($_GET['pkey'])){
        $key = openssl_pkey_get_public('file:///var/vubla/keys/public/api.vubla.com.key');
        $det = openssl_pkey_get_details($key);
        echo $det["key"];
        exit;
    } 
    // Following should check webshop type
    
    if(isset($_GET['host'])){
        
    }
    include('oscommerce.php');
   

?>