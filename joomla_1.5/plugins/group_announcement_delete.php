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
class plgRestapiGroup_Announcement_Delete extends JPlugin {
	
	function plgRestapiGroup_Announcement_Delete( & $subject, $config ) {	

		parent::__construct( $subject, $config );
	}


	function onRestCall( $data ) {

		$id = $this->deletegroupannouncement( $data );

		return $id;
	}
	
	function onRestInfo() {

	}
	
	function deletegroupannouncement( $data )
	{	
	
			require_once( JPATH_SITE .'/components/com_community/libraries/core.php');
			require_once( JPATH_SITE .'/libraries/joomla/filesystem/folder.php');
		
			CFactory::load( 'libraries' , 'apps' );
			$error_messages         = array();
			$response               = NULL;
			$validated              = true;
		
			
			if( "" == $data['groupid'] || 0 == $data['groupid']) {
				    	$validated  = false;
				    	$error_messages[] = array("id"=>1,"fieldname"=>"groupid","message"=>"Groupid cannot be blank");   
			}  
			if($data['bulletinid']=="" || 0 == $data['bulletinid']) 
			{
		                	$validated  = false;
		                	$error_messages[] = array("id"=>1,"fieldname"=>"bulletinid","message"=>"Bulletinid cannot be blank");  
			}
			$user =& JFactory::getUser();
			$group =& JTable::getInstance( 'Group' , 'CTable' );
			$group->id = $data['groupid'];  
		   	
			if( true == $validated )
			{
				$bulletin		=& JTable::getInstance( 'Bulletin' , 'CTable' );
				$bulletin->id		= $data['bulletinid'];
				$bulletin->groupid	= $data['groupid'];
				$validated              = true;

				if( true == empty( $bulletin->id )) 
				{
			                $validated  = false;
					$error_messages[] = array("id"=>1,"fieldname"=>"bulletinid","message"=>"Invalid bulletin id. Check 'bulletinid' field in request"); 
			                 
				}
					CFactory::load( 'helpers' , 'owner' );
					CFactory::load( 'models' , 'bulletins' );

					$groupsModel	=& CFactory::getModel( 'groups' );
					$bulletin		=& JTable::getInstance( 'Bulletin' , 'CTable' );
					$group			=& JTable::getInstance( 'Group' , 'CTable' );
					$group->load( $groupid );
					$bulletin->id		= $data['bulletinid'];
					$bulletin->groupid      = $data['groupid'];
					 
				if( $bulletin->delete() )
				{
			
					//add user points
					CFactory::load( 'libraries' , 'userpoints' );		
					CUserPoints::assignPoint('group.news.remove');			
					
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
						$response = array('id' => $bulletin->id);
				}
		
				return $response;
	
	}

}
