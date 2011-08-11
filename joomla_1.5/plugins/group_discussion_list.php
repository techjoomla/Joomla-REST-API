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
 * Example Group Discussion List Plugin
 *
 * @package Joomla
 * @subpackage JFramework
 * @since 1.5
 */
class plgRestapiGroup_Discussion_List extends JPlugin {
	
	function plgRestapiGroup_Discussion_List( & $subject, $config ) {	

		parent::__construct( $subject, $config );
	}


	function onRestInfo() {

	}
	
	function onRestCall( $data ) 
	{	
		$db 				= JFactory::getDBO();
		$start 				= JRequest::getInt('start', 0);
		$limit 				= JRequest::getInt('limit', 20);
		$id		     		= JRequest::getInt('id');
		$group_id			= JRequest::getInt('groupid');
		$user_id			= JRequest::getInt('userid');
		$username			= JRequest::getVar('username');
		$startdt			= JRequest::getVar('startdate');
		$enddt				= JRequest::getVar('enddate');
		
		$where				= array();
		$wheresql			= '';
		$flag 				= false;

    		if($id == 0)
		{
			$flag = false;
		}
		
		if ($id!="" && $id!=0 && $id) {
			$where[] = 'd.id = ' . $id;
			$flag = false;
		}
		
		if ($group_id) {
			$where[] = 'd.groupid = ' . $db->Quote($group_id);
			$flag = false;
		}
		
		if ($user_id) {
			$where[] = 'd.creator = ' . $db->Quote($user_id);
			$flag = false;
		}
	
		if ($username) {
			$where[] = "u.username LIKE '%".$username."%'";
			$flag = false;
		}		
		
		if($startdt && $enddt){
			$where[] = ' date(d.lastreplied) >= "'.$startdt. '" AND date(d.lastreplied) <= "'.$enddt.'"';
			$flag = false;
		}
		
		$where[]= " d.creator = u.id";
		$where 	= (count($where) ? ' WHERE '.implode(' AND ', $where) : '');
		
		if(!$flag)
		{
			$qry = "SELECT d.id AS discussionid,d.groupid AS groupid,d.creator AS userid,d.title,d.message,d.lastreplied 
						FROM #__community_groups_discuss As d, #__users AS u 
						{$where} LIMIT {$start}, {$limit}";	
			$db->setQuery($qry);
			$discussrec = $db->loadAssocList();
			$discusscount = count($discussrec);

			if($discusscount) {			
				$discuss = array('size' => $discusscount,'records'=>$discussrec);
			}else{
				$discuss = array('size' => 0);
			}
			
		}else{
			$discuss=false;
		}
		
		if(!$discuss){
			$discusserror[] = array("id"=>1,"fieldname"=>"id",'message'=>'Discuss id is not available');
			$discusserror[] = array("id"=>1,"fieldname"=>"groupid",'message'=>'Group id is not available');
			$discusserror[] = array("id"=>1,"fieldname"=>"userid",'message'=>'Userid is not available');
			$discusserror[] = array("id"=>1,"fieldname"=>"username",'message'=>'Username is not available');
			$discusserror[] = array("id"=>1,"fieldname"=>"startdate",'message'=>'Start date is not valid');
			$discusserror[] = array("id"=>1,"fieldname"=>"enddate",'message'=>'End date is not valid');
			
			$discuss = array('size' =>  -1,'errors'=>$discusserror);
		}
                
		return $discuss;
	}
}
