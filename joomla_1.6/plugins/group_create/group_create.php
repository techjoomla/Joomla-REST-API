<?php
/**
 * @version $Id: group_create.php 10381 2008-06-01 03:35:53Z pasamio $
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
 * Example Group Create Plugin
 *
 * @package Joomla
 * @subpackage JFramework
 * @since 1.5
 */
class plgRestapiGroup_Create extends JPlugin {
	
	function plgRestapiGroup_Create( & $subject, $config ) {	

		parent::__construct( $subject, $config );
	}


	function onRestCall( $data ) {
		$db 				= &JFactory::getDBO();
		$error_messages         = array();
        	$response               = NULL;

        if($data['isprivate']==1)
	{
				
		$query = "SELECT id FROM #__community_groups WHERE ownerid=".$data['ownerid'];
		$db->setQuery($query);
		$res = $db->loadResultArray();
		$result = implode(",", $res);
	
		$query = "SELECT COUNT(group_id) FROM #__myhsgroup_type WHERE group_id IN($result) AND isprivate = 1";
		$db->setQuery($query);
		$rescount = $db->LoadResult();
		if($rescount >= 1)
		{
			$res1[]= array("id" => 0);
			$res1[] = array("1"=>'User can create only one private group.'); 
			$res1[] = array("2"=> 'User already created one private group.');
			$error_messages = $res1;
			return $error_messages;
		}else{
			 $id = $this->createnewgroup( $data );
			 return $id;
		}
	}else
	{
		 $id = $this->createnewgroup( $data );
		 return $id;
	}
		       
		        
	}
	
	function onRestInfo() {

	}
	
	function onGroupCreate($groupid,$private) 
	{
	    $db 				= &JFactory::getDBO();
	    if($private !="")
	    {
	        $row 				= new stdClass;
			$row->group_id 		= $groupid;
			$row->isprivate 	= $private;
		
			if(!$db->insertObject( '#__myhsgroup_type', $row))
			{
				$error_message = "Error in saving Content";
				return false;
			} 
				
		}
		
			   $query = "SELECT * FROM #__community_groups WHERE id!=".$groupid;
			   $db->setQuery($query);
			   $result = $db->loadObjectList();
				   foreach($result as $res)
				   {		
				   			$query = "SELECT group_id FROM #__myhsgroup_type WHERE group_id=".$res->id;
			   				$db->setQuery($query);
			   				$result = $db->loadResult();
			   					if(!$result)
			   					{
				   					$row 				= new stdClass;
									$row->group_id 		= $res->id;
									$row->isprivate 		= 0;
									$db->insertObject( '#__myhsgroup_type', $row);
								}
				   	}
	         
	}
	
	
	
	
	function createnewgroup( $data ) 
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

               
                if( "" == $data['ownerid'] || 0 == strlen( $data['ownerid'] )) 
		{
                        $validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"ownerid","message"=>"Ownerid cannot be blank");  
                }
                if( "" == $data['categoryid'] || 0 == strlen( $data['categoryid'] )) 
		{
		       	$validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"categoryid","message"=>"Categoryid cannot be blank");  
		}
		if( $data['name']=="") {
		                $validated  = false;
		                $error_messages[] = array("id"=>1,"fieldname"=>"name","message"=>"Name cannot be blank"); 
		}

		if( $data['description']=="") {
				$validated  = false;
		                $error_messages[] = array("id"=>1,"fieldname"=>"description","message"=>"Description cannot be blank");
		}
		if( $data['group_type']=="") {
				$validated  = false;
		                $error_messages[] = array("id"=>1,"fieldname"=>"grouptype","message"=>"Group_type public/private cannot be blank");
		}
		if( true == $validated ) {

			// Bind the data with the table first
			$group =& JTable::getInstance( 'Group', 'CTable' );		
			$group->set( 'name', 		$data['name'] );
			$group->set( 'description', 	$data['description'] );
			$group->set( 'categoryid', 	$data['categoryid'] );
			$group->set( 'website', 	$data['website'] );
			$group->set( 'ownerid', 	$data['ownerid'] );
			$group->set( 'approvals', 	$data['group_type'] );
		    $group->set( 'created', 	gmdate( 'Y-m-d H:i:s' ));
		        		
		        // Since now is default to the admin, default to publish the group
 			$group->set( 'approvals', $data['group_type'] );
 			
		        // Set the default thumbnail and avatar for the group.
		        $group->set('thumb', 		'components/com_community/assets/group_thumb.jpg');
		        $group->set('avatar', 		'components/com_community/assets/group.jpg');

		        // Check if moderation is turned on.
			if($data['published']==""){
				$group->set('published', 1);	
			}else{
				$group->set('published', $data['published'] );	
			}
		        
		
		        // @rule: Retrieve params and store it back as raw string
		        $params	= new JParameter('');

			if($data['discussordering']==""){
				$params->set( 'discussordering' , 1 );
			}else{
				$params->set( 'discussordering' , $data['discussordering'] );
			}		
		        
			if($data['photopermission']==""){
				$photopermission = 1;
			}else{
				$photopermission = $data['grouprecentphotos'];
			}
			if($data['videopermission']==""){
				$videopermission = 1;
			}else{
				$videopermission = $data['videopermission'];
			}

		        if($data['isprivate']==1) 
			{
		        	$params->set( 'photopermission' , 	-1 );
		        	$params->set( 'videopermission' , 	-1);
		        }else{
				
		        	$params->set( 'photopermission' , 	$photopermission );
		        	$params->set( 'videopermission' , 	$videopermission );
		        }
			if($data['grouprecentphotos']==""){
				$params->set( 'grouprecentphotos' , 1 );
			}else{
				$params->set( 'grouprecentphotos' , 	$data['grouprecentphotos'] );
			}
		        if($data['grouprecentvideos']==""){
				$params->set( 'grouprecentvideos' , 1 );
			}else{
				$params->set( 'grouprecentvideos' , 	$data['grouprecentvideos'] );
			}
		        if($data['newmembernotification']==""){
				$params->set( 'newmembernotification' , 1 );
			}else{
				$params->set( 'newmembernotification' ,	$data['newmembernotification'] );
			}
		        if($data['joinrequestnotification']==""){
				$params->set( 'joinrequestnotification' , 1 );
			}else{
				$params->set( 'joinrequestnotification' ,	$data['joinrequestnotification'] );
			}
		        if($data['wallnotification']==""){
				$params->set( 'wallnotification' , 1 );
			}else{
				$params->set( 'wallnotification' ,	$data['wallnotification'] );
			}
		 
			

		        $group->set('params',$params->toString());
			$query = "SELECT id FROM #__users WHERE id =".$data['ownerid'];
			$db->setQuery($query);
			$isowner = $db->LoadResult();
		        // we here save the group 1st, else the group->id will be missing and causing the member connection and activities broken.
			if(!$isowner){
				$error_messages[] = array("id"=>1,"fieldname"=>"ownerid","message"=>"Invalid group owner id. Update 'ownerid' field in request");
			}else{
		        	$group->store();
			}
		
		        // Since this is storing groups, we also need to store the creator / admin into the groups members table.
		        $member	=& JTable::getInstance( 'GroupMembers' , 'CTable' );
		        $member->set( 'groupid',	$group->id );
		        $member->set( 'memberid',	$group->ownerid );
		
		        // Creator should always be 1 as approved as they are the creator.
		        $member->set( 'approved',	1 );
		
		        // Setup required permissions in the future
		        $member->set( 'permissions',	1 );
			if(!$isowner){
				
			}else{
		        	$member->store();
			}
		        
		
		        //add user points
		        CFactory::load( 'libraries', 'userpoints' );		
		        CUserPoints::assignPoint( 'group.create' );
		        if(!$isowner){
				
			}else{
		        	$this->onGroupCreate($group->id,$data['isprivate']);
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
			//$response = $error_messages;
			
				
		} else {
		        $response = array('id' => $group->id);
		}	
		
		return $response;
		
	}
}
