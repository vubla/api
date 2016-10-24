<?php 
if(!defined('INTERNAL_OK')){
    exit;
}
$content =<<<'EOL'

function validateFieldData($keywords){
    $keywords = str_replace("<script>" , "" , $keywords);
    $keywords = str_replace("</script>" , "" , $keywords);
    $keywords = str_replace("<" , "" , $keywords);
    $keywords = str_replace(">" , "" , $keywords);
    $keywords = str_replace("'" , "" , $keywords);
    $keywords = str_replace("\"" , "" , $keywords);
    $keywords = str_replace("(" , "" , $keywords);
    $keywords = str_replace(")" , "" , $keywords);
    $keywords = str_replace(";" , "" , $keywords);
    
    return $keywords;
}

function vbl_get_content($url){
    if(ini_get('allow_url_fopen')) {
        $content = file_get_contents($url);
    }
    else {
        //Get content
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        
        $content = curl_exec($ch);
        
        curl_close($ch);

    }

    return $content;
    
}

if(isset($_GET['keywords']) && $_GET['keywords'] != ''){
    $_GET['keywords'] = validateFieldData($_GET['keywords']);
}


 
  require('system/includes/application_top.php');
   
    language_variables(FILENAME_ADVANCED_SEARCH, $language, $languages_id);
    require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_ADVANCED_SEARCH);
    


// Search enhancement mod start

if(isset($_GET['keywords']) && $_GET['keywords'] != ''){
    if(!isset($_GET['s'])){
        $pwstr_check = strtolower(substr($_GET['keywords'], strlen($_GET['keywords'])-1, strlen($_GET['keywords'])));
        if($pwstr_check == ''){
                $pwstr_replace = substr($_GET['keywords'], 0, strlen($_GET['keywords'])-1);
                header('location: ' . bg_href_link(SYSTEM_FOLDER . DIR_WS_APPLICATIONS .  FILENAME_ADVANCED_SEARCH_RESULT , 'search_in_description=1&s=1&keywords=' . urlencode($pwstr_replace) . '' ));
                exit;
        }
        } 

       $pw_keywords = explode(' ',stripslashes(strtolower($_GET['keywords'])));
       $pw_boldwords = $pw_keywords;
       $sql_words = bg_db_query("SELECT * FROM searchword_swap");
       $pw_replacement = '';
       while ($sql_words_result = bg_db_fetch_array($sql_words)) {
           if(stripslashes(strtolower($_GET['keywords'])) == stripslashes(strtolower($sql_words_result['sws_word']))){
               $pw_replacement = stripslashes($sql_words_result['sws_replacement']);
               $pw_link_text = '<b><i>' . stripslashes($sql_words_result['sws_replacement']) . '</i></b>';
               $pw_phrase = 1;
               $pw_mispell = 1;
               break;
           }
           for($i=0; $i<sizeof($pw_keywords); $i++){
               if($pw_keywords[$i]  == stripslashes(strtolower($sql_words_result['sws_word']))){
                   $pw_keywords[$i]  = stripslashes($sql_words_result['sws_replacement']);
                   $pw_boldwords[$i] = '<b><i>' . stripslashes($sql_words_result['sws_replacement']) . '</i></b>';
                   $pw_mispell = 1;
                   break;
               }
           }    
       }
       if(!isset($pw_phrase)){
           for($i=0; $i<sizeof($pw_keywords); $i++){
               $pw_replacement .= $pw_keywords[$i]. ' ';
               $pw_link_text   .= $pw_boldwords[$i]. ' ';   
           }
       }
       
       $pw_replacement = trim($pw_replacement);
       $pw_link_text   = trim($pw_link_text);
       $pw_string      = '<br><span class="main"><font color="red">' . TEXT_REPLACEMENT_SUGGESTION . '</font><a href="' . bg_href_link(SYSTEM_FOLDER . DIR_WS_APPLICATIONS .  FILENAME_ADVANCED_SEARCH_RESULT , 'keywords=' . urlencode($pw_replacement) . '&search_in_description=1' ) . '">' . $pw_link_text . '</a></span><br><br>';
}
// Search enhancement mod end

  $error = false;

  if ( (isset($_GET['keywords']) && empty($_GET['keywords'])) &&
       (isset($_GET['dfrom']) && (empty($_GET['dfrom']) || ($_GET['dfrom'] == DOB_FORMAT_STRING))) &&
       (isset($_GET['dto']) && (empty($_GET['dto']) || ($_GET['dto'] == DOB_FORMAT_STRING))) &&
       (isset($_GET['pfrom']) && !is_numeric($_GET['pfrom'])) &&
       (isset($_GET['pto']) && !is_numeric($_GET['pto'])) ) {
    $error = true;

    $messageStack->add_session('search', ERROR_AT_LEAST_ONE_INPUT);
  } else {
    $dfrom = '';
    $dto = '';
    $pfrom = '';
    $pto = '';
    $keywords = '';

    if (isset($_GET['dfrom'])) {
      $dfrom = (($_GET['dfrom'] == DOB_FORMAT_STRING) ? '' : $_GET['dfrom']);
    }

    if (isset($_GET['dto'])) {
      $dto = (($_GET['dto'] == DOB_FORMAT_STRING) ? '' : $_GET['dto']);
    }

    if (isset($_GET['pfrom'])) {
      $pfrom = $_GET['pfrom'];
    }

    if (isset($_GET['pto'])) {
      $pto = $_GET['pto'];
    }

    if (isset($_GET['keywords'])) {
      $keywords = $_GET['keywords'];
    }

    $date_check_error = false;
    if (bg_not_null($dfrom)) {
      if (!bg_checkdate($dfrom, DOB_FORMAT_STRING, $dfrom_array)) {
        $error = true;
        $date_check_error = true;

        $messageStack->add_session('search', ERROR_INVALID_FROM_DATE);
      }
    }

    if (bg_not_null($dto)) {
      if (!bg_checkdate($dto, DOB_FORMAT_STRING, $dto_array)) {
        $error = true;
        $date_check_error = true;

        $messageStack->add_session('search', ERROR_INVALID_TO_DATE);
      }
    }

    if (($date_check_error == false) && bg_not_null($dfrom) && bg_not_null($dto)) {
      if (mktime(0, 0, 0, $dfrom_array[1], $dfrom_array[2], $dfrom_array[0]) > mktime(0, 0, 0, $dto_array[1], $dto_array[2], $dto_array[0])) {
        $error = true;

        $messageStack->add_session('search', ERROR_TO_DATE_LESS_THAN_FROM_DATE);
      }
    }

    $price_check_error = false;
    if (bg_not_null($pfrom)) {
      if (!settype($pfrom, 'double')) {
        $error = true;
        $price_check_error = true;

        $messageStack->add_session('search', ERROR_PRICE_FROM_MUST_BE_NUM);
      }
    }

    if (bg_not_null($pto)) {
      if (!settype($pto, 'double')) {
        $error = true;
        $price_check_error = true;

        $messageStack->add_session('search', ERROR_PRICE_TO_MUST_BE_NUM);
      }
    }

    if (($price_check_error == false) && is_float($pfrom) && is_float($pto)) {
      if ($pfrom >= $pto) {
        $error = true;

        $messageStack->add_session('search', ERROR_PRICE_TO_LESS_THAN_PRICE_FROM);
      }
    }

    if (bg_not_null($keywords)) {
    if($_GET['search_entire_phrase']=='1')
    {
    $search_keywords[] = $keywords;
    }
    else
    {
      if (!bg_parse_search_string($keywords, $search_keywords)) {
        $error = true;

        $messageStack->add_session('search', ERROR_INVALID_KEYWORDS);
      }
     }
    }
  }

  if (empty($dfrom) && empty($dto) && empty($pfrom) && empty($pto) && empty($keywords)) {
    $error = true;

    $messageStack->add_session('search', ERROR_AT_LEAST_ONE_INPUT);
  }

  if ($error == true) {
    bg_redirect(bg_href_link(SYSTEM_FOLDER . DIR_WS_APPLICATIONS . FILENAME_ADVANCED_SEARCH, bg_get_all_get_params(), 'NONSSL', true, false));
  }

// Search enhancement mod start
  $search_enhancements_keywords = $_GET['keywords'];
  $search_enhancements_keywords = strip_tags($search_enhancements_keywords);
  $search_enhancements_keywords = addslashes($search_enhancements_keywords);

  if ($search_enhancements_keywords != $last_search_insert) {
    bg_db_query("insert into search_queries (search_text) values ('" . $search_enhancements_keywords . "')");
    bg_session_register('last_search_insert');
    $last_search_insert = $search_enhancements_keywords;
  }
// Search enhancement mod end

  $breadcrumb->add(NAVBAR_TITLE_1, bg_href_link(SYSTEM_FOLDER . DIR_WS_APPLICATIONS . FILENAME_ADVANCED_SEARCH));
  $breadcrumb->add(NAVBAR_TITLE_2, bg_href_link(SYSTEM_FOLDER . DIR_WS_APPLICATIONS . FILENAME_ADVANCED_SEARCH_RESULT, bg_get_all_get_params(), 'NONSSL', true, false));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<?php

if ( file_exists(SYSTEM_FOLDER . DIR_WS_INCLUDES . 'header_tags.php') ) {
  require(SYSTEM_FOLDER . DIR_WS_INCLUDES . 'header_tags.php');
} else {
?> 
  <title><?php echo TITLE; ?></title>
<?php
}

?>
<link rel="stylesheet" type="text/css" href=<?php echo '"'. STYLE_SHEET . '"' ?>>



</head>
<body class="wish">


<!-- header //-->
<?php require(SYSTEM_FOLDER . DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->




<?
if($_GET['keywords'] != ""){
$q = $_GET['keywords'];
} else {
$q = $_POST['keywords'];
}

$q = urlencode($q);
$host = urlencode($_SERVER['HTTP_HOST']);
$file = urlencode($_SERVER['PHP_SELF']);
$ip = urlencode($_SERVER['REMOTE_ADDR']);
$useragent = urlencode($_SERVER['HTTP_USER_AGENT']);
$pto = (int)$_GET['pto'];
$pfrom = (int)$_GET['pfrom'];
$eqOptions = array();
if(isset($_GET['categories_id']) && $_GET['categories_id'] != '') {
    $eqOptions['categories_id']  = $_GET['categories_id'];
}
if(isset($_GET['manufacturers_id']) && $_GET['manufacturers_id'] != '') {
    $eqOptions['manufacturers_id']  = $_GET['manufacturers_id'];
}
$url = '
EOL
.API_URL.
<<<'EOL'
/search/?q='.$q.'&host='.$host.'&ip='.$ip.'&useragent='.$useragent.'&file='.$file.'&pfrom='.$pfrom.'&pto='.$pto.
'&getvar='.urlencode(json_encode($_GET)).'&postvar='.urlencode(json_encode($_POST));
$content = file_get_contents($url);
//$content = utf8_decode(utf8_decode($content));

if($content != null){
   echo $content;
} else { 
?>





<!-- body //-->
<table border="0" width="100%" cellspacing="<?php echo $bodytable_cellspacing; //set in add_application_top.php ?>" cellpadding="<?php echo $bodytable_cellpadding; //set in add_application_top.php ?>">
  <tr>
  <?php if (BOX_WIDTH_LEFT > 0){ ?>
    <td class="columnLeft" width="<?php echo BOX_WIDTH_LEFT; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH_LEFT; ?>" cellspacing="0" cellpadding="2">
<!-- left_navigation //-->
<?php require(SYSTEM_FOLDER . DIR_WS_INCLUDES . 'column_left.php');?>
<!-- left_navigation_eof //-->
    </table></td>
  <?php } ?>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE_2; ?></td>
            <?php if (CATEGORY_IMAGES_ON == 'true') { ?>
            <td class="pageHeading" align="right"><?php echo bg_image(DIR_WS_IMAGES . 'table_background_browse.gif', HEADING_TITLE_2, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
            <?php } ?>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo bg_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td>
<?php
// create column list
  $define_list = array('PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
                       'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
                       'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
                       'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
                       'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
                       'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
                       'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE,
                       'PRODUCT_LIST_BUY_NOW' => PRODUCT_LIST_BUY_NOW);

  asort($define_list);

  $column_list = array();
  reset($define_list);
  while (list($key, $value) = each($define_list)) {
    if ($value > 0) $column_list[] = $key;
  }
  $select_column_list = '';

  for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
    switch ($column_list[$i]) {
      case 'PRODUCT_LIST_MODEL':
        $select_column_list .= 'p.products_model, ';
        break;
      case 'PRODUCT_LIST_MANUFACTURER':
        $select_column_list .= 'm.manufacturers_name, ';
        break;
      case 'PRODUCT_LIST_QUANTITY':
        $select_column_list .= 'p.products_quantity, ';
        break;
      case 'PRODUCT_LIST_IMAGE':
        $select_column_list .= 'p.products_image, ';
        break;
      case 'PRODUCT_LIST_WEIGHT':
        $select_column_list .= 'p.products_weight, ';
        break;
    }
  }

      $select_str = "select distinct " . $select_column_list . " m.manufacturers_id, p.products_id, pd.products_name, p.products_price, p.products_qty_blocks, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price ";

  if ( (DISPLAY_PRICE_WITH_TAX == 'true') && (bg_not_null($pfrom) || bg_not_null($pto)) ) {
    $select_str .= ", SUM(tr.tax_rate) as tax_rate ";
  }

    if(SEPARATE_PRICING_PER_CUSTOMER != 'true'){
            $groupSql = '';
    }else{
        if(!bg_session_is_registered('sppc_customer_group_id')) { 
            $groupSql = ' AND s.customers_group_id=0';  
        }else{
            $groupSql = ' AND s.customers_group_id='.(int)$sppc_customer_group_id;
        }
    }
  
  //coded by Deepthy
  if(SELECT_SHOP == 'Multi')
  {
    $from_str = "from " . TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m using(manufacturers_id) left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id $groupSql left join " . TABLE_SPECIALS_TO_STORES . " s2s on s2s.specials_id = s.specials_id and s2s.stores_id = '" . STORES_ID . "'," . TABLE_PRODUCTS_DESCRIPTION . " pd,  " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c INNER JOIN " . TABLE_PRODUCTS_TO_STORES . " p2s ON p2c.products_id = p2s.products_id INNER JOIN  " . TABLE_CATEGORIES_TO_STORES . " c2s ON p2c.categories_id=c2s.categories_id ";
  }
  else
  {
      $from_str = "from " . TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m using(manufacturers_id) left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id $groupSql," . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c";
  } //end of code

  if ( (DISPLAY_PRICE_WITH_TAX == 'true') && (bg_not_null($pfrom) || bg_not_null($pto)) ) {
    if (!bg_session_is_registered('customer_country_id')) {
      $customer_country_id = STORE_COUNTRY;
      $customer_zone_id = STORE_ZONE;
    }
    $from_str .= "," . TABLE_PRODUCTS . " p2 left join " . TABLE_TAX_RATES . " tr on p2.products_tax_class_id = tr.tax_class_id left join " . TABLE_ZONES_TO_GEO_ZONES . " gz on tr.tax_zone_id = gz.geo_zone_id and (gz.zone_country_id is null or gz.zone_country_id = '0' or gz.zone_country_id = '" . (int)$customer_country_id . "') and (gz.zone_id is null or gz.zone_id = '0' or gz.zone_id = '" . (int)$customer_zone_id . "')";
  
        $p2Cond = 'p.products_id=p2.products_id AND'; 
  }else{
        $p2Cond = ''; 
  }

    //coded by Deepthy
    if(SELECT_SHOP == 'Multi')
    {
        $where_str = " where $p2Cond p2s.stores_id = '" . STORES_ID . "' and c.categories_status = '1' and p.products_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and c2s.stores_id='" . STORES_ID . "' ";
    }
    else
    {
        $where_str = " where $p2Cond p.products_status = '1' and c.categories_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id ";
    } //end of code

  if (isset($_GET['categories_id']) && bg_not_null($_GET['categories_id'])) {
    if (isset($_GET['inc_subcat']) && ($_GET['inc_subcat'] == '1')) {
      $subcategories_array = array();
      bg_get_subcategories($subcategories_array, $_GET['categories_id']);

      $where_str .= " and p2c.products_id = p.products_id and p2c.products_id = pd.products_id and (p2c.categories_id = '" . (int)$_GET['categories_id'] . "'";

      for ($i=0, $n=sizeof($subcategories_array); $i<$n; $i++ ) {
        $where_str .= " or p2c.categories_id = '" . (int)$subcategories_array[$i] . "'";
      }

      $where_str .= ")";
    } else {
      $where_str .= " and p2c.products_id = p.products_id and p2c.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$_GET['categories_id'] . "'";
    }
  }

  if (isset($_GET['manufacturers_id']) && bg_not_null($_GET['manufacturers_id'])) {
    $where_str .= " and m.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'";
  }

  if (isset($search_keywords) && (sizeof($search_keywords) > 0)) {
    $where_str .= " and (";
    for ($i=0, $n=sizeof($search_keywords); $i<$n; $i++ ) {
      switch ($search_keywords[$i]) {
        case '(':
        case ')':
        case 'and':
        case 'or':
          $where_str .= " " . $search_keywords[$i] . " ";
          break;
        default:
          $keyword = bg_db_prepare_input($search_keywords[$i]);
          $where_str .= "(pd.products_name like '%" . bg_db_input($keyword) . "%' or p.products_model like '%" . bg_db_input($keyword) . "%' or m.manufacturers_name like '%" . bg_db_input($keyword) . "%'";
          if (isset($_GET['search_in_description']) && ($_GET['search_in_description'] == '1')) $where_str .= " or pd.products_description like '%" . bg_db_input($keyword) . "%'";
          $where_str .= ')';
          break;
      }
    }
    $where_str .= " )";
  }

  if (bg_not_null($dfrom)) {
    $where_str .= " and p.products_date_added >= '" . bg_date_raw($dfrom) . "'";
  }

  if (bg_not_null($dto)) {
    $where_str .= " and p.products_date_added <= '" . bg_date_raw($dto) . "'";
  }

  if (bg_not_null($pfrom)) {
    if ($currencies->is_set($currency)) {
      $rate = $currencies->get_value($currency);

      $pfrom = $pfrom / $rate;
    }
  }

  if (bg_not_null($pto)) {
    if (isset($rate)) {
      $pto = $pto / $rate;
    }
  }
    //coded by Deepthy
    if(SELECT_SHOP == 'Multi')
    {
      if (DISPLAY_PRICE_WITH_TAX == 'true') {
        if ($pfrom > 0) $where_str .= " and (IF(s.status = '1' AND s2s.stores_id = '" . STORES_ID . "', s.specials_new_products_price, p.products_price) * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100) ) >= " . (double)$pfrom . ")";
        if ($pto > 0) $where_str .= " and (IF(s.status = '1' AND s2s.stores_id = '" . STORES_ID . "', s.specials_new_products_price, p.products_price) * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100) ) <= " . (double)$pto . ")";
      } else {
        if ($pfrom > 0) $where_str .= " and (IF(s.status = '1' AND s2s.stores_id = '" . STORES_ID . "', s.specials_new_products_price, p.products_price) >= " . (double)$pfrom . ")";
        if ($pto > 0) $where_str .= " and (IF(s.status = '1' AND s2s.stores_id = '" . STORES_ID . "', s.specials_new_products_price, p.products_price) <= " . (double)$pto . ")";
      }
    }
    else
    {
      if (DISPLAY_PRICE_WITH_TAX == 'true') {
        if ($pfrom > 0) $where_str .= " and (IF(s.status, s.specials_new_products_price, p.products_price) * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100) ) >= " . (double)$pfrom . ")";
        if ($pto > 0) $where_str .= " and (IF(s.status, s.specials_new_products_price, p.products_price) * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100) ) <= " . (double)$pto . ")";
      } else {
        if ($pfrom > 0) $where_str .= " and (IF(s.status, s.specials_new_products_price, p.products_price) >= " . (double)$pfrom . ")";
        if ($pto > 0) $where_str .= " and (IF(s.status, s.specials_new_products_price, p.products_price) <= " . (double)$pto . ")";
      }
    } //end of code

  if ( (DISPLAY_PRICE_WITH_TAX == 'true') && (bg_not_null($pfrom) || bg_not_null($pto)) ) {
    $where_str .= " group by p.products_id, tr.tax_priority";
  }

  if ( (!isset($_GET['sort'])) || (!ereg('[1-8][ad]', $_GET['sort'])) || (substr($_GET['sort'], 0, 1) > sizeof($column_list)) ) {
    for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
      if ($column_list[$i] == 'PRODUCT_LIST_NAME') {
        $_GET['sort'] = $i+1 . 'a';
        $order_str = ' order by pd.products_name';
        break;
      }
    }
  } else {
    $sort_col = substr($_GET['sort'], 0 , 1);
    $sort_order = substr($_GET['sort'], 1);
    $order_str = ' order by ';
    switch ($column_list[$sort_col-1]) {
      case 'PRODUCT_LIST_MODEL':
        $order_str .= "p.products_model " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
        break;
      case 'PRODUCT_LIST_NAME':
        $order_str .= "pd.products_name " . ($sort_order == 'd' ? "desc" : "");
        break;
      case 'PRODUCT_LIST_MANUFACTURER':
        $order_str .= "m.manufacturers_name " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
        break;
      case 'PRODUCT_LIST_QUANTITY':
        $order_str .= "p.products_quantity " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
        break;
      case 'PRODUCT_LIST_IMAGE':
        $order_str .= "pd.products_name";
        break;
      case 'PRODUCT_LIST_WEIGHT':
        $order_str .= "p.products_weight " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
        break;
      case 'PRODUCT_LIST_PRICE':
        $order_str .= "final_price " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
        break;
    }
  }

  $listing_sql = $select_str . $from_str . $where_str . $order_str;
  //if(PRODUCT_LISTING_MODE == 'box')   
                include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING); 
        //  elseif(PRODUCT_LISTING_MODE=='column') 
                //include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING_COL); 
?>
        </td>
      </tr>
      <tr>
        <td><?php echo bg_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo '<a href="' . bg_href_link(SYSTEM_FOLDER . DIR_WS_APPLICATIONS . FILENAME_ADVANCED_SEARCH, bg_get_all_get_params(array('sort', 'page')), 'NONSSL', true, false) . '">' . bg_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
<?php if (BOX_WIDTH_RIGHT > 0){ ?>
    <td class="columnRight" width="<?php echo BOX_WIDTH_RIGHT; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH_RIGHT; ?>" cellspacing="0" cellpadding="2">
<!-- right_navigation //-->
<?php require(SYSTEM_FOLDER . DIR_WS_INCLUDES . 'column_right.php');?>
<!-- right_navigation_eof //-->
    </table></td>
<?php } ?>
  </tr>
</table>
<!-- body_eof //-->


<? } ?>




<!-- footer //-->
<?php require(SYSTEM_FOLDER . DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(SYSTEM_FOLDER . DIR_WS_INCLUDES . 'application_bottom.php'); 
?>
EOL;
?>