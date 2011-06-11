<?php
/** [BEGIN HEADER] **
 * COPYRIGHT: (c) 2005 Brice Burgess / All Rights Reserved    
 * LICENSE: http://www.gnu.org/copyleft.html GNU/GPL 
 * AUTHOR: Brice Burgess <bhb@iceburg.net>
 * SOURCE: http://pommo.sourceforge.net/
 *
 *  :: RESTRICTIONS ::
 *  1. This header must accompany all portions of code contained within.
 *  2. You must notify the above author of modifications to contents within.
 * 
 ** [END HEADER]**/

/** 
 * Don't allow direct access to this file. Must be called from
elsewhere
*/
defined('_IS_VALID') or die('Move along...');

if (!class_exists('Smarty')){
	require_once(bm_baseDir.'/inc/smarty/Smarty.class.php');
}
if (!function_exists('T_')){
	require_once (bm_baseDir . '/inc/gettext/gettext.inc');
}		
// wrapper class around smarty
class bTemplate extends Smarty {
	
	function bTemplate() {
	
		// Class Constructor
		$this->Smarty();
		
        $this->register_function('apply_filters',array(&$this,'apply_filters'));
	}	
	
	// custom display function to fall back to "default" theme if template file not found
	// also assigns any poMMo errors or messages
	function display($resource_name, $cache_id = null, $compile_id = null, $display = false) {
		
		// attempt to load the theme's requested template
		if (!is_file($this->template_dir.'/'.$resource_name) and substr($resource_name,0,5) != 'file:')
			// template file not existant in theme, fallback to "default" theme
			if (!is_file($this->_themeDir.'default/'.$resource_name))
				// requested template file does not exist in "default" theme, die.
				die('<img src="'.bm_baseUrl.'themes/shared/images/icons/alert.png" align="middle">'.$resource_name.': '._T('Template file not found in default theme.'));
			else
				$resource_name = $this->_themeDir.'default/'.$resource_name;
		
		global $poMMo;
		if ($poMMo->_logger->isMsg()){ 
			$this->assign('messages',$poMMo->_logger->getMsg(false,false));
                }
		if ($poMMo->_logger->isErr())
			$this->assign('errors',$poMMo->_logger->getErr(false,false));
		
		return parent::display($resource_name, $cache_id = null, $compile_id = null, $display = false);
	}
        
	function myFetch($resource_name, $cache_id = null, $compile_id = null) {
		
		// attempt to load the theme's requested template
		if (!is_file($this->template_dir.'/'.$resource_name))
			// template file not existant in theme, fallback to "default" theme
			if (!is_file($this->_themeDir.'default/'.$resource_name))
				// requested template file does not exist in "default" theme, die.
				die('<img src="'.bm_baseUrl.'themes/shared/images/icons/alert.png" align="middle">'.$resource_name.': '._T('Template file not found in default theme.'));
			else
				$resource_name = $this->_themeDir.'default/'.$resource_name;
                                
		global $poMMo;
		if ($poMMo->_logger->isMsg()){ 
			$this->assign('messages',$poMMo->_logger->getMsg());
                }
		if ($poMMo->_logger->isErr())
			$this->assign('errors',$poMMo->_logger->getErr());
		
                return parent::fetch($resource_name, $cache_id = null, $compile_id = null);
        }
	
	function prepareForForm() {
		$this->plugins_dir[] = bm_baseDir.'/inc/smarty-plugins/validate';
		require(bm_baseDir.'/inc/class.smartyvalidate.php');
		
		// assign isForm to TRUE, used by header.tpl to include form CSS/Javascript in HTML HEAD
		$this->assign('isForm',TRUE);
		
		/*
		// strip out those bastard slashes
		if (get_magic_quotes_gpc()) {
			if (!empty($_POST))
				$_POST = bmStripper($_POST);
			if (!empty($_GET))
				$_GET = bmStripper($_GET);
		}
		*/
	}
	
	// Loads field data into template, as well as _POST (or a saved subscribeForm). 
	function prepareForSubscribeForm() {
		require_once (bm_baseDir . '/inc/db_fields.php');
		global $dbo;
		global $poMMo;
		
		// Get array of fields. Key is ID, value is an array of the demo's info
		$fields = dbGetFields($dbo,'active');
		
		if (function_exists('apply_filters')){
			global $poMMo_package;
			$fields = apply_filters($poMMo_package->package_name.'_subscribe_form_fields',$fields,array(&$this)); // deprecated.  Use next filter
			$fields = apply_filters('subscribe_form_fields',$fields,array(&$this));
		}
		if (!empty($fields))
			$this->assign('fields', $fields);
		
		// process.php appends serialized values to _GET['input']
		if ($poMMo->get('pommo_input')) {
			$this->assign(unserialize($poMMo->get('pommo_input')));
		}
		
		$this->assign($_POST);
	}
	
    function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false){
		if (function_exists('apply_filters')){
			// The most common use of this filter is to change the template directory (thereby overriding
			// the chosen template in one fell swoop).  To play nice, we'll save the template_directory
			// and then restore it at the end.
			$save_dir = $this->template_dir;
			if (has_filter("Smarty_Instance_resource_name_$resource_name")){
				$resource_name = apply_filters("Smarty_Instance_resource_name_$resource_name",$resource_name,array(&$this));
			}
			else{
				$resource_name = apply_filters('Smarty_Instance_resource_name',$resource_name,array(&$this));
			}
		}
		$return = parent::fetch($resource_name,$cache_id,$compile_id,$display);
		$this->template_dir = $save_dir;
		return $return;
	}
	
	function _smarty_include($params){
		if (function_exists('apply_filters')){
			$save_dir = $this->template_dir;
			$params = apply_filters('Smarty_Instance_smarty_include',$params,array(&$this));
		}
		$return  = parent::_smarty_include($params);
		$this->template_dir = $save_dir;
		return $return;
	}
	
	function apply_filters($params,&$smarty){
		// We'll call the WordPress apply_filters function, making the first parameter the $smarty object (so the filter can access Smarty variables)
		extract($params);
		if ($filter == ''){
			return '';
		}
		if (!isset($default_value)){
			$default_value = '';
		}
		return apply_filters($filter,$default_value,array(&$this,$params));			
	}
	
	
}
?>