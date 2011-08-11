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
class plgRestapiGroup_Discussion_Create extends JPlugin {
	
	function plgRestapiGroup_Discussion_Create( & $subject, $config ) {	

		parent::__construct( $subject, $config );
	}


	function onRestCall( $data ) {

		$id = $this->createnewgroupdiscussion( $data );

		return $id;
	}
	
	function onRestInfo() {

	}
	
	function createnewgroupdiscussion( $data ) {	
		
		require_once( JPATH_SITE .'/components/com_community/libraries/core.php');
		CFactory::load( 'libraries' , 'apps' );

                $error_messages         = array();
                $response               = NULL;
                $validated              = true;
                $db 			= &JFactory::getDBO();
                $survey_filepath = JPATH_ROOT.DS.'configuration'.DS.'surveyfiles'.DS.$data['survey_filename'];
                   
                if( "" == $data['creator'] || 0 == $data['creator']) {
                        $validated  = false;
					$error_messages[] = array("id"=>1,"fieldname"=>"creator","message"=>"Creator cannot be blank"); 
                } 
                
                if( "" == $data['groupid'] || 0 == $data['groupid']) {
                        $validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"groupid","message"=>"Groupid cannot be blank");  
                }
                if( $data['title'] == "" || 0 == strlen($data['title'])) 
				{
		         		$validated  = false;
		         		$error_messages[] = array("id"=>1,"fieldname"=>"title","message"=>"Title cannot be blank");
				}
			
				if($data['message'] == "" || 0 == strlen($data['message'])) 
				{
			  			$validated  = false;
		          		$error_messages[] = array("id"=>1,"fieldname"=>"message","message"=>"Message cannot be blank");
				}
        
        		
			$inputFilter    = CFactory::getInputFilter( true );
			$validated      = true;
		
		
			    
			if( true == $validated ) {

		    // Bind the data with the table first
			$discussion =& JTable::getInstance( 'Discussion' , 'CTable' );	
			$discussion->set( 'title', 	strip_tags( $data['title'] ));
			$discussion->set( 'message', 	$inputFilter->clean( $data['message'] ));
			$discussion->set( 'groupid', 	$data['groupid'] );
			$discussion->set( 'creator', 	$data['creator'] );
			$discussion->set( 'created', 	gmdate( 'Y-m-d H:i:s' ));
			$discussion->set( 'lastreplied',$discussion->created );
			
			
				$isNew	= is_null( $discussion->id ) || !$discussion->id ? true : false;
		        
		        $query = "SELECT id FROM #__users WHERE id =".$data['creator'];
				$db->setQuery($query);
				$creator = $db->LoadResult();
			
				$query = "SELECT id FROM #__community_groups WHERE id =".$data['groupid'];
				$db->setQuery($query);
				$isgroup = $db->LoadResult();

				if(!$creator){
				
					$error_messages[] = array("id"=>1,"fieldname"=>"creator","message"=>"Invalid discussion creator id. Check 'creator' field in request");
				} 
				if(!$isgroup){
					
						$error_messages[] = array("id"=>1,"fieldname"=>"groupid","message"=>"Invalid group id. Check 'groupid' field in request");
					}else{		
				   
		             if(!file_exists($survey_filepath))
					{
			    	$validated  = false;						
					$error_messages[] = array("id"=>1,"fieldname"=>"survey_filename","message"=>"Invalid Survey File Name");
						
					} else {
						 // Save the discussion.      
		             	$discussion->store();	
		             	
						$survey = new stdClass;						
						$survey->group_id = $data['groupid'];						
	  					$survey->discussion_id = $discussion->id;	  					
	  					$survey->status = 0;
	  					$survey->timestamp = date('Y-m-d H:i:s');
	  					$survey->filename = $data['survey_filename'];
	 					$db->insertObject( '#__myhsclosure_survey', $survey,'discussion_id');

					}      
		                   
				}
		  
		
		        //add user points
		        CFactory::load( 'libraries', 'userpoints' );		
		        CUserPoints::assignPoint( 'group.discussion.create' );
		       	if(!$creator)
			{
				
			}if(!$isgroup)
			{
					
			}else if($data['isqm']!='') 
				 {
				        	$db 				= &JFactory::getDBO();				
						$row 				= new stdClass;
						$row->id 			= NULL;
						$row->discussion_id = $discussion->id;
						$row->qm_flag 		= $data['isqm'];
						$db->insertObject( '#__myhs_qmflag', $row, 'id' );
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
