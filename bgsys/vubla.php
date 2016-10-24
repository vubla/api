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

//header('Content-Type: text/xml; charset=utf-8');
require('system/includes/application_top.php');

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

$product_query = bg_db_query("select p.products_id, p.products_model, p.products_image, p.products_price, m.manufacturers_name, m.manufacturers_id from " . TABLE_PRODUCTS . " p left join manufacturers m on m.manufacturers_id = p.manufacturers_id WHERE $extra products_status = 1 ORDER BY p.products_id ASC");

while($product = bg_db_fetch_array($product_query)){
	
	
	$product_description_query = bg_db_query("select  products_name,products_description from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = ". $product['products_id']);
	
	$product_description = bg_db_fetch_array($product_description_query);
	
	echo "<product>\n";
    echo "<pid>".$product['products_id']."</pid>\n";  
	
	
	$categories = bg_db_query("select categories_id from products_to_categories where products_id = ".(int) $product['products_id']);
    while($cat_temp = bg_db_fetch_array($categories)){
	   echo '<category>'. $cat_temp['categories_id'].'</category>'."\n";
    }
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
	       echo "<option>\n\t<name>$key</name>\n\t<value>\n\t\t<name>" . htmlspecialchars(strip_tags($val)) . "</name>\n\t</value>\n</option>\n";
        }
        if($key == 'products_model'){
           echo "<option>\n\t<name>sku</name>\n\t<value>\n\t\t<name>" . htmlspecialchars(strip_tags($val)) . "</name>\n\t</value>\n</option>\n";
        }
	}
   
    
	echo '<option><name>url</name><value><name>product_info.php?products_id='. $product['products_id']."</name></value></option>\n"; 
	foreach($product_description as $key=>$val){
	    $key = vubla_translate($key);
	    echo "<option>\n\t<name>$key</name>\n\t<value>\n\t\t<name>" . htmlspecialchars(strip_tags($val)) . "</name>\n\t</value>\n</option>\n";
	}
	
	$special_query = bg_db_query("select specials_new_products_price from specials where products_id = ". $product['products_id'] . "  and status = 1 and specials_end_date > '".date('Y-m-d 00:00:00',time())."' and specials_start_date < '".date('Y-m-d 00:00:00',time())."'");
	
	$special = bg_db_fetch_array($special_query);
    if($special['specials_new_products_price']){
        echo '<option><name>discount_price</name><value><name>' . $special['specials_new_products_price']. '</name></value></option>'."\n";
    }
    if($_SERVER['HTTP_HOST'] =='everlight.dk'){
        @$stocknumber_q = bg_db_query("select stock_level_item_number from stock_level inner join products on stock_level.stock_level_id = products.stock_level_id where products_id = ". $product['products_id']);
	
	   @$sku = bg_db_fetch_array($stocknumber_q);
        echo '<sku>' . $sku['stock_level_item_number']. '</sku>' .PHP_EOL;
    }   


	
	
	
	echo "<option><name>buy_link</name><value><name>".htmlspecialchars(strip_tags("index.php?cPath=3_10&amp;sort=2a&amp;action=buy_now&amp;products_id=". $product['products_id'])) . "</name></value></option>\n";
	
	
	/// Options
    $product_att_query = bg_db_query("select * from products_attributes as pa left join products_options po on pa.options_id = po.products_options_id  where products_id = '" . $product['products_id'] . "' group by options_id");   
 
    while($product_att = bg_db_fetch_array($product_att_query)){
            
         echo "<option>\n";
    
        echo "\t<name>" . $product_att["products_options_name"] . "</name>\n";
        $product_att_query2 = bg_db_query("select * from 
                                                         products_attributes as pa 
                                                     left join 
                                                         products_options_values po 
                                                     on 
                                                         pa.options_values_id = po.products_options_values_id  
                                                     where 
                                                        products_id = '" . $product['products_id'] . "' 
                                                     and 
                                                        options_id = ". $product_att["products_options_id"]);
        while($product_att2 = bg_db_fetch_array($product_att_query2)){
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
$cat_q = bg_db_query("select cd.categories_id, categories_name, parent_id from categories c join categories_description cd on cd.categories_id = c.categories_id");
while($categories = bg_db_fetch_array($cat_q)){
   echo "<category>\n";
   echo '<id>'.$categories['categories_id'].'</id>';
   echo '<name>'.htmlspecialchars($categories['categories_name']).'</name>';
   echo '<parent_id>'.$categories['parent_id'].'</parent_id>';
   echo '</category>';
}        
    



echo "</catalog>";
EOL;
?>