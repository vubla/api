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
    
    if(isset($_GET['pkey'])) {
        $key = openssl_pkey_get_public ('file:///var/vubla/keys/public/api.vubla.com.key');
        $det = openssl_pkey_get_details($key);
        echo $det["key"];
        exit;
    } 
    
    // Following should check webshop type
    if(isset($_GET['file'])) {
        $file = $_GET['file'];
    } else {
        $file = 'vubla';
    }
    $file = str_replace('_', '.', $file);
    switch ($file) {
        //Any of these files will set the content variable 
        case 'default':
            include('bg_advanced_search_result.php');
            break;
        case 'vubla':
        case 'vubla.php':
            include('vubla.php');
            break;
        default:
            exit;
    }
    
    //Encrypt the content
    $key = openssl_pkey_get_private('file:///var/vubla/keys/private/api.vubla.com.key','vublakey12#');
    $det = openssl_pkey_get_details($key);
    
    //var_dump($det); 
    $content2 = base64_encode($content);
    //echo $content2;
    $chunks = str_split ( $content2, 64 );
    $output = '';
    foreach($chunks as $chunk){
        
        $result = openssl_private_encrypt($chunk, $crypted, $key);
        
        $output .= $crypted;
        
    }
    $output = base64_encode($output);
    
    echo $output;

?>