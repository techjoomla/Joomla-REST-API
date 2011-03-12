<?php
/**
 * @version $Id: group_announcement_create.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package Joomla
 * @subpackage JFramework
 * @copyright Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
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
 * Example Group Announcement Create Plugin
 *
 * @package Joomla
 * @subpackage JFramework
 * @since 1.5
 */
class plgRestapiGroup_Announcement_Update extends JPlugin {
	
	function plgRestapiGroup_Announcement_Update( & $subject, $config ) {	

		parent::__construct( $subject, $config );
	}


	function onRestCall( $data ) {

		$id = $this->updategroupannouncement( $data );

		return $id;
	}
	
	function onRestInfo() {

	}
	
	function updategroupannouncement( $data ) {	

		require_once( JPATH_SITE .'/components/com_community/libraries/core.php');
                require_once( JPATH_SITE .'/libraries/joomla/filesystem/folder.php');

		CFactory::load( 'libraries' , 'apps' );
                $error_messages         = array();
                $response               = NULL;
                $validated              = true;
                
                /*if( 0 == sizeof( $data )) {
                        $validated  = false;
                        $error_messages[] = 'Fail: Faild to load request data.';  
                }*/
                        
                if( "" == $data['createdby'] || 0 == strlen( $data['createdby'] )) {
                        $validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"createdby","message"=>"Createdby cannot be blank");  
                }  
                if( "" == $data['bulletinid'] || 0 == strlen( $data['bulletinid'] )) {
                        $validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"bulletinid","message"=>"Bulletinid cannot be blank");  
                }  
                if( "" == $data['groupid'] || 0 == strlen( $data['groupid'] )) {
                        $validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"groupid","message"=>"Groupid cannot be blank");  
                }  
		if( "" == $data['title'] || 0 == strlen( $data['title'] )) {
                        $validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"title","message"=>"Title cannot be blank"); 
                }  
		if( "" == $data['message'] || 0 == strlen( $data['message'] )) {
                        $validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"message","message"=>"Message cannot be blank");   
                }   
                    
                // get user object.
                $user =& JFactory::getUser();
                $user->load( $data['createdby'] );
                    
                // get group object.
                $group =& JTable::getInstance( 'Group' , 'CTable' );
	        $group->load( $data['groupid'] );       
	            
	        
	         if( $user->id != $group->ownerid ) {
	                $validated  = false;
                      
			$error_messages[] = array("id"=>1,"fieldname"=>"createdby and groupid","message"=>"Mismatched the user id and group owner id.Check 'createdby and groupid' fields in request");  
	        }  else{
                
	        
	        if( true == $validated ) {
	        
	                $bulletin		=& JTable::getInstance( 'Bulletin' , 'CTable' );
		        $bulletin->title	= $data['title'];
		        $bulletin->message	= $data['message'];
		        $bulletin->id	= $data['bulletinid'];
		        $validated              = true;
		       

	                $bulletin->id		= $data['bulletinid'];
	                $bulletin->groupid      = $data['groupid'];
	                $bulletin->date		= gmdate( 'Y-m-d H:i:s' );
	                $bulletin->created_by	= $data['createdby'];
	                
	               	if($data['published']==""){
			$bulletin->published	= 1;
			}else{
 			$bulletin->published	= $data['published'];
			}

			$bulletin->store();
			}	   
			
	        }       
		
		if( true == isset( $error_messages ) && 0 < sizeof( $error_messages )) {
			$res= array(); 
		       		foreach( $error_messages as $key => $error_message ) 
		       		{
					$res[] = $error_message;
				                            
                       		}
				
				$response = array("id" => 0,'errors'=>$res);	
		} else {
		        $response = array('id' => $bulletin->id);
		}
		
		return $response;


	}
	

}
