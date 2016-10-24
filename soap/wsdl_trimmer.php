<?
exit;
/*
include("../../config.php");

header('Content-Type: text/xml; charset=UTF-8');
$httpbasicauth = '';
$exp = explode('@', $_GET['url']);
if(count($exp) == 2)
{
    $exp = explode('//', $exp[0]);
    $httpbasicauth = $exp[1]; 
}
//echo $httpbasicauth; exit;

if(!empty($timeout))
{
    $oldTimeout = ini_get('default_socket_timeout');
    ini_set('default_socket_timeout', $timeout);
}
$str = file_get_contents($_GET['url']);
if(isset($oldTimeout))
{
    ini_set('default_socket_timeout', $oldTimeout);
}
$bom = pack("CCC", 0xef, 0xbb, 0xbf);
if (0 == strncmp($str, $bom, 3)) {
    echo "BOM detected - file is UTF-8\n";
    $str = substr($str, 3);
}
$str =  trim(iconv("ISO-8859-1","UTF-8",preg_replace("/<!--.*-->/Uis", "",$str)));
 
 if (0 == strncmp($str, $bom, 3)) {
    echo "BOM detected - file is UTF-8\n";
    $str = substr($str, 3);
}
if(!empty($httpbasicauth))
{
    //$str = preg_replace('/<soap:address location="http:\/\//', '<soap:address location="'.API_URL.'/soap/wsdl_trimmer.php?url=http://'.$httpbasicauth.'@', $str);
    //$str = preg_replace('/<soap:address location="https:\/\//', '<soap:address location="https://'.$httpbasicauth.'@', $str);
    //I must confess that I feel quite dirty from writing the parts about $httpbasicauth
    //Feel free to 
}
@echo preg_replace('/\?SID=........................../', '', $str);


