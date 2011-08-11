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
jimport('joomla.user.helper');
jimport( 'joomla.application.component.helper' );
require_once( JPATH_SITE .DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php');
require_once( JPATH_SITE .DS.'libraries'.DS.'joomla'.DS.'filesystem'.DS.'folder.php');


class plgRestapiEvents_Delete extends JPlugin
{


	function plgRestapiEvents_Delete( & $subject, $config ) {
		parent::__construct( $subject, $config );
	}


	function onRestCall( $data ) {
		$id = $this->deleteevents( $data );
		return $id;
	}
	
	function deleteevents( $data )	
	{		
    	jimport('joomla.user.helper');
		require_once( JPATH_SITE .'/components/com_community/libraries/core.php');
        	$error_messages         = array();
        	$response               = NULL;
        	$validated              = true;

		// Get the configuration object.
		CFactory::load( 'libraries' , 'activities' );
		CFactory::load( 'helpers' , 'owner' );
		CFactory::load( 'models' , 'events' );
		$config = & CFactory::getConfig();
		CFactory::load('helpers', 'limits' );
        CFactory::load( 'libraries' , 'apps' );
                
       	if($data['eventid']== "" || $data['eventid']== "0") 
       	{
                        $validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"eventid","message"=>"Eventid cannot be blank");
		}
		
		if( true == $validated ) 
		{
			// Bind the data with the table first
			$model	=& CFactory::getModel( 'events' );
			$event	=& JTable::getInstance( 'Event' , 'CTable' );
			$event->load( $data['eventid'] ); 
		
			
			if( $event->delete() )
			{

				jimport( 'joomla.filesystem.file' );			
						
			}
			
			$event->deleteAllMembers();	
			CFactory::load ( 'libraries',   'activities' );
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
		        		$response = array('id' => $event->id);;
		}
		
		return $response;
		

		
  	}
  	
  	
}
