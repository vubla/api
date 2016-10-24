<?php


require_once '../../config.php';
require_once CLASS_FOLDER.'/autoload.php';
Autoload::init();
set_exception_handler('exceptionHandler');
//error_reporting(-1);
//ini_set('display_errors', 'stdout');

if(is_null($_GET['id'])) {
    //echo 'id';
    exit;
}
$wid = 0;
if(!isset($_GET['host'])) {
    if(!isset($_GET['wid'])) {
    	exit;
    }
	else 
	{
		$wid = $_GET['wid'];
	}
}
else 
{
	$wid = HttpHandler::resolveWid($_GET['host']);
}

if($wid <= 0) {
	exit;
}
$id  = $_GET['id'];
$isEnabled = Settings::get('enabled',$wid);
@$getEnbled = $_GET['enable'];
if(!$isEnabled && !$getEnbled) {
    exit();
}

$test = NULL;
if(isset($_GET['test'])) {
    $test = trim(urldecode($_GET['test']));
    if(strpos($test,'/') || strpos($test,'..')) // Otherwise anyone could access all our files :/
    {
        $test = null;
    }
    $test = CLASS_FOLDER . '/tests/testjs/'.$test.'_test.js';
}


switch ($id) {
	case 'magento_all_pages':
        header("Content-type: text/javascript");
		$jsGen = new JavaScriptGenerator($wid,$test); 
		echo $jsGen->generateSuggestion();
		break;
	
	case 'suggestion_css':
        header("Content-type: text/css");
		$cssGen = new CSSGenerator($wid); 
		echo $cssGen->generateSuggestion();
		break;
	default:
		
		break;
}
?>