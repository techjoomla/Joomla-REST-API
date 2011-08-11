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
class plgRestapiGroup_Member_list extends JPlugin {
	
	function plgRestapiGroup_Member_list( & $subject, $config ) {	

		parent::__construct( $subject, $config );
	}


	function onRestCall( $data ) {

		$id = $this->groupmemberlist( $data );

		return $id;
	}
	
	function onRestInfo() {

	}
	
	function groupmemberlist( $data ) 
	{	
	
                $error_messages         = array();
                $response               = NULL;
                $validated              = true;
                
                
                if($data['groupid']=='' || $data['groupid']=='0')
                {
                		$validated  = false;
                        	$error_messages[] = array("id"=>1,"fieldname"=>"groupid","message"=>"Groupid cannot be blank"); 
                }
                
				 if( true == $validated ) 
				 {
				
				    $db 			= &JFactory::getDBO();
				    $query 			= "SELECT id FROM #__community_groups WHERE id = '".$data['groupid']."'";
				    $db->setQuery($query);
				    $group_id = $db->loadResult();
				    if(!$group_id){
					$error_messages[] = array("id"=>1,"fieldname"=>"groupid","message"=>"Invalid group id. Check 'groupid' field in request"); 
				    }else{
					
				    	$query 			= "SELECT m.memberid, u.id, u.name, u.username, u.email, u.registerDate as registerdate, u.usertype 
						FROM #__community_groups_members As m, #__users AS u 
						WHERE m.memberid = u.id AND m.groupid = '".$data['groupid']."'";
					$db->setQuery($query);
					$result = $db->loadAssocList();
					   
						
					/*for($i=0;$i<count($result);$i++)
					{
						
					
						$query 	= "SELECT id, name, username, email, registerDate, usertype FROM #__users WHERE id=".$result[$i]['memberid'];
						$db->setQuery($query);
						$memberdata 	= $db->loadObjectList();
					
						foreach($memberdata as $memberinfo)
						{
							
							$result[$i]['id'] 		= $memberinfo->id;
							$result[$i]['name'] 		= $memberinfo->name;
							$result[$i]['username'] 	= $memberinfo->username;
							$result[$i]['email'] 		= $memberinfo->email;
							$result[$i]['registerDate'] 	= $memberinfo->registerDate;
							$result[$i]['usertype'] 	= $memberinfo->usertype;
							
						}
						
					}*/
				}	
              	 }

				if( true == isset( $error_messages ) && 0 < sizeof( $error_messages )) 
				{
					$res= array(); 
		       			foreach( $error_messages as $key => $error_message ) 
		       			{
						$res[] = $error_message;
				                            
                       			}
				
					$response = array("size" => -1,'errors'=>$res);
					//$response = $res;
				} else {
						if(count($result)){
			
						 $response = array('size' => count($result),'records'=>$result);
						}else{
							$response = array('size' => 0);
						}
		        				//$response = $result;
		        				
				}
		
			return $response;
		
	}
}
