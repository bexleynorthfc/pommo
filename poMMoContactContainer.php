<?php

require_once(PACKAGE_DIRECTORY."Common/ObjectContainer.php");
require_once(PACKAGE_DIRECTORY."Common/Parameterized_Object.php");

class poMMoContactContainer extends ObjectContainer{

	function poMMoContactContainer(){
		$this->DB_Object();
		$this->setDSN(DSN);

        global $bmdb,$poMMo;
		$Bootstrap = Bootstrap::getBootstrap();
		$potential_packages = array('p','export_package','import_package');
		foreach ($potential_packages as $potential_package){
			if ($_GET[$potential_package] != ""){
				$package_name = $_GET[$potential_package];
				break;
			}
		}
		$_poMMo_package = $Bootstrap->usePackage($package_name);
		define('_IS_VALID',true);
        require_once(dirname(__FILE__).'/bootstrap.php');
		require_once('inc/db_subscribers.php');
		require_once('inc/db_fields.php');
		require_once('inc/db_groups.php');
		require_once('inc/db_sqlgen.php');
		
        $this->poMMo = & fireup('secure');
		$poMMo = $this->poMMo;
        $this->logger = & $this->poMMo->_logger;
        $this->dbo = & $this->poMMo->_dbo;
		$this->fields = dbGetFields($this->dbo);
		$this->setColumnName('poMMoContactEmail','email');
		$this->setColumnName('poMMoContactID','id');
		$this->setColumnName('poMMoContactDate','date');
		$this->setColumnName('poMMoContactModifiedTimestamp','lastModified');
		
		foreach ($this->fields as $field){
			$this->setColumnName('poMMoContact'.$field['name'],$field['name']);
		}
	}
	
	function addpoMMoContact(&$poMMoContact){
		$this->setTimestamp($poMMoContact);
		return $this->addObject($poMMoContact,true);
	}
	
	function updatepoMMoContact($poMMoContact){
		$this->setTimestamp($poMMoContact);
		return $this->updateObject($poMMoContact);
	}
	
	function setTimestamp(&$poMMoContact){
		$poMMoContact->setParameter('poMMoContactLastModified',date("Y-m-d H:i:s"));
	}
	
	function getAllpoMMoContacts($table = 'subscribers',$group_id = ""){
		if ($group_id == ""){
			$Objects = dbGetSubscriber($this->dbo,'all');
		}
		else{
			$ids = dbGetGroupSubscribers($this->dbo,$table,$group_id);
			$Objects = dbGetSubscriber($this->dbo,$ids);			
		}
		if ($Objects){
            return $this->manufacturepoMMoContact($Objects);
		}
		else{
			return null;
		}
	}
	
    function manufacturepoMMoContact($Object){
            if (!is_array($Object)){
                    $_Objects = array($Object);
            }
            else{
                    $_Objects = $Object;
            }
            
            $poMMoContacts = array();
            foreach ($_Objects as $_Object){
                    $_tmp = new Parameterized_Object();
					if (is_a($_Object,'Parameterized_Object')){
						$poMMoContacts[$_Object->getParameter('poMMoContactID')] = $_Object;
					}
					else{
						$_tmp->setParameter('poMMoContactID',$_Object['id']);
						$_tmp->setParameter('poMMoContactEmail',$_Object['email']);
						$_tmp->setParameter('poMMoContactDate',$_Object['date']);
						foreach ($this->fields as $field_id => $field){
							$_tmp->setParameter('poMMoContact'.$field['name'],$_Object['data'][$field_id]);
						}
	                    //$_tmp->saveParameters();
	                    $poMMoContacts[$_tmp->getParameter('poMMoContactID')] = $_tmp;
					}
            }

            if (!is_array($Object)){
                    return array_shift($poMMoContacts);
            }
            else{
                    return $poMMoContacts;
            }
    }	

	function manufacturepoMMoSubscriber($Object){
		$return = array();
		$return['id'] = $Object->getParameter('poMMoContactID');
		$return['email'] = $Object->getParameter('poMMoContactEmail');
		$return['date'] = $Object->getParameter('poMMoContactDate');
		if ($return['date'] == "" or $return['date'] = "0000-00-00"){
			$return['date'] = date("Y-m-d");
		}
		$return['data'] = array();
		foreach ($this->fields as $field_id => $field){
			if (array_key_exists('poMMoContact'.$field['name'],$Object->params)){
				$return['data'][$field_id] = $Object->getParameter('poMMoContact'.$field['name']);
			}
		}
		return $return;
	}
    
	function getpoMMoContact($poMMoContact_id){
		$Object = dbGetSubscriber($this->dbo,$poMMoContact_id);
		
		if ($Object){
            return $this->manufacturepoMMoContact($Object);
		}
		else{
			return null;
		}
	}
	
	function getAllObjects($wc,$sort_field = "",$sort_dir = ""){
		$input = $this->decodeWhereClause($wc);
		$lookup = ($input['id'] != "" ? 'id' : 'email');
		if ($lookup == 'email' and $input['email'] == bm_BlankEmail){
			// If the email address is blank, then looking up and filtering ALL blank email addresses is 
			// very time consuming.  Let's tackle it a different way.  
			$subscribers = dbGetSubscriberWithData($this->dbo,array('First Name' => $input['First Name'], 'Last Name' => $input['Last Name']));
		}
		else{
			$subscribers = dbGetSubscriber($this->dbo,$input[$lookup]);
		}		
		$return = $this->manufacturepoMMoContact($subscribers);
		
		return $return;
	}
	
	function addObject(& $Object){
		$subscriber = $this->manufacturepoMMoSubscriber($Object);
		if ($subscriber['id'] != ""){
			dbSubscriberUpdate($this->dbo,$subscriber);
		}
		else{			
			$id = dbSubscriberAdd($this->dbo,$subscriber);
			$Object->setParameter('poMMoContactID',$id);
		}
	}
	
	function updateObject($Object){
		$subscriber = $this->manufacturepoMMoSubscriber($Object);
		dbSubscriberUpdate($this->dbo,$subscriber);
	}
	
	function deleteObject($wc){
		if (DB::isError($dbh = $this->connectDB())) return PEAR::raiseError("Error:  ".$dbh->getMessage());

		$query = "SELECT subscribers_id FROM ".$this->dbo->table['subscribers'];
		$where_clause = "";
		$where_parms = array();
		if (is_a($wc,'whereClause') and count($wc->getConditions())){
			$where_clause = " WHERE ".$wc->getSafeString();
			$where_parms = $wc->getValues();
		}		
		
		$query.=$where_clause;

		$sth = $dbh->prepare($query);
                
		//echo $dbh->executeEmulateQuery($sth,$where_parms);
		
		$result = $dbh->execute($sth,$where_parms);
		$numRows = $result->numRows();

		$subscriber_ids = array();
		
		for ($i = 0; $i < $numRows; $i++){
			$arr = array();
			$result->fetchInto($arr,DB_FETCHMODE_ASSOC,$i);
			$subscriber_ids[] = $arr['subscribers_id'];
		}		
		if (count($subscriber_ids)){
			dbSubscriberRemove($this->dbo,$subscriber_ids);
		}
	}
	        
	function deletepoMMoContact($poMMoContactID){
		return dbSubscriberRemove($this->dbo,$poMMoContactID);
	}
	
	function countAllObjects($whereClause){
		$input = $this->decodeWhereClause($whereClause);
		$count = dbGetGroupSubscribers($this->dbo,$input['table'],$input['group'],'count',NULL,ASC,NULL,NULL,$input['search']);
		return $count;
	}
	
	function getLimitedObjects($wc,$limit,$offset,$sort_parms,$sort_dir){
		$input = $this->decodeWhereClause($wc);
		$sort = current($sort_parms);
		if ($sort != ""){
			foreach ($this->fields as $field_id => $field){
				if ($field['name'] == str_replace('poMMoContact','',$sort)){
					$sort = $field_id;
					break;
				}
			}
		}
		if (!is_numeric($sort)){
			unset($sort);
		}
		$ids = dbGetGroupSubscribers($this->dbo, $input['table'], $input['group'], 'list', $sort, current($sort_dir), $limit, $offset,$input['search']);
		$Objects = dbGetSubscriber($this->dbo,$ids);
		if ($Objects){
            return $this->manufacturepoMMoContact($Objects);
		}
		else{
			return null;
		}
	}
	
	function decodeWhereClause($wc){
		if (!count($wc->_whereCondition)){
			return array('group' => 'all', 'table' => 'subscribers','search' => '');
		}
		else{
			$groups = dbGetGroups($this->dbo);
			$return = array();
			// First parm is group
			foreach ($wc->_whereCondition as $key => $condition){
				$condition = preg_replace("/ *[\<\>\=] *\?/",'',$condition);
				switch($condition){
				case 'Group':
					switch($wc->_searchVariableList[$key]){
					case 'All Subscribers':
					case '':
						$return['group'] = 'all';
						break;
					default:
						foreach ($groups as $group_id => $group_name){
							if ($group_name == $wc->_searchVariableList[$key]){
								$return['group'] = $group_id;
							}
						}
						break;
					}
					break;
				case 'Table':
					switch($wc->_searchVariableList[$key]){
					case '':
						$return['table'] = 'subscribers';
						break;
					default:
						$return['table'] = $wc->_searchVariableList[$key];
						break;
					}
					break;
				case 'searchText':
					switch($wc->_searchVariableList[$key]){
					case '':
						break;
					default:
						$return['search'] = $wc->_searchVariableList[$key];
						break;
					}
					break;
				case 'email':
					switch($wc->_searchVariableList[$key]){
					case '':
					case bm_BlankEmail:
						$return['email'] = bm_BlankEmail;
						break;
					default:
						$return['email'] = $wc->_searchVariableList[$key];
						break;
					}
					break;
				case 'First Name':
				case 'Last Name':
				case 'id':
					$return[$condition] = $wc->_searchVariableList[$key];
					break;
				}
			}
			return $return;
		}
	}
	
	
}

?>