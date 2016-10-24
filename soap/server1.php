<?php 
    require_once '../../config.php';
    require_once CLASS_FOLDER.'/autoload.php';
     
    Autoload::init();
  


class VublaSoapServer {
    private $username;
    private $user;
    private $wid = null;
    private $cid = null;
    
    
    //SETTINGS:
    function  getSetting( $name) {
        $this->_checkLogin(); 
        $this->_verifyPrivileges('settings');
        $result = new stdClass();
        $result = settings::get($name, $this->wid);
        return $result;
    } 
    
    function setSetting ($name, $value){
       $this->_checkLogin(); 
       $this->_verifyPrivileges('settings');
       if(settings::setLocal($name, $value, $this->wid)){
           $result = new stdClass();
           $result = true;
           return $result;
       } else {
           throw new SoapFault("VublaSoapServer", "Saving setting failed!");
       }
    }
    
    //STATISTICS
    /**
     * type:    hitp, hitpercentage(hit percentage in time intarval), 
     *          hit, hits(hits in time intarval), 
     *          searches(number of searchers in time interval),
     *          missp, misspercentage(miss percentage in time intarval), 
     *          miss, misses(miss' in time intarval)
     * 
     * tStart:  start timestamp
     * 
     * tEnd:    end timestamp
     */
    function getStatistics($options = array()){
        $this->_checkLogin(); 
        $this->_verifyPrivileges('statistics');
        $wid = $this->wid;
        
        if(!isset($options['tStart'])) $tStart = $this->default_tStart();
        else                           $tStart = $options['tStart'];
        if(!isset($options['tEnd']))   $tEnd = $this->default_tEnd();
        else                           $tEnd = $options['tEnd'];
        if(!isset($options['type']))   throw new SoapFault("VublaSoapServer", "'type' parameter not set for getStatistics");
        else                           $type = $options['type'];;
        //if(!isset($max)) $max = $this->default_max();
        
        $stat = new WebshopStatisticsProvider($wid);
        switch ($type) {
            case 'hitp':
            case 'hitpercentage':
                return $stat->getSearchHits($tStart,$tEnd)*100/$stat->getNumberOfSearches($tStart,$tEnd);
                break;
            case 'hits':
            case 'hit':
                return $stat->getSearchHits($tStart,$tEnd);
                break;
            case 'searches':
                return $stat->getNumberOfSearches($tStart,$tEnd);
                break;
            case 'missp':
            case 'misspercentage':
                return $stat->getSearchMisses($tStart,$tEnd)*100/$stat->getNumberOfSearches($tStart,$tEnd);
                break;
            case 'miss':
            case 'misses':
                return $stat->getSearchMisses($tStart,$tEnd);
                break;
            default :    
               throw new SoapFault("VublaSoapServer", "Unknown 'type' parameter in  ".__FUNCTION__.": " . $type);
          
        }
        
    }

    /**
     * type:        log, searchlog(get log from within time interval, use fields to project)
	 * 				//count (counts the number entries - nullifies fields)                       <-- Not implemented yet
     * 
     * tStart:      start timestamp
     * 
     * tEnd:        end timestamp
     * 
     * max:         maximum number of log entries
     * 
     * startRow:    the first row to be shown
     * 
     * fields:      projection of the results
     */
    function getLog($options = array()) {
        $this->_checkLogin(); 
        $this->_verifyPrivileges('statistics');
        $type = $options['type'];
        $wid = $this->wid;
        $tStart = $options['tStart'];
        $tEnd = $options['tEnd'];
        $max = $options['max'];
        $startRow = $options['startRow'];
        $fields = $options['fields'];
        
        if(!isset($type)) $type = 'log';
        if(!isset($tStart)) $tStart = $this->default_tStart();
        if(!isset($tEnd)) $tEnd = $this->default_tEnd();
        if(!isset($fields)) $fields = $this->default_fields();
        if(!isset($max)) $max = $this->default_max();
        if(!isset($startRow)) $startRow = $this->default_startRow();
        
        $stat = new WebshopStatisticsProvider($wid);
        switch ($type) {
            case 'searchlog':
            case 'log':
                $result = $stat->getSearchLog($tStart, $tEnd, $max, $startRow, $fields);
                break;
            default :    
               throw new SoapFault("VublaSoapServer", "Unknown 'type' parameter in  ".__FUNCTION__.": " . $type);
          
        }
        
        foreach ($result as $entry) {
            if(isset($entry->prodids)) {
                $entry->prodids = explode(',', $entry->prodids);
            }
            if(isset($entry->prodnames)) {
                $entry->prodnames = explode(',', $entry->prodnames);
            }
            if(isset($entry->words)) {
                $entry->words = explode(',', $entry->words);
            }
        }
        $toReturn = new stdClass();
        $toReturn->Result =$result; 
        return $toReturn;
    }

    /**
     * type:    hits, hit(get the words with the most searches, with hits, use max to limit)
     *          miss, misses(get the search query with the most searches, with no hits, use max to limit)
     * 
     * tStart:      start timestamp
     * 
     * tEnd:        end timestamp
     * 
     * max:         maximum number of log entries
     * 
     * startRow:    the first row to be shown
     */
    function getHotSearches($options = array()) {
        $this->_checkLogin(); 
        $this->_verifyPrivileges('statistics');
        $type = $options['type'];
        $wid = $this->wid;
        $tStart = $options['tStart'];
        $tEnd = $options['tEnd'];
        $max = $options['max'];
        $startRow = $options['startRow'];
        
        if(!isset($tStart)) $tStart = $this->default_tStart();
        if(!isset($tEnd)) $tEnd = $this->default_tEnd();
        if(!isset($max)) $max = $this->default_max();
        if(!isset($startRow)) $startRow = $this->default_startRow();
        
        $result = new stdClass();
        $stat = new WebshopStatisticsProvider($wid);
        switch ($type) {
            case 'hits':
            case 'hit':
                $result->Result = $stat->getSearchWords($max, $tStart, $tEnd, $startRow);
                break;
            case 'miss':
            case 'misses':
                $result->Result = $stat->getNotFoundSearches($max, $tStart, $tEnd, $startRow);
                break;
            default :    
               throw new SoapFault("VublaSoapServer", "Unknown 'type' parameter: " . $type);
          
        }
        
        foreach ($result->Result as $res) {
            $res->count = (int)$res->count;
        }
        return $result;
    }

    //SEARCH
    /**
     * Allowed indices in data variable
     * ip
     * q
     * useragent
     * pfrom
     * min_price
     * pto
     * max_price
     * nolog
     */
    function search($data) {
      
        $this->_checkLogin(); 
        $this->_verifyPrivileges('search');
        $this->_ensureParameterSet($data,array('q','ip','useragent'),__FUNCTION__);
        /*#############################
        #GET USER DATA & OPEN WEBSHOP
        #############################*/
        
        $q = $data['q'];
        $out = new stdClass();
        $out->Result = new stdClass();
        $out->Result->ids = array();
        $out->Result->alternatives = array();
        $out->Result->keywords = array();
        
        ##########################################
        # MIN AND MAX PRICE
        ##########################################
        $min_price = null;
        if(isset($data['min_price'])){
            $min_price = (int)$data['min_price'];
        }
        if(isset($data['pfrom'])){
            $min_price = (int)$data['pfrom'];
        }
        
        $max_price = null;
        if(isset($data['pto'])){
            $max_price = (int)$data['pto'];
        }
         if(isset($data['max_price'])){
            $max_price = (int)$data['max_price'];
        }
        if($max_price == 0){
                $max_price = null;
        }
        /**
         * min and max is set to null if none is given. 
         */
        ##########################################
        ##########################################
        
       
        ###################################################
        # Load varius settings
        ################################################### 
        if(!isset($data['ip']))
        {
            throw new SoapFault("VublaSoapServer", "'ip' parameter not set in ".__FUNCTION__);
        }
        @define('IP',$data['ip']);
        @define('USERAGENT', $data['useragent']);
    
        $meta = VPDO::getVdo(DB_METADATA);

        $wid = $this->wid;

        $sql2 = "Select hostname from webshops where id = ? limit 1";
        $host = $real_host = $meta->fetchOne($sql2,array($wid) );
        $active_encode = true;//(int)Settings::get('active_encode', $wid);
        $from_encoding = Settings::get('encode_from', $wid);//"ISO-8859-1"; // This should be from settings.
        $vubla_encoding = Settings::getGlobal('vubla_encoding');
        if($active_encode && !isset($data['enable']) && isset($from_encoding) && $from_encoding != $vubla_encoding){
           $q = iconv($from_encoding, $vubla_encoding, $q);
        }
    
        define('WID', $wid);
    
        $meta = null;
    
        $pdo = VPDO::getVdo(DB_PREFIX . WID);
        ##############################
        ##############################
        
        
        ##############################
        # OPTIONS
        ##############################
        
        $optionsArray = null;

   $maxOptionsArray = array();
   $minOptionsArray = array();
   $eqOptionsArray = array();
    if(isset($data['max_options'])){ 
        $maxOptionsArray = $data['max_options'];
        
        if(!is_array($maxOptionsArray)) {
            VublaMailer::sendOnargiEmail('Maxoptions was not an array', 'It failed for host '. $host .' with wid '. $wid .'<br />' . $data['max_options'] .'<br/><pre>'.ob_get_contents().'</pre>');
            $maxOptionsArray = array();
        }
    } 
    
        #############################
    
    if(isset($data['min_options'])){ 
        $minOptionsArray = $data['min_options'];
        
        if(!is_array($minOptionsArray)) {
            VublaMailer::sendOnargiEmail('Minoptions was not an array', 'It failed for host '. $host .' with wid '. $wid .'<br />' . $data['min_options'] .'<br/><pre>'.ob_get_contents().'</pre>');
            $minOptionsArray = array();
        }
    } 
    if(isset($data['eq_options'])){ 
        $eqOptionsArray = $data['eq_options'];
        
        if(!is_array($eqOptionsArray)) {
            VublaMailer::sendOnargiEmail('Eqoptions was not an array', 'It failed for host '. $host .' with wid '. $wid .'<br />' . $data['eq_options'] .'<br/><pre>'.ob_get_contents().'</pre>');
            $eqOptionsArray = array();
        }
    } 
    if(isset($min_price)) {
        $minOptionsArray['lowest_price'] = $min_price;
    }
    
    
    if(isset($max_price)) {
        $maxOptionsArray['lowest_price'] = $max_price;
    }
    
    if(isset($data['sort_by'])) {
        $sortBy = $data['sort_by'];
    }
    
    if(isset($data['sort_order'])) {
        $sortOrder = $data['sort_order'];
    }
    //* */
    ##############################
    ##############################
    
     //echo 'hje'; exit;
    
    
    #############################
    #PERFORM SEARCH
    #############################

    $search = new Search($q, false);
    
    //$search->setCategories($categoriesArray);
    //var_dump($minOptionsArray);
    $search->setMaxOptions($maxOptionsArray);
    $search->setMinOptions($minOptionsArray);
    $search->setEqOptions($eqOptionsArray);
    if(isset($sortBy)){
        $search->setSortBy($sortBy);
    }
    if(isset($sortOrder)) {
        $search->setSortOrder($sortOrder);
    }
    
    $result = '';

    if($q){
        $result = $search->getResults();
    }
        
        if(is_null($result)){
            if($ob = ob_get_contents()){
                VublaLog::_n("There were no result from a search and the output buffer contains something \n");
                VublaLog::_n("<pre>".print_r($search->errors, true)."</pre> \n");
                VublaLog::_n("Getting ob flush from search(no result flush): \n" . nl2br($ob));
                VublaLog::saveAll('[Err] Error in search');
                ob_end_clean();
                
                throw new SoapFault('VublaSoapServer','No results were found');
            }
                
        }
        if(isset($result['products'])) {
            foreach ($result['products'] as $product) {
                if(isset($product->pid)) {
                    $out->Result->ids[] = (int)$product->pid;
                }
            }
            
            $out->Result->ids = array_unique($out->Result->ids);
        }
        if(isset($result['userdefinedkeywords'])) {
            foreach ($result['userdefinedkeywords'] as $keyword) {
                if(isset($keyword->url) && isset($keyword->text)) {
                    $temp = new stdClass();
                    $temp->url = $keyword->url;
                    $temp->text = $keyword->text;
                    $out->Result->keywords[] = $temp;
                }
            }
        }
        if(isset($result['alternatives'])) {
            foreach ($result['alternatives'] as $alternative) {
                if(isset($alternative)) {
                    $temp = new stdClass();
                    $temp->word = $alternative;
                    $out->Result->alternatives[] = $temp;
                }
            }
        }
        /*#############################
        #LOG AND TERMINATIION
        #############################*/
        if($search->errors()){
            VublaLog::_n("A search contained errors");
            VublaLog::_n("<pre>".print_r($search->errors(), true)."</pre> \n" . nl2br(ob_get_clean()));
            VublaLog::saveAll('[Err] Error in search');
            ob_end_clean();
            
            $searcherror = 'Search error: ' . implode('. ', $search->errors());
            throw new SoapFault('VublaSoapServer',$searcherror);
        }
    
    
    
        if($ob = ob_get_contents()){
          
            VublaLog::_n("Getting ob flush from search1: \n" . nl2br($ob));
            VublaLog::_n("<pre>".print_r($search->errors(), true)."</pre> \n");
            VublaLog::saveAll('[Err] Search outputed something weird');
            ob_end_clean();
            
            throw new SoapFault('VublaSoapServer','Data has been flushed prematurely');
        }
    
        if(is_null($out)){
            VublaLog::_n("Getting ob flush from search2: \n" . nl2br(print_r($ob),true));
            VublaLog::_n("<pre>".print_r($search->errors(), true)."</pre> \n");
            ob_end_clean();
            VublaLog::saveAll('[Err] Search, output missing');
            
            throw new SoapFault('VublaSoapServer','Output corrupted');
        }
        
        VublaLog::killGently();  // We write to the log at many points and want it to send on kills, but not this time.
        
        return $out;
        
    }

    private function _ensureParameterSet($array,$indices,$functionName = null)
    {
        if($functionName != null) {
            $functionText = ' in '.$functionName;
        } else {
            $functionText = '';
        }
        foreach ($indices as $index) {
            if(!isset($array[$index])) {
                throw new SoapFault("VublaSoapServer","'$index' parameter not set$functionText");
            }
        }
    }

    //USER ADMINISTRATION
    function createUser($data) {
        $this->_checkLogin(); 
        $this->_verifyPrivileges('createuser');
        $data['password2'] = $data['password'];
        $data['master'] = $this->cid;
        if(isset($data['subscription'])) {
            if(is_string($data['subscription'])) {
                foreach ($this->subscriptions as $key => $value) {
                    if($key == $data['subscription']) {
                        $data['subscription'] = Package::getId('Free Small');
                    }
                }
                if(is_string($data['subscription'])) {
                    throw new SoapFault('VublaSoapServer','Invalid subscription: ' .$data['subscription'] . ' Please use one of the following: '.implode(', ',array_keys($this->subscriptions)));
                    break;
                }
            }
        }
        
        
        $user = new User();
        $userRes = $user->create($data);
        if(empty($userRes)) {
            $cid = (int)$user->getCustomerFromSingleField('email',$data['email'])->id;
        } else {
            throw new SoapFault('VublaSoapServer','Unable to create user for following reason(s): ' . implode(', ', $userRes));
        }
        
        return $cid;
    }

    private $subscriptions = array('small' => 'Free Small','medium' => 'Free Medium','big' => 'Free Big');
    
    /**
     * cid
     * email
     * sendconfirm
     */
    function deleteUser($data) {
        $this->_checkLogin(); 
        $this->_verifyPrivileges('deleteuser');
        
        $user = new User();
        if(isset($data['cid'])) {
            $cid = (int)$data['cid'];
        } elseif(isset($data['email'])) {
            $cid = (int)$user->getCustomerFromSingleField('email',$data['email'])->id;
        }
        $masterId = $user->getMasterId($cid);
        
        if(isset($data['sendconfim'])) {
            $sendConfirm = $data['sendconfirm'];
        } else {
            $sendConfirm = false;
        }
        
        if($masterId == $this->cid) {
            $res = $user->delete($cid,$sendConfirm);
            if(!empty($res )) {
                throw new SoapFault('VublaSoapServer','An error occured while deleting user with id "' . $cid . '" with message(s): '.
                    implode(', ', $res));
            }
        } else {
            throw new SoapFault('VublaSoapServer','You are not allowed to delete this user');
        }
        
        return true;
    }
    
    /**
     * cid
     * email
     */
    function purgeUser($data) {
        $this->_checkLogin(); 
        $this->_verifyPrivileges('purgeuser');
        
        $user = new User();
        if(isset($data['cid'])) {
            $cid = (int)$data['cid'];
        } elseif(isset($data['email'])) {
            $cid = (int)$user->getDeletedCustomerFromSingleField('email',$data['email'])->id;
        }
        $masterId = $user->getMasterIdOfDeleted($cid);
        
        if($masterId == $this->cid) {
            $user->purge($cid);
        } else {
            throw new SoapFault('VublaSoapServer','You are not allowed to purge this user');
        }
        
        return true;
    }
    
    /**
     * cid
     * email
     */
    function recoverUser($data) {
        $this->_checkLogin(); 
        $this->_verifyPrivileges('recoveruser');
        
        $user = new User();
        if(isset($data['cid'])) {
            $cid = (int)$data['cid'];
        } elseif(isset($data['email'])) {
            $cid = (int)$user->getDeletedCustomerFromSingleField('email',$data['email'])->id;
        }
        $masterId = $user->getMasterIdOfDeleted($cid);
        
        if($masterId == $this->cid) {
            $user->recover($cid);
        } else {
            throw new SoapFault('VublaSoapServer','You are not allowed to recover this user');
        }
        
        return $cid;
    }
    

    //AUTHENTIFICATION
    function login ($username, $password){
        $user = User::checkLoginAPI(array('email'=>$username, 'password'=>$password)) ;
        if(is_object($user)){
            $this->user = $user;
            $this->cid = $user->id;
            $this->wid = User::getWid($this->cid); 
            $this->_verifyPrivileges('login');
			return $this->_getSoapType();
        } else {
            throw new SoapFault("VublaSoapServer", "Invalid username or password!");
        }
    }
    
    private function _checkLogin(){
        if(is_null($this->wid)){       
           throw new SoapFault("VublaSoapServer", "You need to login using the login() method.");
        } else {
            return true;         
        }
    }
    
    //TODO: This should be changed when we implement user groups and privs
    private function _verifyPrivilege($privilege) {
        
        $type = $this->_getSoapType();
        
        switch($privilege) {
            case 'statistics':
            case 'settings':
            case 'login':
            case 'search':
                if($type >= 1) {
                    break;
                } else {
                    throw new SoapFault("VublaSoapServer", "You do not have privileges($type) to use: " . $privilege);
                }
            case 'createuser': 
            case 'deleteuser':
            case 'purgeuser':
            case 'recoveruser':
                if($type >= 2) {
                    break;
                } else {
                    throw new SoapFault("VublaSoapServer", "You do not have privileges($type) to use: " . $privilege);
                }
            default:
                throw new SoapFault("VublaSoapServer", "The following privilege($type) is not supported: " . $privilege);
        }
        
        return true;
    }
        
    private function _verifyPrivileges($privileges) {
        if(!is_array($privileges)) {
            return $this->_verifyPrivilege($privileges);
        } else {
            foreach ($privileges as $privilege) {
                $this->_verifyPrivilege($privilege);
            }
        }
        
        return true;
    }
	
	private function _getSoapType()
	{
		$db = VPDO::getVdo(DB_METADATA);
        $q = 'SELECT soap_type FROM customers WHERE id = ' . $db->quote($this->cid);
        return (int)$db->fetchOne($q);
	}
    
    //DEFAULT VALUES
    private function default_tStart() {
        return 0;
    }
    
    private function default_tEnd() {
        return time();
    }
    
    private function default_fields() {
        return '*';
    }

    private function default_max() {
        return 10;
    }
    
    private function default_startRow(){
        return 0;
    }
}
ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache 
$server = new SoapServer('wsdl.php');

$server->setClass("VublaSoapServer");
$server->setPersistence(true);
$server->handle(); 
?>