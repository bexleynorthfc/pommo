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

// Added by Trevor Mills to integrate into his CMS
//include_once(dirname(__FILE__)."/../../../Standard.php");

/** 
 * Common class. Holds Configuration values, authentication state, etc.. (revived from session)
*/

class Common {
	
	var $_config; // configuration array to hold values loaded from the DB
	var $_authenticated; // TRUE if user has successfully logged on.
	var $_data; // Used to hold temporary data (such as an uploaded file's contents).. accessed via set (sets), get (returns), clear(deletes)
	var $_state; // Used to hold the state of pages -- e.g. variables that should be stored like 'limit, order, etc'
	var $_logger; // holds the logger class object
	var $_dbo; // the database object
	
	// default constructor
	function Common() {
		$this->_config = array ();
		
		if (empty($_SESSION['pommo']['authenticated'])) {
			$_SESSION['pommo']['authenticated'] = FALSE;
		}
		$this->_authenticated = & $_SESSION['pommo']['authenticated'];
		
		if (empty($_SESSION['pommo']['data'])) {
			$_SESSION['pommo']['data'] = array();
		}
		$this->_data = & $_SESSION['pommo']['data'];
		
		// initialize logger
		$this->_logger = new bmLogger(); // NOTE -> this clears messages that may have been retained (not outputted) from logger.
		
		// initialize database object
		global $bmdb;
		$this->_dbo = new dbo($bmdb['username'], $bmdb['password'], $bmdb['database'], $bmdb['hostname'], $bmdb['prefix']);
		
		// if debugging is set in config.php, enable debugging on the database.
		if (bm_debug == 'on') {
			$this->_dbo->debug(TRUE);
		}
	}

	// Loads configuration data from SESSION. If optional argument is supplied, configuration will be loaded from
	// the database & stored in SESSION.
	
	// NOTE: must be called after a proper session_start
	function loadConfig($fromDB = FALSE) {
		
		// if fromDB is passed, or config data is not in SESSION, attempt to load.
		// Trevor Mills - Adding in a token to know if we're on the right installation of poMMo
		// This allows several installations of poMMo to exist on the same site and the admin user
		// can go back and forth between them and always be confident that the correct 
		// config info is loaded. 
		if ($fromDB || empty($_SESSION['pommo']['config']) || $_SESSION['pommo']['token'] != md5(dirname(__FILE__))) {
			
			$_SESSION['pommo']['config'] = array();
			$dbo = & $this->_dbo;
			
			$dbo->dieOnQuery(FALSE);	
			$sql = 'SELECT * FROM '.$dbo->table['config'].' WHERE autoload=\'on\'';
			if ($dbo->query($sql)) {
				while ($row = mysql_fetch_assoc($dbo->_result))
					$_SESSION['pommo']['config'][$row['config_name']] = $row['config_value'];
			}
			$dbo->dieOnQUery(TRUE);		
			
			$_SESSION['pommo']['token'] != md5(dirname(__FILE__));
		}
		
		$this->_config = & $_SESSION['pommo']['config'];
		
		return (!empty ($this->_config['version'])) ? true : bmKill('poMMo does not appear to be set up.' .
					'Have you <a href="'.bm_baseUrl.'install/install.php">Installed?</a>');
	}
	
	// Gets specified config value(s) from the DB. 
	// Pass a single or array of config_names, returns array of their name>value.
	function getConfig($arg) {
		$dbo = & $this->_dbo;
		$dbo->dieOnQuery(FALSE);
		if (!is_array($arg))
			$arg = array($arg);
			
		$config = array();
		if ($arg[0] == 'all')
			$sql = 'SELECT config_name,config_value FROM '.$dbo->table['config'];
		else
			$sql = 'SELECT config_name,config_value FROM '.$dbo->table['config'].' WHERE config_name IN (\''.implode('\',\'',$arg).'\')';
		
		while ($row = $dbo->getRows($sql)) 
				$config[$row['config_name']] = $row['config_value'];
	
		$dbo->dieOnQUery(TRUE);
		return $config;
	}

	// Check if user has sucessfully logged on.
	function isAuthenticated() {
                // Changed by Trevor Mills to integrate with his CMS.  
                // 
                // Old code: 
		// return ($this->_authenticated) ? true : false;
		switch (CMS_PLATFORM){
		case 'WordPress':
			if (isset($_COOKIE['wordpress_logged_in_'.COOKIEHASH])){
				return true;
			}
			else{
				return false;
			}
			break;
		case 'topCMS':
		default:
			if (!isset($_SESSION['auth_level']) or $_SESSION['auth_site'] != md5(SITE_URL)){
	                        return false;
			}
	        else{
	                return true;
	        }
			break;
		}
		
	}

	// Set's authentication variable. TRUE = authenticated, FALSE/NULL = NOT... 
	// NOTE: must be called after proper session_start()
	// $this->_authenticated references $_SESSION['pommo']['authenticated'] in class constructor
	function setAuthenticated($var) {
		return ($this->_authenticated = $var) ? true : false;
	}


	// deletes stored data in SESSION [not authentication state or config values]
	function clear() {
		return ($this->_data = array()) ? true : false;
	}
	
	// merges data into SESSION ($this->_data references $_SESSION['pommo']['data'] in class constructor)
	function set($value) {
		if (!is_array($value))
			$value = array($value);
		return (empty($this->_data)) ? $this->_data = $value : $this->_data = array_merge($this->_data,$value);
	}
	
	function &get($name = NULL) {
		if ($name) {
			return (empty($this->_data[$name])) ? false : $this->_data[$name];
		}
		return $this->_data;
	}
	
	function stateInit($name = 'default', $state = array()) {
		if (empty($_SESSION['state_'.$name])) {
			$_SESSION['state_'.$name] = $state;
		}
		$this->_state =& $_SESSION['state_'.$name];
		return;
	}
	
	// used to access or set state Vars
	// TODO -> remove str2db (dbSanitize) when queries are made safe by DB abstraction class
	function stateVar($varName, $varValue = NULL) {
		if (!empty($varValue)) {
			$this->_state[$varName] = dbSanitize($varValue);
		}
		return (isset($this->_state[$varName])) ? $this->_state[$varName] : false;
	}
	
	function runInstall($ignore_errors = false){
		$logger = $GLOBALS['logger'] = & $this->_logger;
		$dbo = $GLOBALS['dbo'] = & $this->_dbo;

		$dbo->dieOnQuery(FALSE);
		$sql = 'SELECT config_value FROM '.$dbo->table['config'].' WHERE config_name=\'revision\'';
		$revision = $dbo->query($sql,0);
		if (!$revision){
			// poMMo hasn't been installed here at all.  Let's install it for them.
			if (isset ($_REQUEST['debugInstall']))
				$dbo->debug(TRUE);

			// Let's do them a favour and install, eh?  
			// drop existing poMMo tables - cleanup really
			foreach (array_keys($dbo->table) as $key) {
				$table = $dbo->table[$key];
				$sql = 'DROP TABLE IF EXISTS ' . $table;
				$dbo->query($sql);
			}

			// install poMMo
			require_once (bm_baseDir . '/install/helper.install.php');
			require_once (bm_baseDir . '/inc/db_procedures.php');
			$install = parse_mysql_dump($ignore_errors);
			if ($install){
				// load configuration, set message defaults.
				$this->loadConfig('TRUE');
				dbResetMessageDefaults('all');	

				// Insert initial values		
				$new_config = array();
				$new_config['site_name'] = get_bloginfo('site_name');
				$new_config['site_url'] = get_bloginfo('url');
				$new_config['list_name'] = get_bloginfo('site_name');
				$new_config['admin_email'] = get_bloginfo('admin_email');
				$new_config['list_fromname'] = get_bloginfo('site_name');
				$new_config['list_fromemail'] = get_bloginfo('admin_email');
				$new_config['list_frombounce'] = get_bloginfo('admin_email');

				foreach ($new_config as $key => $config){
					$sql = "UPDATE ".$dbo->table['config']." SET `config_value` = '".mysql_real_escape_string($config)."' WHERE `config_name` = '$key'";
					$dbo->query($sql);
				}
			}
			else{
				$dbo->debug(FALSE);

				// drop existing poMMo tables (cleanup)
				foreach (array_keys($dbo->table) as $key) {
					$table = $dbo->table[$key];
					$sql = 'DROP TABLE IF EXISTS ' . $table;
					$dbo->query($sql);
				}

				bmKill(sprintf(_T('poMMo installation failed!')));
			}
		}
		return true;
	}
}
?>