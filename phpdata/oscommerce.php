<?php 

if(!defined('INTERNAL_OK')){
    exit;
}
$content =<<<'EOL'
$allow = array("77.66.51.2", "77.66.51.3", "77.66.51.4","77.66.51.5","77.66.51.6");
if (!in_array ($_SERVER['REMOTE_ADDR'], $allow)) {
   header("location: http://www.google.com/");
   exit();
}

header('Content-Type: text/xml; charset=utf-8');
require('includes/application_top.php');

function vubla_translate($key){

    switch ($key){
        case 'options_values_price':
            $key = 'value_price';
            break; 
        case 'products_options_values_name':
            $key = 'name';
            break;
    }
    
    return $key;

}

echo "<catalog>\n";
if(isset($_GET["id"])) {
    $extra = " where p.products_id = '" . (int) $_GET["id"] . "'"; 
}
 
$product_query = tep_db_query("select p.products_id, products_model, products_image,products_price, m.manufacturers_name,m.manufacturers_id from " . TABLE_PRODUCTS . " p left join products_to_categories p2c on p2c.products_id = p.products_id left join categories_description cd on cd.categories_id = p2c.categories_id  left join manufacturers m on m.manufacturers_id = p.manufacturers_id left join categories c on c.categories_id = p2c.categories_id left join categories_description cp on cp.categories_id = c.parent_id" . $extra. " WHERE products_status = 1 ORDER BY p.products_id ASC");

while($product = tep_db_fetch_array($product_query)){
	
	
	$product_description_query = tep_db_query("select  products_name,products_description from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = ". $product['products_id']);
	
	$product_description = tep_db_fetch_array($product_description_query);
	
	  
	$categories = tep_db_query("select categories_id from products_to_categories where products_id = ".(int) $product['products_id']);
	$cat_temp = tep_db_fetch_array($categories);
	$category_id = $cat_temp['categories_id'];
	

	
	echo "<product>\n";
	echo "<pid>".$product['products_id']."</pid>\n";
	 echo '<category>'. $category_id.'</category>\n';
	foreach($product as $key=>$val){
	    
		switch($key){
		    case 'products_id':
			continue;
			break;
			case 'products_image':
			$val = 'images/'.$val;
			break;
		}
		
		$key = vubla_translate($key);
		if($key != 'products_id'){
	       echo "<option>\n\t<name>$key</name>\n\t<value>\n\t\t<name>" . strip_tags($val) . "</name>\n\t</value>\n</option>\n";
        }
	}
   
    
	echo '<option><name>url</name><value><name>product_info.php?products_id='. $product['products_id']."</name></value></option>\n"; 
	foreach($product_description as $key=>$val){
	    $key = vubla_translate($key);
	    echo "<option>\n\t<name>$key</name>\n\t<value>\n\t\t<name>" . strip_tags($val) . "</name>\n\t</value>\n</option>\n";
	}
	
	$special_query = tep_db_query("select specials_new_products_price from specials where products_id = ". $product['products_id']);
	
	$special = tep_db_fetch_array($special_query);
    if($special['specials_new_products_price']){
        echo '<option><name>discount_price</name><value><name>' . $special['specials_new_products_price']. '</name></value></option>'."\n";
    }
    if($_SERVER['HTTP_HOST'] =='everlight.dk'){
        @$stocknumber_q = tep_db_query("select stock_level_item_number from stock_level inner join products on stock_level.stock_level_id = products.stock_level_id where products_id = ". $product['products_id']);
	
	   @$sku = tep_db_fetch_array($stocknumber_q);
        echo '<sku>' . $sku['stock_level_item_number']. '</sku>' .PHP_EOL;
    }   


	
	
	
	echo "<option><name>buy_link</name><value><name>".strip_tags("index.php?cPath=3_10&amp;sort=2a&amp;action=buy_now&amp;products_id=". $product['products_id']) . "</name></value></option>\n";
	
	
	/// Options
    $product_att_query = tep_db_query("select * from products_attributes as pa left join products_options po on pa.options_id = po.products_options_id  where products_id = '" . $product['products_id'] . "' group by options_id");   
 
    while($product_att = tep_db_fetch_array($product_att_query)){
            
         echo "<option>\n";
    
        echo "\t<name>" . $product_att["products_options_name"] . "</name>\n";
        $product_att_query2 = tep_db_query("select * from 
                                                         products_attributes as pa 
                                                     left join 
                                                         products_options_values po 
                                                     on 
                                                         pa.options_values_id = po.products_options_values_id  
                                                     where 
                                                        products_id = '" . $product['products_id'] . "' 
                                                     and 
                                                        options_id = ". $product_att["products_options_id"]);
        while($product_att2 = tep_db_fetch_array($product_att_query2)){
            echo "\t\t<value>";
            foreach($product_att2 as $key=>$val){
                $name = vubla_translate($key);
                echo  "\t\t".'<'. $name . '>' . $val . '</'. $name . ">\n";
            }
            echo "\t</value>\n";
        } 
        
     //   echo "</values>\n";
   
        
        echo "</option>\n";
    }
    
	echo "</product>\n";
	
}
$cat_q = tep_db_query("select cd.categories_id, categories_name, parent_id from categories c join categories_description cd on cd.categories_id = c.categories_id");
while($categories = tep_db_fetch_array($cat_q)){
   echo "<category>\n";
   echo '<id>'.$categories['categories_id'].'</id>';
   echo '<name>'.$categories['categories_name'].'</name>';
   echo '<parent_id>'.$categories['parent_id'].'</parent_id>';
   echo '</category>';
}        
    



echo "</catalog>";
EOL;

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