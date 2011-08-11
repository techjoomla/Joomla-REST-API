<?php
/**
 * @version		$Id: example.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla
 * @subpackage	JFramework
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
jimport('joomla.user.helper');
jimport( 'joomla.application.component.helper' );
require_once( JPATH_SITE .DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php');
require_once( JPATH_SITE .DS.'libraries'.DS.'joomla'.DS.'filesystem'.DS.'folder.php');
//vaibhav
if(JFolder::exists(JPATH_BASE.DS.'components'.DS.'com_xipt'))
{
require_once( JPATH_SITE .DS.'components'.DS.'com_xipt'.DS.'api.xipt.php');
}
class plgRestapiUser_Create extends JPlugin
{


	function plgRestapiUser_Create( & $subject, $config ) {
		parent::__construct( $subject, $config );
	}


	function onRestCall( $data ) {
		$id = $this->createnewuser( $data );
		return $id;
	}
	
	function isValidEmail( $email ) {
	
		$pattern = "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$";

    	if ( eregi( $pattern, $email )) {
    	  return true;
      } else {
        return false;
      }   
	}
	
	function _validatePT($ptid) {
	//vaibhav
	if(JFolder::exists(JPATH_BASE.DS.'components'.DS.'com_xipt'))
	{
		$pt = XiptAPI::getProfiletypeInfo($ptid);
	}
		if ((count($pt) == 1) && $pt[0]->id == $ptid) {
			return true;
		}
		
		return false;
		
	}
	
	function createnewuser( $data )	
	{	
	
           $usersConfig=& JComponentHelper::getParams( 'com_users' );
		//$authorize 	= & JFactory::getACL();
		$user = clone(JFactory::getUser());
              
		$error_messages         = array();
		$fieldname		= array();
		$response               = NULL;
		$validated              = true;

		
		$pt = $this->_validatePT($data['jspt']);
		$pt = true;
		if (!$pt) {
			$validated  = false;
			$error_messages[] = array("id"=>1,"fieldname"=>"jspt","message"=>"Invalid profile type id");
		}
		
		if($data['email']=="") 
		{
			$validated  = false;
			$error_messages[] = array("id"=>1,"fieldname"=>"email","message"=>"Email cannot be blank");  
		} elseif( false == $this->isValidEmail( $data['email'] )) {
      $validated  = false;
			$error_messages[] = array("id"=>1,"fieldname"=>"email","message"=>"Please set valid email id eg.(example@gmail.com). Check 'email' field in request");
		}
	        
		if( $data['password']=="") 
		{
			$validated  = false;
			$error_messages[] = array("id"=>1,"fieldname"=>"password","message"=>"Password cannot be blank");
   	}
	        
		if( $data['name']=="") 
    {
    	$validated  = false;
			$error_messages[] = array("id"=>1,"fieldname"=>"name","message"=>"Name cannot be blank");
   	}	        
	        
  		
		if( true == $validated ) 
   	{ 
			jimport('joomla.filesystem.file');
			jimport('joomla.utilities.utility');
			CFactory::load( 'helpers' , 'image' );
			CFactory::load( 'libraries' , 'avatar' );
			
		
			$userModel =& CFactory::getModel('user');
			if($data['username']==""){
				$username = $data['email'];
			}else{
				$username = $data['username'];
			}
			
		       	$user->set('username', $username);
			$user->set('password', $data['password']);
			$user->set('name', $data['name']);
			$user->set('email', $data['email']);

			// password encryption
			$salt  = JUserHelper::genRandomPassword(32);
			$crypt = JUserHelper::getCryptedPassword($user->password, $salt);
			$user->password = "$crypt:$salt";

			//$user->set('usertype', 'Registered');
			// Initialize new usertype setting
		        // ECR - Joomla 1.6 change
		        $userConfig = JComponentHelper::getParams('com_users');
			// user group/type
			$user->set('id', '');
		        // Default to Registered.
			$defaultUserGroup = $userConfig->get('new_usertype', 2);
			//$newUsertype = $params->get('new_usertype', $usersConfig->get('new_usertype'));
			//if(!$newUsertype){
			//     $newUsertype = 'Registered';
			//}
			//$user->set('gid', $authorize->get_group_id( '', 'Registered', 'ARO' ));
			// ECR - Joomla 1.6 change
 			$user->set('usertype', 'Registered');
			//$user->set('usertype', $newUsertype);

 			// ECR - Joomla 1.6 change
   			 $user->set('groups', array($defaultUserGroup));
			//$user->set('gid', $authorize->get_group_id('', $newUsertype, 'ARO'));

			$date =& JFactory::getDate();
			$user->set('registerDate', $date->toMySQL());
	        
		        
     		$storage			= JPATH_ROOT . DS . 'images' . DS . 'avatar';

			//kapil
			if($data['avatar_imagedata']!="")
			{
			file_put_contents( JPATH_ROOT . DS . 'user_images/'.$data['username'],base64_decode  ( $data['imagedata']));//kapil
			 $imagedata = getimagesize(JPATH_ROOT . DS . 'user_images/'.$data['username']);
			rename( JPATH_ROOT . DS . 'user_images/'.$data['username'], JPATH_ROOT . DS . 'user_images/'.$data['username'].".".str_replace("image/","",$imagedata['mime']));
		  	$imgtype			= explode(".", $data['username'].'.'.str_replace("image/","",$imagedata['mime']));
				$imgmimetype = $imagedata['mime'];
     	}
			else if($data['avatar'] != "")
			{
	  		$imgtype			= explode(".", $data['avatar']);
			$imgmimetype = "image/".$imgtype[1];
			}


			//kapil
			if(isset($imgtype))
			{
	    	$storageImage			= $storage . DS . $imgtype[0] . '.'.$imgtype[1];
			$storageThumbnail		= $storage . DS . 'thumb_' . $imgtype[0] . '.'.$imgtype[1];
			$image				= 'images/avatar/' . $imgtype[0] . '.'.$imgtype[1];
			$thumbnail			= 'images/avatar/' . 'thumb_' . $imgtype[0] . '.'.$imgtype[1];
			$imgpath 			= JPATH_ROOT . DS . 'user_images/' .$imgtype[0] . '.'.$imgtype[1];
			$filetype			= $imgmimetype;
			}
			else
			{
			$image= 'components/com_community/assets/default.jpg';
			$thumbnail= 'components/com_community/assets/default_thumb.jpg';
			}
			if($data['avatar'] == ""&& $data['avatar_imagedata']=="")
			{
				$user->set('avatar',  'components/com_community/assets/default.jpg' );
				$user->set('thumb',  'components/com_community/assets/default_thumb.jpg' );
				
			} else {
			
				if(!file_exists($imgpath))
				{
			    	$validated  = false;						
					$error_messages[] = array("id"=>1,"fieldname"=>"avatar","message"=>"Invalid Avatar image path");
						
				} else {
					$user->set('avatar',  'images/avatar/'.$imgtype[0].'.'.$imgtype[1] );
					$user->set('avatar',  'images/avatar/thumb_'.$imgtype[0].'.'.$imgtype[1] );
					$imageMaxWidth	= 160;
					$imageSize		= cImageGetSize( $imgpath );
					// Generate full image
					if(!cImageResizePropotional( $imgpath , $storageImage , $filetype, $imageMaxWidth ) )
					{
						$error_messages[] = array("id"=>1,"fieldname"=>"avatar","message"=>"Fail: Error Moving Uploaded File");
						
					}
			
					// Generate thumbnail
					if(!cImageCreateThumb( $imgpath , $storageThumbnail ,$filetype ))
					{
						$error_messages[] = array("id"=>1,"fieldname"=>"avatar","message"=>"Fail: Error Moving Uploaded File");
						
					}
				}
			}
			
		

			if(!$user->save()) 
			{               	                        	        
				$error_messages[] = array("id"=>1,"fieldname"=>"usernameoremail","message"=>"username or email already in use."); 

			}
			else{
			

				$my 		= CFactory::getUser($user->id);	
				$userModel->setImage( $my->id , $image , 'avatar' );
				$userModel->setImage( $my->id , $thumbnail , 'thumb' );
			
				// Update the user object so that the profile picture gets updated.
				$my->set( '_avatar' , $image );
				$my->set( '_thumb'	, $thumbnail );
				
				////vaibhav
				if($data['field'])
				{
					$values=$data['field'];
					$profileModel	= CFactory::getModel( 'profile' );
					$profileModel->saveProfile($my->id, $values);
				}
				// Update Jomsocial profile type
				
				if(JFolder::exists(JPATH_BASE.DS.'components'.DS.'com_xipt'))
				{
					if ($user->id)
						XiptAPI::setUserProfiletype($user->id, $data['jspt']);
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
		    $response = array('id' => $user->id);
		}
		
			return $response;
		
  	}
  	
  	function onRestInfo() {
  	
			$vars[0]->name = 'name';
			$vars[0]->required = 1;
			$vars[0]->desc = 'Name of the user being created';
			
			$vars[1]->name = 'email';
			$vars[1]->required = 1;
			$vars[1]->desc = 'Email address';
			
			$vars[2]->name = 'username';
			$vars[2]->required = 0;
			$vars[2]->desc = 'Login name. If blank, email will be used';
			
			$vars[3]->name = 'password';
			$vars[3]->required = 1;
			$vars[3]->desc = 'User\'s password';
			
			return $vars;
			
  	}
	
}
