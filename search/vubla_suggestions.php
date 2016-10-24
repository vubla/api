<?php
	/*#############################
	#PERFORM SEARCH
	#
	#"Versions" or something
	#110907 - Joakim - Based on /search/index.php
	#############################*/

	error_reporting(-1);
	require_once '../../config.php';
	require_once CLASS_FOLDER.'/autoload.php';
	 
	Autoload::init();
  
	/*#############################
	#GET USER DATA & OPEN WEBSHOP
	#############################*/
	$pass = DB_PASS;
	$user = DB_USER;

	define('MAX_DIST_PERCENT', 50);

    
	if(!isset($_GET['host']) || empty($_GET['host'])) die();	
	$host = $_GET['host'];

	if(!isset($_GET['q']) || empty($_GET['q'])) die();
 	$q = $_GET['q'];
	
	$meta = VPDO::getVdo(DB_METADATA);
	$sql = "Select id from webshops where hostname like " . $meta->quote('%'.$host.'%'). " limit 1";
	$wid = $meta->fetchOne($sql);

	if(!$wid) die();
	define('WID', $wid);
	
	$isEnabled = Settings::get('enabled',$wid);
	@$getEnbled = $_GET['enable'];
	if(!$isEnabled && !$getEnbled) {
		exit();
	}
	
	$pdo = VPDO::getVdo(DB_PREFIX . WID);
	
	#############################
	#PERFORM SEARCH
	#############################
	$search = new Search($q);
	$results = $search->getSuggestions($q);
    
    if(!is_null($results) || sizeof($results) > 0){
        $out = new stdClass();
        $out->results = $results;
        $out->q = $q;
		die(json_encode($out));
    }
?>