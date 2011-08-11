<?php
/**
 * @version		$Id: example.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla
 * @subpackage	JFramework
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
 * Example Authentication Plugin
 *
 * @package		Joomla
 * @subpackage	JFramework
 * @since 1.5
 */
class plgRestapiUser_Delete extends JPlugin
{


	function plgRestapiUser_Delete( & $subject, $config ) {
		parent::__construct( $subject, $config );
	}


	function onRestCall( $data ) {
		$id = $this->deleteuser( $data );
		return $id;
	}
	
	function onRestInfo() {

	}
	
	
	
	function deleteuser( $data )	{		
	
		jimport('joomla.user.helper');
		require_once( JPATH_SITE .'/components/com_community/libraries/core.php');
        	require_once( JPATH_SITE .'/libraries/joomla/filesystem/folder.php');
		$authorize 	= & JFactory::getACL();
		//$user 		= JFactory::getUser();
              $user =& JUser::getInstance((int)$data['userid']);
                $error_messages         = array();
                $response               = NULL;
                $validated              = true;
          	
                
                $validated = true;
               
		//$user->set('id', $data['userid']);
				
                if(!$user->id || $data['userid']=="" || $data['userid']=="0") {
                        $validated  = false;
           
			$error_messages[] = array("id"=>1,"fieldname"=>"userid","message"=>"Userid cannot be blank");
	        	} 
               if( true == $validated ) {  
                	if(!$user->delete()) 
			{               	                        	        
                	        
				$error_messages[] = array("id"=>1,"fieldname"=>"userid","message"=>"userid not exist modify the field userid");
                	} 
               }	
                      
                if( true == isset( $error_messages ) && 0 < sizeof( $error_messages )) 
		{
			$res= array(); 
		       		foreach( $error_messages as $key => $error_message ) 
		       		{
					$res[] = $error_message;
				                            
                       		}
				
				$response = array("id" => 0,'errors'=>$res);
		} else {
		        $response = array('id' => $user->id);
		}
		
		return $response;
	}

	

}
