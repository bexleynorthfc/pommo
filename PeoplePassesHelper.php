<?php
include_once(PACKAGE_DIRECTORY.'Common/DB_Object.php');
include_once(PACKAGE_DIRECTORY.'Common/Parameterized_Object.php');

class PeoplePassesHelper{
    
    function PeoplePassesHelper(){
        global $dbo,$poMMo,$logger,$bmdb;
        
        $Bootstrap = Bootstrap::getBootstrap();
        
        $this->package = $Bootstrap->usePackage('poMMo');
        $this->PassCategoryField = 'Festival Pass';
        
        // Okay, let's the get the correct DB information from poMMO
        define('_IS_VALID',true);
		$_poMMo_package = $this->package;
        include(dirname(__FILE__).'/../pommo/config.php');
        
        $this->bmdb = $bmdb;
        $this->dsn = "mysql://".$this->bmdb['username'].":".$this->bmdb['password']."@".$this->bmdb['hostname']."/".$this->bmdb['database'];
        
    }
    
    
    function getPassTypes(){
        // Get the current fields
        $query = "SELECT field_options FROM ".$this->bmdb['prefix']."subscriber_fields WHERE field_name = '".$this->PassCategoryField."'";
        $result = $this->runQuery($query);
        if(is_a($result, 'DB_Result')){
    	    $numRows = $result->numRows();
    	}

        $PassTypes = array();
    	for ($i = 0; $i < $numRows; $i++){
    		$arr = array();
    		$result->fetchInto($arr,DB_FETCHMODE_ASSOC,$i);
		
    		// this is a comma separated list of Categories.  Just in case there's a comma in one of the fields, 
    		// we'll do some special handling.  
    		$arr['field_options'] = preg_replace("/\\\,/","__COMMA__",$arr['field_options']);
    		$PassTypes = explode(",",$arr['field_options']);
    		foreach ($PassTypes as $key => $Category){
    		    $PassTypes[$key] = str_replace("__COMMA__",",",$Category);
    		}
    	}	
    	
    	asort($PassTypes);	
    	
    	return $PassTypes;
    }
    
    function getFieldID($poMMoField){
        
        // Step 1.  Get the Choice Fields
        $query = "SELECT field_id FROM ".$this->bmdb['prefix']."subscriber_fields WHERE field_name = ?";
        $parms = array($poMMoField);
        $result = $this->runQuery($query,$parms);
        if(is_a($result, 'DB_Result')){
    		$arr = array();
    		$result->fetchInto($arr,DB_FETCHMODE_ASSOC,0);
    		return $arr['field_id'];
    	}
    	else{
    	    return false;
    	}
    }
    
    function prepareForFestival($FestivalYear){
        
    }
    
    function getSubscriber($subscriber_id,$sortFunction = '__PeoplePassesHelper_sortByName'){
        global $bmdb;
        
		$_poMMo_package = $this->package;
        include_once(dirname(__FILE__).'/../pommo/bootstrap.php');
        $poMMo = & fireup('secure');
        $logger = & $poMMo->_logger;
        $dbo = & $poMMo->_dbo;
        
        include_once(dirname(__FILE__).'/../pommo/inc/db_subscribers.php');
        
        if (!is_array($subscriber_id)){
            $subscriber_id = array($subscriber_id);
        }
        if (count($subscriber_id)){
            if (count($subscriber_id) == 1){
                $subscriber_id = current($subscriber_id);
            }
            $Subscribers = dbGetSubscriber($dbo,$subscriber_id);
        }
        
        $People = array();
        if (is_array($Subscribers) and count($Subscribers)){
            $SubscriberFields = $this->getSubscriberFields();
            
            foreach ($Subscribers as $id => $Subscriber){
                $Person = new Parameterized_Object();
                $Person->setParameter('id',$id);
                $Person->setParameter('Email',$Subscriber['email']);
                foreach ($Subscriber['data'] as $key => $value){
                    if ($SubscriberFields[$key] != ""){
                        $Person->setParameter($SubscriberFields[$key],$value);
                    }
                }
                $People[$id] = $Person;
            }
        }
        
        uasort($People,$sortFunction);
        
        return $People;
    }
    
    function getPeopleOfType($Types,$sortFunction = '__PeoplePassesHelper_sortByCategory'){
        $query = "SELECT subscribers_id FROM ".$this->bmdb['prefix']."subscribers_data where field_id = ?";
        $parms = array($this->getFieldID($this->PassCategoryField));
        
        $sep = " and value in (";
        if (is_array($Types)){
            foreach ($Types as $Type){
                $query.=$sep."?";
                $sep = ",";
                $parms[] = $Type;
            }
            if (count($Types)){
                $query.=")";
            }
        }
        
        $result = $this->runQuery($query,$parms);
        if(is_a($result, 'DB_Result')){
    	    $numRows = $result->numRows();
    	}

        $SubscriberIDs = array();
    	for ($i = 0; $i < $numRows; $i++){
    		$arr = array();
    		$result->fetchInto($arr,DB_FETCHMODE_ASSOC,$i);
		
    		$SubscriberIDs[] = $arr['subscribers_id'];
    	}		
    	
    	if (count($SubscriberIDs)){
    	    return $this->getSubscriber($SubscriberIDs,$sortFunction);
    	}
    	else{
    	    return array();
    	}
    }
    
    function getPeopleWithPasses(){
        return $this->getPeopleOfType($this->getPassTypes(),'__PeoplePassesHelper_sortByName');
    }
    
    function getSubscriberFields(){
        // Get the current fields
        $query = "SELECT field_id,field_name FROM ".$this->bmdb['prefix']."subscriber_fields order by field_ordering asc";
        $result = $this->runQuery($query);
        if(is_a($result, 'DB_Result')){
    	    $numRows = $result->numRows();
    	}

        $SubscriberFields = array();
    	for ($i = 0; $i < $numRows; $i++){
    		$arr = array();
    		$result->fetchInto($arr,DB_FETCHMODE_ASSOC,$i);
		
		    if ($arr['field_name'] != 'Password'){
    		    $SubscriberFields[$arr['field_id']] = preg_replace("/[^A-Za-z0-9]/","",$arr['field_name']);
    		}
    	}		
    	
    	return $SubscriberFields;
    }
    
    
    
    function runQuery($query,$parms = array()){
        // Connect to the DB.  
        if (!$this->dbh){
            $this->dbh = DB::connect($this->dsn);
        }
        
    	$sth = $this->dbh->prepare($query);
    	
    	//  Debug Purposes
    	/*
    	$result = $this->dbh->executeEmulateQuery($sth,$parms);
    	if (DB::isError($result)){
    	    echo "<p>Error: (".$result->getMessage().") $query<br>Parms: ";
    	    var_dump($parms);
    	}
    	else{
            echo "<p>Query = ".$result;
        }
        */
        
    	$result = $this->dbh->execute($sth,$parms);
    	if (DB::isError($result)){
    	    var_dump($this->dbh->executeEmulateQuery($sth,$parms));
    	    die("Something went wrong there: ".$result->getMessage());
    	}
    	return $result;
    }
    
    
}

function __PeoplePassesHelper_sortByName($A,$B){
    $a = strtolower($A->getParameter('LastName'));
    $b = strtolower($B->getParameter('LastName'));
    if ($a == $b){
        return 0;
    }
    return ($a < $b ? -1 : 1);
}

if (!function_exists('__PeoplePassesHelper_sortByCategory')){
    function __PeoplePassesHelper_sortByCategory($A,$B){
        $a = strtolower($A->getParameter('FestivalPass'));
        $b = strtolower($B->getParameter('FestivalPass'));
        if ($a == $b){
            return __PeoplePassesHelper_sortByName($A,$B);
        }
        return ($a < $b ? -1 : 1);
    }
}



?>