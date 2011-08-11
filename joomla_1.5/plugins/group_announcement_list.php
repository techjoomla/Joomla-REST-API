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
class plgRestapiGroup_Announcement_list extends JPlugin {
	
	function plgRestapiGroup_Announcement_list( & $subject, $config ) {	

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
			$where[] = 'w.id = ' . $id;
			$flag = false;
		}
		
		if ($group_id) {
			$where[] = 'w.groupid = ' . $db->Quote($group_id);
			$flag = false;
		}
		
		if ($user_id) {
			$where[] = 'w.created_by = ' . $db->Quote($user_id);
			$flag = false;
		}
	
		if ($username) {
			$where[] = "u.username LIKE '%".$username."%'";
			$flag = false;
		}		
		
		if($startdt && $enddt){
			$where[] = ' date(w.date) >= "'.$startdt. '" AND date(w.date) <= "'.$enddt.'"';
			$flag = false;
		}
		
		$where[]= " w.created_by = u.id AND w.published = 1 ";
		$where 	= (count($where) ? ' WHERE '.implode(' AND ', $where) : '');
		
		if(!$flag)
		{
			$qry = "SELECT w.id AS announcementid,w.groupid AS groupid,w.created_by AS userid,w.title,w.message,w.date 
						FROM #__community_groups_bulletins As w, #__users AS u 
						{$where} LIMIT {$start}, {$limit}";	
			$db->setQuery($qry);
			$annorec = $db->loadAssocList();
			$annocount = count($annorec);

			if($annocount) {			
				$announcements = array('size' => $annocount,'records'=>$annorec);
			}else{
				$announcements = array('size' => 0);
			}
			
		}else{
			$announcements=false;
		}
		
		if(!$announcements){
			$announcementserror[] = array("id"=>1,"fieldname"=>"id",'message'=>'Announcement id is not available');
			$announcementserror[] = array("id"=>1,"fieldname"=>"groupid",'message'=>'Group id is not available');
			$announcementserror[] = array("id"=>1,"fieldname"=>"userid",'message'=>'Userid is not available');
			$announcementserror[] = array("id"=>1,"fieldname"=>"username",'message'=>'Username is not available');
			$announcementserror[] = array("id"=>1,"fieldname"=>"startdate",'message'=>'Start date is not valid');
			$announcementserror[] = array("id"=>1,"fieldname"=>"enddate",'message'=>'End date is not valid');
			
			$announcements = array('size' =>  -1,'errors'=>$announcementserror);
		}
                
		return $announcements;
	}
}
