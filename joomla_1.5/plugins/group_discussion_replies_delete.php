<?php
/**
 * @version $Id: wall_post_create.php 10381 2008-06-01 03:35:53Z pasamio $
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
 * Example Wall Post Create Plugin
 *
 * @package Joomla
 * @subpackage JFramework
 * @since 1.5
 */
class plgRestapiGroup_Discussion_Replies_Delete extends JPlugin {
	
	function plgRestapiGroup_Discussion_Replies_Delete( & $subject, $config ) {	

		parent::__construct( $subject, $config );
	}


	function onRestCall( $data ) {

		$id = $this->deleterepliespost( $data );

		return $id;
	}
	
	function onRestInfo() {

	}
	/*
	
	*/
	function deleterepliespost( $data ) {	

               	require_once( JPATH_SITE .'/components/com_community/libraries/core.php');
                
                $error_messages         = array();
                $response               = NULL;
                $validated              = true;
              	$db 			= &JFactory::getDBO();
                    
	        if($data['wall_id']=="" || $data['wall_id']=="0") {
                        $validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"wall_id","message"=>"Wall id cannot be blank");
	        } 
	        		
		//@rule: Check if user is really allowed to remove the current wall
		$my			= CFactory::getUser();
		$model		=& CFactory::getModel( 'wall' );
		$wall		= $model->get( $data['wall_id'] );
		
		$groupModel	=& CFactory::getModel( 'groups' );
		$group		=& JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $wall->contentid );
		
		CFactory::load( 'helpers' , 'owner' );
			$query = "SELECT id FROM #__community_wall WHERE id ='".$data['wall_id']."' AND type = 'discussions'";
			$db->setQuery($query);
			$isdiscussion = $db->LoadResult();
			if(!$isdiscussion){
			    $error_messages[] = array("id"=>1,"fieldname"=>"wallid","message"=>"wall id for type discussion does not exists. Modify the wall_id field");
			}else{
			if( !$model->deletePost( $data['wall_id'] ) )
			{
				$validated  = false;
            			//$error_messages[] = 'wall id does not exists. Modify the wall_id fields in request'; 
				$error_messages[] = array("id"=>1,"fieldname"=>"wallid","message"=>"wall id does not exists. Modify the wall_id field");
			}
			else
			{
				if($wall->post_by != 0)
				{
					//add user points
					CFactory::load( 'libraries' , 'userpoints' );		
					CUserPoints::assignPoint('wall.remove', $wall->post_by);
				}			
			}
		
		       // Substract the count
			 $groupModel->substractWallCount( $wall->contentid );
		}	
		if( true == isset( $error_messages ) && 0 < sizeof( $error_messages )) {
		       		$res= array(); 
		       		foreach( $error_messages as $key => $error_message ) 
		       		{
					$res[] = $error_message;
				                            
                       		}
				
				$response = array("id" => 0,'errors'=>$res);
		} else {
		        $response = array('id' => $wall->id);
		}
	        
		return $response;

	}

}

