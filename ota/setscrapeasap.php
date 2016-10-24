<?php



$allow = array("41.0.152.164", "41.0.152.166", "41.21.218.148", "77.66.51.2", "77.66.51.3", "77.66.51.4","77.66.51.5","77.66.51.6","41.21.218.149", "41.0.152.164","41.0.152.166","41.0.152.165");
if (!in_array ($_SERVER['REMOTE_ADDR'], $allow)) 
{
       header("location: http://www.google.com/");
       exit();
}
require_once '../../config.php';    
require_once CLASS_FOLDER.'/autoload.php';


Autoload::init();

switch($_GET['host'])
{
    case 'uat':
        $id = 124;
        break;
    case 'dev':
        $id = 123;
        break;
     case 'game':
        $id = 146;
        break;
    default: 
        echo 'Could not find environment';
        exit;

}
$user = new User();
if($user->crawlAtNextCron($id)) {
    echo 'User with id: '.$id.' will be crawled soon!';
} else {
    echo 'Already sat';
}
