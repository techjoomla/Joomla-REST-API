<?php


// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
jimport('joomla.user.helper');
jimport( 'joomla.application.component.helper' );
require_once( JPATH_SITE .DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php');
require_once( JPATH_SITE .DS.'libraries'.DS.'joomla'.DS.'filesystem'.DS.'folder.php');
require_once( JPATH_SITE .DS.'components'.DS.'com_xipt'.DS.'api.xipt.php');


class plgRestapiUser_Update extends JPlugin
{


	function plgRestapiUser_Update( & $subject, $config ) {
		parent::__construct( $subject, $config );
	}


	function onRestCall( $data ) {
		$id = $this->updateuser( $data );
		return $id;
	}
	
	function onRestInfo() {
		//$data['username']
	}
	
	function _validatePT($ptid) {
	
		$pt = XiptAPI::getProfiletypeInfo($ptid);

		if ((count($pt) == 1) && $pt[0]->id == $ptid) {
			return true;
		}
		
		return false;
		
	}
		
	function isValidEmail( $email ) {
	
                $pattern = "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$";

                if ( eregi( $pattern, $email )) {
                        return true;
                } else {
                        return false;
                }   
        } 
	
	function updateuser( $data )
	{		

		$authorize 	= & JFactory::getACL();
		$user = clone(JFactory::getUser());
              
                $error_messages         = array();
                $response               = NULL;
                $validated              = true;

		$pt = $this->_validatePT($data['jspt']);
		
		if (!$pt) {
			$validated  = false;
			$error_messages[] = array("id"=>1,"fieldname"=>"jspt","message"=>"Invalid profile type id");
		}
		          	
    if( true == empty( $user->email ) || $data['email']=="") 
		{
	   	$validated  = false;
			$error_messages[] = array("id"=>1,"fieldname"=>"email","message"=>"Email cannot be blank");
   	} elseif( false == $this->isValidEmail( $user->email )) {
	   	$validated  = false;
			$error_messages[] = array("id"=>1,"fieldname"=>"email","message"=>"Please set valid email id eg.(example@gmail.com). Check 'email' field in request");
    }

	  if( $data['userid']=="" || $data['userid']=="0") 
		{
    	$validated  = false;
			$error_messages[] = array("id"=>1,"fieldname"=>"userid","message"=>"Userid cannot be blank");
    }

    if( true == empty( $user->password ) || $data['password']=="") 
		{
    	$validated  = false;
      $error_messages[] = array("id"=>1,"fieldname"=>"password","message"=>"Password cannot be blank");
	  }
	        
    if( true == empty( $user->name ) || $data['name']=="") 
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
			} else {
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
			if($data['usertype']==""){
				$usertype = 'Registered';
			}else{
				$usertype = $data['usertype'];
			}
			// user group/type
			$user->set('id', $data['userid']);
			$user->set('usertype', $usertype);
			$user->set('gid', $authorize->get_group_id( '', $usertype, 'ARO' ));

			$date =& JFactory::getDate();
			$user->set('registerDate', $date->toMySQL());
	       
			$storage			= JPATH_ROOT . DS . 'images' . DS . 'avatar';
			$imgtype			= explode(".", $data['avatar']);
			$storageImage			= $storage . DS . $imgtype[0] . '.'.$imgtype[1];
			$storageThumbnail		= $storage . DS . 'thumb_' . $imgtype[0] . '.'.$imgtype[1];
			$image				= 'images/avatar/' . $imgtype[0] . '.'.$imgtype[1];
			$thumbnail			= 'images/avatar/' . 'thumb_' . $imgtype[0] . '.'.$imgtype[1];
			$imgpath 			= JPATH_ROOT . DS . 'user_images/' .$imgtype[0] . '.'.$imgtype[1];
			$filetype			= 'image/'.$imgtype[1];
				
				
			if($data['avatar']=="")
			{
				
				$user->set('avatar',  'components/com_community/assets/default.jpg' );
				$user->set('thumb',  'components/com_community/assets/default_thumb.jpg' );
				
			} else {
			
				if(!file_exists($imgpath))
				{
						$error_messages[] = array("id"=>1,"fieldname"=>"avatar","message"=>"Invalid Avatar image name.Modify 'avatar'name");
				}
				else
				{
                                       
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
			
			if(!file_exists($imgpath))
			{
			}else{			
				if(!$user->save()) 
				{               	                        	        
					//$error_messages[] = "Fail: This username/password already in use. Please try another."; 
		
				}
			}			
				
				$my 		= CFactory::getUser($data['userid']);	
				$userModel->setImage( $my->id , $image , 'avatar' );
				$userModel->setImage( $my->id , $thumbnail , 'thumb' );
	
				// Update the user object so that the profile picture gets updated.
				$my->set( '_avatar' , $image );
				$my->set( '_thumb'	, $thumbnail );
				
			// Update Jomsocial profile type
			XiptAPI::setUserProfiletype($user->id, $data['jspt']);	
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

	
}
