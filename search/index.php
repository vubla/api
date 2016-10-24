<?php
	/*#############################
	#PERFORM SEARCH
	#
	#"Versions" or something
	#-110608 Rasmus
	#-110805 Some unused stuff cleaned 
	#and code commented by Joakim ;)
	#############################*/

	ob_start();
	
      
    if(isset($_GET) && isset($_GET['devname']))
    {
        $devname = str_replace('/', '.', $_GET['devname']);
        $devname = explode('.', $devname);
        $_GET['devname'] = null;
        print(file_get_contents('http://'.$devname[0].'.vubla.com/'.$devname[1].'/api/search/?'.http_build_query($_GET,'','&')));
        exit;
    } 
    else if(isset($_GET) && isset($_GET['api_version']) && $_GET['api_version'] >= '2.0' )
    {
        print(file_get_contents('http://localhost:8080/?'.http_build_query($_GET)));
        exit;
    }
	require_once '../../config.php';    
    //require_once '../../../../alex.vubla.com/htdocs/config.php';    
	require_once CLASS_FOLDER.'/autoload.php';

    
	Autoload::init();
  
    try {
	   $searchHandler = new SearchHandler();
       $out = $searchHandler->getOutput();
    
        ob_end_clean();
        print($out);
    } catch (Exception $e) {
         if(defined('VUBLA_DEBUG') && VUBLA_DEBUG)
         {
             throw $e;
         }
         @ob_end_clean();
    }
	