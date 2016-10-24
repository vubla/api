<?php
exit(); // Hereby formally taken out. 
error_reporting(1);  
//ini_set('display_errors', 'stdout');
require_once '../../config.php';    

function customError($errno, $errstr)
{
   
    if(isset($_GET['debug']) && $_GET['debug'])
    {
        echo "<pre>";
        echo $errstr.$errno; 
        var_dump(debug_backtrace());
        echo "</pre>";
        exit;
    }
    header('Content-Type: image/png');
    echo file_get_contents(API_URL.'/images/no-picture.png'); 
    
    exit;
}
set_error_handler("customError");
require_once CLASS_FOLDER.'/autoload.php';
Autoload::init();
   
if(     !isset($_GET['image_link']) || 
        !isset($_GET['pid']) || 
        !isset($_GET['wid']) || 
        !isset($_GET['h']) || 
        md5("super picture salt who you never guess. My name is rasmus and im super cool 1231564654231321".$_GET['image_link']) != $_GET['h'])  {
 
    $image_info = getimagesize($_GET['image_link']);
         
    $image = file_get_contents($_GET['image_link']); 
     
    if(!$image){
        trigger_error('None');
    }
    CachedImage::setHeaderStatic( $image_info[2]);  
    echo $image;
    exit;
     
}
   
   
$image = new CachedImage($_GET['wid'], $_GET['pid'], $_GET['image_link']);  
 

$image->setHeader();

$image->output();  

exit();
