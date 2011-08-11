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
class plgRestapiGroup_Members_Create extends JPlugin {
	
	function plgRestapiGroup_Members_Create( & $subject, $config ) {	

		parent::__construct( $subject, $config );
	}


	function onRestCall( $data ) {

		$id = $this->createnewgroupmembers( $data );

		return $id;
	}
	
	function onRestInfo() {

	}
	
	function createnewgroupmembers( $data ) {	
		
		require_once( JPATH_SITE .'/components/com_community/libraries/core.php');
		CFactory::load( 'libraries' , 'apps' );
                
                $error_messages         = array();
                $success_messages       = array();
                $response               = NULL;
                $validated              = true;
                $db 			= &JFactory::getDBO();
                
                
                if( "" == $data['groupid'] || 0 == $data['groupid']) {
                        $validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"groupid","message"=>"Groupid cannot be blank");    
                }
                
                if( false == array_key_exists( 'memberids',  $data ) || 0 == sizeof( $data['memberids'] ) || $data['memberids'] == "") {
                        $validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"memberids","message"=>"Memberids cannot be blank"); 
                }
               
                if( true == $validated ) {                  
              	$membersid = array();
			if(!is_array($data['memberids']))
			$data['memberids'] = array($data['memberids']);

		        foreach( $data['memberids'] as $memberid ) {
				$query = "SELECT id FROM #__users WHERE id =".$memberid;
				$db->setQuery($query);
				$ismemberid = $db->LoadResult();
				if(!$ismemberid){
					
				}else{	
			         // create instance of group member
			         $member =& JTable::getInstance( 'GroupMembers' , 'CTable' );
			         $member->set( 'groupid', 	 $data['groupid'] );
			         $member->set( 'memberid',	 $memberid );
			         $member->set( 'approved',	 1 );
			         $member->set( 'permissions', 1 );
				
				$query = "SELECT id FROM #__community_groups WHERE id =".$data['groupid'];
				$db->setQuery($query);
				$isgroup = $db->LoadResult();
				if(!$isgroup){
					//$error_messages[] = "Invalid group id. Check 'groupid' field in request";
					$error_messages[] = array("id"=>1,"fieldname"=>"groupid","message"=>"Invalid group id. Check 'groupid' field in request");
				}else{	 
			         	if(!$member->store()) 
					{
			             		//$error_messages[] = "Member not inserted. please check 'memberid' field in request";
						$error_messages[] = array("id"=>1,"fieldname"=>"memberids","message"=>"Member not inserted. please check 'memberids' field in request");
 
			         	} 
					
					$membersid[]=$member->memberid;
					
				     }
				    
				}
			         
		        }
			
			
		        $db	=& JFactory::getDBO();
		        $query	= 'SELECT COUNT(1) FROM ' . $db->nameQuote( '#__community_groups_members' ) . ' '
					. 'WHERE groupid=' . $db->Quote( $data['groupid']) . ' '
					. 'AND approved=' . $db->Quote( '1' );

					$db->setQuery( $query );
					$membercount	= $db->loadResult();
					
					$members				= new stdClass();
					$members->id		= $data['groupid'];
					$members->membercount		= $membercount;	
					$db->updateObject( '#__community_groups' , $members , 'id');
		             	
	        }	
	        
	        if( true == isset( $error_messages ) && 0 < sizeof( $error_messages )) {
				$res= array(); 
		       		foreach( $error_messages as $key => $error_message ) 
		       		{
					$res[] = $error_message;
				                            
                       		}
				
				$response = array("id" => 0,'errors'=>$res);	
		} else {
		        $response = array('memberid' => $membersid );
		}

		return $response;

	}

}
