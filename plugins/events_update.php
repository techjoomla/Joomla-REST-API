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


class plgRestapiEvents_Update extends JPlugin
{


	function plgRestapiEvents_Update( & $subject, $config ) {
		parent::__construct( $subject, $config );
	}


	function onRestCall( $data ) {
		$id = $this->updateevents( $data );
		return $id;
	}
	
	function updateevents( $data )	
	{		
    	
        jimport('joomla.user.helper');
		require_once( JPATH_SITE .'/components/com_community/libraries/core.php');
                $db 			= &JFactory::getDBO();
                $error_messages         = array();
                $response               = NULL;
                $validated              = true;
				$config = & CFactory::getConfig();
				CFactory::load('helpers', 'limits' );
                CFactory::load( 'libraries' , 'apps' );

               	if( "" == $data['eventid'] || 0 == strlen( $data['eventid'] )) 
				{
                        $validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"eventid","message"=>"eventid cannot be blank");  
                }
                
                if( "" == $data['creator'] || 0 == strlen( $data['creator'] )) 
				{
                        $validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"creator","message"=>"creator cannot be blank");  
                }
                if( "" == $data['catid'] || 0 == strlen( $data['catid'] )) 
				{
		       			$validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"catid","message"=>"catid cannot be blank");  
				}
				if( $data['title']=="") {
		                $validated  = false;
		                $error_messages[] = array("id"=>1,"fieldname"=>"title","message"=>"title cannot be blank"); 
				}

				if( $data['location']=="") {
						$validated  = false;
		                $error_messages[] = array("id"=>1,"fieldname"=>"location","message"=>"location cannot be blank");
				}
				if( $data['allowinvite']=="") {
						$validated  = false;
		                $error_messages[] = array("id"=>1,"fieldname"=>"allowinvite","message"=>"allowinvite cannot be blank");
				}
			if( true == $validated ) {

					// Bind the data with the table first
					$event		= JTable::getInstance( 'Event' , 'CTable' );
					$event->set( 'id', 		$data['eventid'] );	
					$event->set( 'title', 		$data['title'] );
					$event->set( 'description', $data['description'] );
					$event->set( 'location', 	$data['location'] );
					$event->set( 'ticket', 	$data['ticket'] );
					$event->set( 'catid', 	$data['catid']);
					$event->set( 'allowinvite', 	$data['allowinvite']);
					$event->set( 'creator', 	$data['creator']);
					$event->set( 'created', 	gmdate( 'Y-m-d H:i:s' ));
					$event->set( 'confirmedcount', 	1);
					
					if($data['permission']==""){
					$event->set( 'permission', 	0);
					}else{
					$event->set( 'permission', 	$data['permission']);
					}
					if($data['startdate']==""){
					$event->set( 'startdate', 	gmdate( 'Y-m-d H:i:s' ));
					}else{
					$event->set( 'startdate', 	$data['startdate'] );
					}
					if($data['enddate']==""){
					$event->set( 'enddate', 	gmdate( 'Y-m-d H:i:s' ));
					}else{
					$event->set( 'enddate', 	$data['enddate'] );
					}
					$query = "SELECT id FROM #__users WHERE id =".$data['creator'];
					$db->setQuery($query);
					$isowner = $db->LoadResult();
						// we here save the group 1st, else the group->id will be missing and causing the member connection and activities broken.
					if(!$isowner){
						$error_messages[] = array("id"=>1,"fieldname"=>"creator","message"=>"Invalid creator id. Update 'creator' field in request");
					}else{
							$event->store();
					}
	

						if(!$isowner){
				
						}else{
							$event_members				= new stdClass();
							$event_members->eventid		= $data['eventid'];
							$event_members->memberid	= $data['creator'];	
							$db->updateObject( '#__community_events_members' , $event_members, 'eventid');
							
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
						$response = array('id' => $event->id);
				}	
		
				return $response;
	
		
  	}
  	
  	
}
