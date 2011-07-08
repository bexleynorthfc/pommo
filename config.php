<?php 
/**
 * poMMo Configuration File:
 *   This file sets up your database, language, and debugging options.
 *
 *   IMPORTANT: File must be named "config.php" and saved in the
 * 	"root" directory of your poMMo installation (where bootstrap.php is)
 */
// DO NOT REMOVE OR CHANGE THE BELOW LINE
defined('_IS_VALID') or die('Move along...');

/************************************************************************
 * ::: MySQL Database Information :::
 *   in order to use poMMo, you must have access to a valid MySQL database.
 *   Contact your webhost for details if you are unsure of its details.
************************************************************************/

// * Set your MySQL username
$tmDSN = parse_url(DSN);
global $bmdb;
$bmdb['username'] = $tmDSN['user'];

// * Set your MySQL password
$bmdb['password'] = $tmDSN['pass'];

// * Set your MySQL hostname ("localhost" if  your MySQL database is running on the webserver)
$bmdb['hostname'] = $tmDSN['host'];

// * Set the name of the MySQL database used by poMMo
$bmdb['database'] = str_replace('/','',$tmDSN['path']); 

// * Set the table prefix  (change if you intend to have multiple poMMos running from the same database)
$Bootstrap = Bootstrap::getBootstrap();
if (!is_array($_SESSION)){
	session_start();
}
if (is_a($_poMMo_package,'Package')){
	global $poMMo_package;
	$poMMo_package = $GLOBALS['poMMo_package'] = $_poMMo_package; 
	$bmdb['prefix'] = $poMMo_package->db_prefix;
	define('poMMo_instance',base64_encode($poMMo_package->package_name));
}
elseif (isset($_REQUEST['package']) and $Bootstrap->packageExists($_REQUEST['package'])){
	global $poMMo_package;
	$poMMo_package = $GLOBALS['poMMo_package'] = $Bootstrap->usePackage($_REQUEST['package']);
	$bmdb['prefix'] = $poMMo_package->db_prefix;
	define('poMMo_instance',base64_encode($_REQUEST['package']));
}
elseif (isset($_REQUEST['pk']) and $Bootstrap->packageExists(base64_decode($_REQUEST['pk']))){ // using base64_encode/decode to obfuscate the package name
	global $poMMo_package;
	$poMMo_package = $GLOBALS['poMMo_package'] = $Bootstrap->usePackage(base64_decode($_REQUEST['pk']));
	$bmdb['prefix'] = $poMMo_package->db_prefix;
	define('poMMo_instance',$_REQUEST['pk']);
}
else{
	$bmdb['prefix'] = DATABASE_PREFIX.'pommo_';
	define('poMMo_instance',base64_encode('poMMo')); 
}
if (is_a($poMMo_package,'Package') and isset($poMMo_package->bm_SubscriberWord)){
	define('bm_SubscriberWord',$poMMo_package->bm_SubscriberWord);
}
else{
	define('bm_SubscriberWord','Subscriber');
}


/************************************************************************
 * ::: Language Information :::
 *   Set this to your desired locale  -- this is a work in progress
 * 
 *	bg - Bulgarian					es - Spanish
 *	br - Brazilian Portugese		fr - French
 *	da - Danish						it - Italian
 *	de - German						nl - Dutch
 *	en - English						ro - Romanian
************************************************************************/
define('bm_lang','en');


/******************[ OPTIONAL CONFIGURATION ]*******************
 * (Below options intended for debugging and overriding 
 * automatic configuration)
*/

/************************************************************************
 * ::: Debugging Information :::
 *   Only modify these values if you'd like to provide information
 *   to the developers.
*/

// enable (on) or disable (off) debug mode. Set this to 'on' to provide debugging information
//  to the developers. Make sure to set it to 'off' when you are finished collecting information.
define('bm_debug','off');

// set the verbosity level of logging.
//  1: Debugging
//  2: Informational
//  3: Important (default)
define('bm_verbosity',3);

/************************************************************************
 * ::: Blank Email :::
 * 
 * PoMMo requires that everyone in the database has a non-blank email address.
 * However, some people don't have email addresses.  Use the following constant
 * to mark the string that will be put in the email field to represent
 * an unknown email address.  
 *
 * (Note: only administrators can do this.  If they blank out the email address
 * on the update_user form, then this value will get inserted.  End Users must
 * enter a valid email address.  This will also work when importing)
 * 
 * Default: none
 */
if (is_a($poMMo_package,'Package') and isset($poMMo_package->bm_BlankEmail)){
	define('bm_BlankEmail', $poMMo_package->bm_BlankEmail);
}
else{
	//define('bm_BlankEmail', 'none');  
}
if (is_a($poMMo_package,'Package') and isset($poMMo_package->bm_importAllowBlankRequired)){
	define('bm_importAllowBlankRequired', $poMMo_package->bm_importAllowBlankRequired);
}
else{
	//define('bm_importAllowBlankRequired', true);  
}
if (is_a($poMMo_package,'Package') and isset($poMMo_package->bm_adminAllowBlankRequired)){
	define('bm_adminAllowBlankRequired', $poMMo_package->bm_adminAllowBlankRequired);
}
else{
	//define('bm_adminAllowBlankRequired', true);  
}
if (is_a($poMMo_package,'Package') and isset($poMMo_package->bm_exportWithSubscriberID)){
	define('bm_exportWithSubscriberID', $poMMo_package->bm_exportWithSubscriberID);
}
else{
	//define('bm_exportWithSubscriberID', true);  
}

/************************************************************************
 * ::: Password Authentication :::
 * To turn Password Authentication on, set this to the name of the 
 * password field 
 */
if (apply_filters('poMMo_use_password_field',true,$poMMo_package)){
	if (is_a($poMMo_package,'Package') and isset($poMMo_package->bm_PasswordField)){
		define('bm_PasswordField',$poMMo_package->bm_PasswordField); 
	}
	else{
		define('bm_PasswordField','Password'); 
	}
}
 
/************************************************************************
 * ::: Parent Email :::
 * To allow multiple people with the same email address to be tracked, 
 * we'll create a parent email field which will be set to the email
 * address of the parent record.  The parent will be able to login
 * and will be given a choice as to which records they wish to update
 */
if (is_a($poMMo_package,'Package') and isset($poMMo_package->bm_ParentEmailField)){
	define('bm_ParentEmailField',$poMMo_package->bm_ParentEmailField); 
}
else{
	//define('bm_ParentEmailField','Parent Email'); 
}

/************************************************************************
 * Uncomment (remove leading "//") and define the following 
 * settings to override default values.
 */


/************************************************************************
 * ::: Base URL :::
 * 
 * This is the path to pommo relative to the WEB.
 * For example, if poMMo is http://newsletter.mydomain.com/, the baseURL
 * would be '/'. If poMMo is http://www.mydomain.com/mysite/pommo, the
 * baseURL would be '/mysite/pommo/'
 * 
 * Default: Automatically Detected
 * NOTE: Include trailing slash
 */
//define('bm_baseUrl', '/mysite/newsletter');

/************************************************************************
 * ::: Cache Directory :::
 * 
 *   poMMo uses this directory to cache templates. By default, it
 *   is set to the "cache" directory in the poMMo root, and can
 *   safely be left blank or commented out (default).
 * 
 *   Make sure the webserver can write to this directory! poMMo
 *   will NOT WORK without being able to write to this directory.
 * 
 *   If you change its location, it is recommended to set it to a path
 *   outside the web root (for security reasons). 
 *  
 *   DO NOT USE A RELATIVE PATH, USE THE FULL SERVER PATH: e.g.
 *   '/home/b/brice/pommoCache'
 * 
*/
//define('bm_workDir','/path/to/pommoCache');

/************************************************************************
 * ::: Webserver Hostname :::
 * 
 * This is the hostname of the webserver running poMMo
 * 
 * Default: Automatically Detected
 */
//define('bm_hostname','www.mysite.com');
 
 /************************************************************************
 * ::: Webserver Port :::
 * 
 * This is the port number of the webserver running poMMo
 * 
 * Default: Automatically Detected [Usually 80, 8080, or 443]
 */
//define('bm_hostport','8080'); 
?>