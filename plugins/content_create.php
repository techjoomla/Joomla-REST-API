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
 * Example Catlist Create Plugin
 *
 * @package Joomla
 * @subpackage JFramework
 * @since 1.5
 */
class plgRestapiContent_Create extends JPlugin {
	
	function plgRestapiContent_Create( & $subject, $config ) {	

		parent::__construct( $subject, $config );
	}


	function onRestCall( $data ) {

		$id = $this->createnewcontent( $data );

		return $id;
	}
	
	function onRestInfo() {

	}
	
	function createnewcontent( $data ) 
	{	
		
		//jimport('joomla.user.helper');
		//require_once( JPATH_SITE .'/components/com_community/libraries/core.php');
                
                $error_messages         = array();
                $response               = NULL;
                $validated              = true;
                
               
                if($data['title']=='' || strlen($data['title'])=='0')
                {
                	$validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"title","message"=>"Title cannot be blank");  
                }
                if($data['sectionid']=='' || $data['sectionid']==0)
                {
                	$validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"sectionid","message"=>"Sectionid cannot be blank");
                }
                if($data['introtext']=='' || strlen($data['introtext'])==0)
                {
                	$validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"introtext","message"=>"Introtext cannot be blank");
                }
                if($data['catid']=='' || $data['catid']==0)
                {
                	$validated  = false;
                        $error_messages[] = array("id"=>1,"fieldname"=>"catid","message"=>"Catid cannot be blank");
                }
			
		        	$db 				= &JFactory::getDBO();				
				$row 				= new stdClass;
				$row->id 			= NULL;
				$row->title 		= $data['title'];
				$row->introtext 	= $data['introtext'];
				$row->alias			= str_replace(" ", "-", $row->title);
				$row->fulltext 		= $data['fulltext'];
				$row->state 		= $data['state'];
				$row->sectionid 	= $data['sectionid'];
				$row->catid 		= $data['catid'];
				$row->created_by 	= $data['creared_by'];
				$row->created		= date('Y-m-d H:i:s');
				 if( true == $validated ) 
				 {
						if(!$db->insertObject( '#__content', $row, 'id' ))
						{
							
							$error_messages[] = array("id"=>1,"fieldname"=>"createdby","message"=>"Content not inserted.please check fields in request");
							return false;
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
		        				$response = array('id' => $row->id);;
				}
		
			return $response;
		
	}
}
