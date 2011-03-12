<?php
/**
 * @version $Id: group_members_create.php 10381 2008-06-01 03:35:53Z pasamio $
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
 * Example Group Members Create Plugin
 *
 * @package Joomla
 * @subpackage JFramework
 * @since 1.5
 */
class plgRestapiGroup_Members_Delete extends JPlugin {
	
	function plgRestapiGroup_Members_Delete( & $subject, $config ) {	

		parent::__construct( $subject, $config );
	}


	function onRestCall( $data ) {

		$id = $this->deletegroupmembers( $data );

		return $id;
	}
	
	function onRestInfo() {

	}
	
	function deletegroupmembers( $data )
	{	
		require_once( JPATH_SITE .'/components/com_community/libraries/core.php');
		CFactory::load( 'libraries' , 'apps' );
                
		$error_messages         = array();
		$success_messages       = array();
		$response               = NULL;
		$validated              = true;
		
		
		
		if( "" == $data['groupid'] || 0 == $data['groupid']) {
		        $validated  = false;
			$error_messages[] = array("id"=>1,"fieldname"=>"groupid","message"=>"Groupid cannot be blank");   
		}
		
		if( false == array_key_exists( 'memberids',  $data ) || 0 == sizeof( $data['memberids'] )) {
		        $validated  = false;
		        $error_messages[] = array("id"=>1,"fieldname"=>"memberids","message"=>"Memberids cannot be blank");    
		}
	       
		if( true == $validated ) {
				
				$model	=& CFactory::getModel( 'groups' );
				$group		=& JTable::getInstance( 'Group' , 'CTable' );
				$group->id = $data['groupid'];  
			    	$group->ownerid = $data['ownerid']; 
		}
		
		
			if($data['ownerid'] == $data['memberids'])
			{
			     $validated  = false;
		     	     
			     $error_messages[] = array("id"=>1,"fieldname"=>"ownerid/memberid","message"=>"owner id and member id are same.please update 'ownwrid or memberid' fields in request");
			}
			else
			{
				$groupMember	=& JTable::getInstance( 'GroupMembers' , 'CTable' );
				$memberId=$data['memberids'];
				$groupId=$data['groupid'];
				$groupMember->load( $memberId , $groupId );
			
				$data		= new stdClass();

				$data->groupid	= $groupId;
				$data->memberid	=  $memberId;
			
			

				$data		= new stdClass();

				$data->groupid	= $groupId;
				$data->memberid	= $memberId;

				$model->removeMember($data);
			
				
				$db	=& JFactory::getDBO();
				$query	= 'SELECT COUNT(1) FROM ' . $db->nameQuote( '#__community_groups_members' ) . ' '
						. 'WHERE groupid=' . $db->Quote( $groupId) . ' '
						. 'AND approved=' . $db->Quote( '1' );

						$db->setQuery( $query );
						$membercount	= $db->loadResult();
					
						$members				= new stdClass();
						$members->id		= $groupId;
						$members->membercount		= $membercount;	
						$db->updateObject( '#__community_groups' , $members , 'id');
			
				//add user points
				CFactory::load( 'libraries' , 'userpoints' );		
				CUserPoints::assignPoint('group.member.remove', $memberId);			
				
			
			}
			if( true == isset( $error_messages ) && 0 < sizeof( $error_messages )) {
				$res= array(); 
		       		foreach( $error_messages as $key => $error_message ) 
		       		{
					$res[] = $error_message;
				                            
                       		}
				
				$response = array("id" => 0,'errors'=>$res);
			} else {
				$response = array('id' => $memberId);
			}
		
			return $response;
			       
	}


}
