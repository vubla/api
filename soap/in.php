<?php 
//ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache 

/*
    require_once '../../config.php';
    require_once CLASS_FOLDER.'/autoload.php';
     
    Autoload::init();

    $user = new User();
    $userRes = $user->create(
        array(
            'url' => 'www.google.com',
            'subscription' => 1,
            'email' => 'alex@vubla.com', 
            'fullname' => 'John Johnson',
            'phone' => '12345678', 
            'address' => 'Fakerstreet 42',
            'postal'=> '1234',
            'city'=>'Someville',
            'password'=>'123456789',
            'password2'=>'123456789',
            //'master'=>'3'
        )
    );
 *  if(!empty($userRes)) {
        var_dump($userRes);
    }
 * */
    
   //echo 'john'; exit;
   $url = "http://alex.vubla.com/api/soap/index.php";
   echo $url . '<br/>';
   ini_set("soap.wsdl_cache_ttl", 1);
      
  $client = new SoapClient($url,array( 
    "trace"      => 1, 
    "exceptions" => 0,
    'login' => 'searcher',
    'password' => 'Trekant01'
    )); 
  //echo($client->login("rprentow@gmail.com","Trekant01")); 
  echo($client->login("abondoa@gmail.com","123456789") . '<br/>');
  $setting = $client->getSetting("mage_api_key");
  var_dump( $setting);
  echo  '<br/>';
  $client->setSetting("mage_api_key", $setting. '2');
  $setting2 = $client->getSetting("mage_api_key");
  var_dump($setting2);
  echo '<br/>';
  $client->setSetting("mage_api_key", $setting);
  
  $temp =$client->getStatistics(array(
    'type'=>'hitp',
    'tStart'=>time()-7*24*60*60,
    'tEnd'=>time()));
  echo $temp . '<br/>';
  $temp = $client->getLog(array(
  	'type'=>'log', 
  	//'max' => 40,
	'fields' => 'q   ,   words   '));
	//var_dump($temp);
  foreach ($temp->Result as $entry) {
      echo $entry->q . ' | ';
      foreach ($entry->words as $word ) {
          echo $word . ' ';
      }
      echo '<br/>';
  }
  echo '<br/>';
  $temp = $client->getHotSearches(array('type'=>'hit', 'max' => 10, 'tStart' => time() - 3600*24*7));
  foreach ($temp->Result as $entry) {
      echo $entry->word . ' | ';
      echo $entry->count;
      echo '<br/>';
  }
  echo '<br/>';
  
  $content = $client->search(array(
    'q' => 'matrox',
    'useragent'=>urlencode($_SERVER['HTTP_USER_AGENT']),
    'ip'=>urlencode($_SERVER['REMOTE_ADDR']),
    'max_options'=>array('lowest_price'=>300),
    'sort_by'=>'lowest_price',
    'sort_order'=>'desc'));
    
    echo "<div >";
    //var_dump($content);
    foreach ($content->Result->ids as $entry) {
      echo $entry;
      echo '<br/>';
    }
    echo "</div>";
    echo "<div >";
    foreach ($content->Result->keywords as $entry) {
      echo $entry->text .' '. $entry->url;
      echo '<br/>';
    }
    echo "</div>";
    //var_dump($content);
     
    echo "<div >";
    $cid = $client->createUser(
        array(
                'url' => 'www.google.com',
                'email' => 'alex@vubla.com', 
                'fullname' => 'John Johnson',
                'subscription' => 'small',
                'phone' => '12345678', 
                'address' => 'Fakerstreet 42',
                'postal'=> '1234',
                'city'=>'Someville',
                'password'=>'123456789'
            ));
    echo $cid;
    echo "</div>";
    echo "<div>";
    echo $client->deleteUser(
        array(
            'email' => 'alex@vubla.com'
        ));
    echo "</div>";
    echo "<div>";
    echo $client->recoverUser(
        array(
            'email' => 'alex@vubla.com'
        ));
    echo "</div>";
    
    echo "<div>";
    echo $client->deleteUser(
        array(
            //'email' => 'alex@vubla.com'
            'cid' => $cid
        ));
    echo "</div>";
    
    echo "<div>";
    echo $client->purgeUser(
        array(
            //'email' => 'alex@vubla.com'
            'cid' => $cid
        ));
    echo "</div>";
    /* * */

  print "<pre>\n"; 
  print "Request :\n".htmlspecialchars($client->__getLastRequest()) ."\n"; 
  print "Response:\n".htmlspecialchars($client->__getLastResponse())."\n"; 
  print "Request :\n".htmlspecialchars($client->__getLastRequestHeaders()) ."\n"; 
  print "Response:\n".htmlspecialchars($client->__getLastResponseHeaders())."\n"; 

  print "</pre>"; 
  
  ?>