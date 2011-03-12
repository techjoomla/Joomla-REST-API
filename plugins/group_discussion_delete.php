<?php
/**
 * @version $Id: group_discussion_create.php 10381 2008-06-01 03:35:53Z pasamio $
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
 * Example Group Discussion Create Plugin
 *
 * @package Joomla
 * @subpackage JFramework
 * @since 1.5
 */
class plgRestapiGroup_Discussion_Delete extends JPlugin {
	
	function plgRestapiGroup_Discussion_Delete( & $subject, $config ) {	

		parent::__construct( $subject, $config );
	}


	function onRestCall( $data ) {

		$id = $this->deletegroupdiscussion( $data );

		return $id;
	}
	
	function onRestInfo() {

	}

	function deletegroupdiscussion( $data ) {	
		
		
		require_once( JPATH_SITE .'/components/com_community/libraries/core.php');
		CFactory::load( 'libraries' , 'apps' );

		$error_messages         = array();
		$response               = NULL;
		$validated              = true;
		
		
		if( "" == $data['groupid'] || 0 == $data['groupid']) {
		        $validated  = false;
		 	$error_messages[] = array("id"=>1,"fieldname"=>"groupid","message"=>"Groupid cannot be blank");
		}
		
		
		$inputFilter    = CFactory::getInputFilter( true );
		$validated      = true;
		
		if( true == $validated ) {
			if($data['discuss_id']=="" || $data['discuss_id']=="0"){
			$error_messages[] = array("id"=>1,"fieldname"=>"discussid","message"=>"Discuss id cannot be blank");
			}else{
			
			CFactory::load( 'helpers' , 'owner' );
			CFactory::load( 'models' , 'discussions' );
			$groupsModel	=& CFactory::getModel( 'groups' );
			$wallModel		=& CFactory::getModel( 'wall' );
			$discussion		=& JTable::getInstance( 'Discussion' , 'CTable' );
			$group			=& JTable::getInstance( 'Group' , 'CTable' );
			$group->load( $groupid );
			//$isGroupAdmin	= $groupsModel->isAdmin( $my->id , $group->id );
			$discussion->set( 'id', 	strip_tags( $data['discuss_id'] ));
			
				if( $discussion->delete() )
				{
					// Remove the replies to this discussion as well since we no longer need them
					$wallModel->deleteAllChildPosts( $data['discuss_id'] , 'discussions' );

					// Substract the count from the groups table
					$groupsModel->substractDiscussCount( $groupid );
					
					//$success_message = "Group discussion id[" . $discussion->id . "] deleted successfully.";
					
				}
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
						$response = array('id' => $discussion->id);
				}
		
				return $response;

	}


}
