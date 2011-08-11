<?php

// Set flag that this is a parent file
define( '_JEXEC', 1 );
define( 'JPATH_BASE', dirname(__FILE__) );
define( 'DS', DIRECTORY_SEPARATOR );
define( 'LOGLEVEL', 2 );

require_once JPATH_BASE.DS.'includes'.DS.'defines.php';
require_once JPATH_BASE.DS.'includes'.DS.'framework.php';
require_once JPATH_BASE.DS.'includes'.DS.'custom_json.php';
require_once 'classes.php';
jimport('joomla.utilities.arrayhelper');
// We want to echo the errors so that the xmlrpc client has a chance to capture them in the payload
JError::setErrorHandling( E_ERROR,	 'die' );
JError::setErrorHandling( E_WARNING, 'ignore' );
JError::setErrorHandling( E_NOTICE,	 'ignore' );

// create the mainframe object
$mainframe =& JFactory::getApplication('site');
$document =& JFactory::getDocument();
$db =& JFactory::getDBO();

// Identify method
$pcs = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$url = parse_url( end($pcs) );

$pcs = @explode('.', $url['path']);
$call = $pcs[0];

$format = $pcs[1] ? $pcs[1] : 'json';

// Identify request method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$req = JRequest::get('post');
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
	$req = JRequest::get('get');
	//$noauth = 1;
} else {
	$req = $_REQUEST;
}

if ($format == 'info') { $noauth = 1; }

// authenticate the api caller.
$credentials['username'] = $req['auth_user'];
$credentials['password'] = $req['auth_pass'];
$options = array();
$options['autoregister'] = false;
$options['group'] = 'Administrator';
$authenticated = $mainframe->login($credentials, $options);
$user = JFactory::getUser();

// Unset access credos
unset($req['auth_user'],$req['auth_pass']);

// Run method only if login succeds and user is > Administrator
if (($authenticated === true && $user->id) || $noauth) {

	// load all available remote calls
	JPluginHelper::importPlugin( 'restapi', $call );
        
	if ($format == 'info') {
		$plugin = $mainframe->triggerEvent( 'onRestInfo', array($req) );	
	} else {
		$plugin = $mainframe->triggerEvent( 'onRestCall', array($req) );
	}

} else {
	$plugin[0] = 'Cannot login - Please provide correct auth_user and auth_password';
}

// Generate response
switch ($format) {

	case 'json':
	default:
	$document->setMimeEncoding('application/json');
	$op = json_encode($plugin[0]);
//		$op = custom_json::encode($plugin[0]);
	break;
	
	case 'xml':
	$document->setMimeEncoding('application/xml');
	$op = ArrayToXML::toXml($plugin[0]);
	break;
	
	case 'html':
	break;
	
	case 'info':
	$op = JoomlaRest::getInfo($plugin[0]);
	break;
	
}

// Logout
$mainframe->logout();


/* **** Added By - Deepak 
	* LOGLEVEL 0 - No logs save
	* 1 - Save only date, Method, IP
	* 2 - Save All. 
*/	

if(LOGLEVEL != 0) 
{

	if(LOGLEVEL == 2) 
	{		
		ApiHelperLogs::simpleLog($url['path'] . " request: " . JArrayHelper::toString($req));
		ApiHelperLogs::simpleLog("Response: ". $op);
	} else if(LOGLEVEL == 1) {
		ApiHelperLogs::simpleLog($url['path'] . " request: " . JArrayHelper::toString($req));
	}	
} 	



// Deliver response to client
echo $op;
jexit();


class ApiHelperLogs
{
    /**
     * Simple log
     * @param string $comment  The comment to log
     * @param int $userId      An optional user ID
     */
    function simpleLog($comment, $level=1)
    {
        // Include the library dependancies
        jimport('joomla.error.log');
        $my = JFactory::getUser();
        $options = array('format' => "{DATE} - {TIME} - {IP} - {COMMENT}");
        // Create the instance of the log file in case we use it later
        $log = &JLog::getInstance('restapi.log');
        $log->addEntry(array('comment' => $comment, 'ip' => $_SERVER['REMOTE_ADDR']));
    }
}	
