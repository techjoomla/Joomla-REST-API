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
class plgRestapiGroup_Discussion_Replies_Create extends JPlugin {
	
	function plgRestapiGroup_Discussion_Replies_Create( & $subject, $config ) {	

		parent::__construct( $subject, $config );
	}


	function onRestCall( $data ) {

		$id = $this->createnewdiscussionreplies( $data );

		return $id;
	}
	
	function onRestInfo() {

	}
	
	function createnewdiscussionreplies( $data ) {	

                require_once( JPATH_SITE .'/components/com_community/libraries/core.php');
                
                $error_messages         = array();
                $response               = NULL;
                $validated              = true;
                $db 			= &JFactory::getDBO();


                if( "" == $data['postby'] || 0 == strlen( $data['postby'] )) {
                        $validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"postby","message"=>"postby cannot be blank");   
                }
                
		if("0" == $data['discussionid'] || "" == $data['discussionid']){
			$validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"discussionid","message"=>"discussionid (groupdiscussionid) cannot be blank");
		}
                
		
                if( $data['content'] =="") {
                        $validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"content","message"=>"Content cannot be blank");
	        }               
                
                if( true == $validated ) {  
                                                 
	                $user =& JFactory::getUser();
                        $user->load( $data['postby']);
                       
		        CFactory::load( 'libraries',    'activities' );
		        CFactory::load( 'libraries',    'wall' );	
		        CFactory::load( 'helpers',      'url' );
		        CFactory::load( 'helpers',      'owner' );
                	CFactory::load( 'helpers',      'time' );
		        CFactory::load( 'models',       'discussions' );
		                                  
		        $discussionModel= CFactory::getModel( 'Discussions' );
		        $discussion     =& JTable::getInstance( 'Discussion', 'CTable' );

		        $now    =& JFactory::getDate();
		        $now    = $now->toMySQL();
		
		        // Set the wall properties                
                	$wall		=& JTable::getInstance( 'Wall' , 'CTable' );                
                	$wall->comment  = strip_tags( $data['content'] );
		        $wall->type	= 'discussions';
		        $wall->contentid= $data['discussionid'];
		        $wall->post_by	= $data['postby'];
		        $wall->date	= $now;
                        $wall->published= 1; // need to add kind of setting for this.                                                   
		        $wall->ip       = $_SERVER['REMOTE_ADDR'];

			$query = "SELECT id FROM #__community_groups_discuss WHERE id =".$data['discussionid'];
			$db->setQuery($query);
			$isdiscussion = $db->LoadResult();
			if($data['discussionid']!="")
			{
				if(!$isdiscussion)
				{
					$error_messages[] = array("id"=>1,"fieldname"=>"discussionid","message"=>"Invalid discussion id (groupdiscussionid). Check 'discussionid' field in request");
				}
				else
				{
				
		
		        		// Save the wall content
		        		$query = "SELECT id FROM #__users WHERE id =".$data['postby'];
					$db->setQuery($query);
					$postby = $db->LoadResult();
					if(!$postby){
						$error_messages[] = array("id"=>1,"fieldname"=>"postby","message"=>"Invalid postby(userid). Check 'postby' field in request");
					}else{
		        	        $wall->store();
		        	       
					 }
				} 
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
		        	$response = array('id' => $wall->id);
		}
	        
		return $response;

	}


}
