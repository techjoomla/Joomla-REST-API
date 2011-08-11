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
 * Example WallpostList Plugin
 */
class plgRestapiGroup_Discussion_Replies_list extends JPlugin 
{
	
	function plgRestapiGroup_Discussion_Replies_list( & $subject, $config ) 
	{	
		parent::__construct( $subject, $config );
	}

		
	function onRestCall($data) 
	{		
		// id, groupDiscussionId,groupId, userId, user Name & startdate/enddate
		$db 				= JFactory::getDBO();
		$start 				= JRequest::getInt('start', 0);
		$limit 				= JRequest::getInt('limit', 20);
		$id					= JRequest::getInt('id');
		$groupdiscussionid  = JRequest::getInt('groupdiscussionid');
		$groupid		    = JRequest::getInt('groupid');		
		$userid		    	= JRequest::getInt('userid');		
		$username			= JRequest::getVar('username');
		$startdt			= JRequest::getVar('startdate');
		$enddt				= JRequest::getVar('enddate');		
		$where				= array();
		$wheresql			= '';
		$flag 				= false;

    	if($groupid == 0) {
			$flag = false;
		}

		if ($id!="" && $id!=0 && $id) { 
			$where[] = 'w.id = '.$id;
			$flag = false;
		}
		
		if ($groupdiscussionid!="" && $groupdiscussionid!=0 && $groupdiscussionid) { 
			$where[] = 'w.contentid = '.$groupdiscussionid;
			$flag = false;
		}
				
		if ($groupid!="" && $groupid!=0 && $groupid) { 
			$where[] = 'd.groupid = '.$groupid;
			$flag = false;
		}

		if ($userid!="" && $userid!=0 && $userid) {
			$where[] = 'w.post_by = '.$userid;
			$flag = false;
		}
				
		if ($username) {
			$where[] = "u.username LIKE '%".$username."%'";
			$flag = false;
		}
				
		if($startdt && $enddt) {
			$where[] = ' date(w.date) >= "'.$startdt. '" AND date(w.date) <= "'.$enddt.'"';
			$flag = false;
		}
		
		$where[]= " w.contentid = d.id AND w.post_by = u.id AND w.published = 1 AND w.type = 'discussions' ";
		$where 	= (count($where) ? ' WHERE '.implode(' AND ', $where) : '');
		
		if(!$flag)
		{				
			$qry = "SELECT w.id AS wallpostid,w.contentid AS groupdiscussionid,w.post_by AS userid,w.date,w.comment 
						FROM #__community_wall AS w, #__community_groups_discuss AS d, #__users AS u					
						{$where} LIMIT {$start}, {$limit}";						
			$db->setQuery($qry);
			$dissreprec = $db->loadAssocList();
			$dissrepcount = count($dissreprec);

			if($dissrepcount) {				
				$dissrep = array('size' => $dissrepcount,'records'=>$dissreprec);
			} else {
				$dissrep = array('size' => 0);
			}
			
		} else {
			$dissrep = false;
		}
		
		if(!$dissrep) {
			$dissreperror[] = array("id"=>1,"fieldname"=>"id",'message'=>'Group Discussions reply post id is not available');
			$dissreperror[] = array("id"=>1,"fieldname"=>"groupid",'message'=>'Groupid is not available');
			$dissreperror[] = array("id"=>1,"fieldname"=>"groupdiscussionid",'message'=>'Group Discussion id is not available');
			$dissreperror[] = array("id"=>1,"fieldname"=>"userid",'message'=>'Userid is not available');
			$dissreperror[] = array("id"=>1,"fieldname"=>"username",'message'=>'Username is not available');
			$dissreperror[] = array("id"=>1,"fieldname"=>"startdate",'message'=>'Start date is not valid');
			$dissreperror[] = array("id"=>1,"fieldname"=>"enddate",'message'=>'End date is not valid');
			
			$dissrep = array('size' =>  -1,'errors'=>$dissreperror);
		}
                
		return $dissrep;
	}
			

} // end class
