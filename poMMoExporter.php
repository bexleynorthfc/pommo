<?php

require_once(PACKAGE_DIRECTORY."ImportExport/Exporter.php");

class poMMoExporter extends Exporter{
    
    function poMMoExporter(){
		$this->Exporter();
		$this->default_encoding = 'macintosh';
		$this->default_delimiter = 'comma';
		
		$Bootstrap = Bootstrap::getBootstrap();
		$Package = $Bootstrap->usePackage('poMMo');
        
		$this->parameterPrefix = 'poMMoContact';
        $this->setContainer('poMMoContactContainer');
        
        $this->ignoreParameter(array());
		$this->sort_parms = array('poMMoContactLast Name','poMMoContactFirst Name');
		$this->sort_dir = array('asc');
		
		$filter_parms = array('Group' => array('all' => 'All Subscribers'),'Table' => array('subscribers','pending'));
		if ($_GET['searchText'] != ""){
			$filter_parms['searchText'] = array(urldecode($_GET['searchText']));
		}
		$tmp = new poMMoContactContainer(); // used to get the groups functions in
		$groups = dbGetGroups($tmp->dbo);
		if (is_array($groups)){
			foreach ($groups as $group_id => $group_name){
				$filter_parms['Group'][$group_id] = $group_name;
			}
		}
		$this->addCustomFilterParameter($filter_parms);
    }
    
    function massageData(& $Object){
   	}

	function postExport(& $poMMoTerm){
	}
    
}