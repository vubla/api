<?php
    /*#############################
    #PERFORM SEARCH
    #
    #"Versions" or something
    #-110608 Rasmus
    #-110805 Some unused stuff cleaned 
    #and code commented by Joakim ;)
    #############################*/
    header('Access-Control-Allow-Origin: *');
    if(empty($_POST) && !empty($_GET)) $_POST = $_GET;
    //$_POST['ajax_only_results'] = 1;
    //ob_start();
    
  
    require_once '../../config.php';    
    //require_once '../../../../alex.vubla.com/htdocs/config.php';    
    require_once CLASS_FOLDER.'/autoload.php';

    Autoload::init();
	if(!isset($_POST['ip'])) {
		$_POST['ip'] = $_SERVER['REMOTE_ADDR'];
	}
  
    try {
        $searchHandler = new SearchHandler();
        $out = $searchHandler->getOutput();
        ob_end_clean();
        print($out);
    } catch (Exception $e) {
         @ob_end_clean();
         if(defined('VUBLA_DEBUG') && VUBLA_DEBUG) {
             print_r($e);
         }
    }
    
?>