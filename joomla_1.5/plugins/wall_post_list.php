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
class plgRestapiWall_Post_list extends JPlugin 
{
	
	function plgRestapiWall_Post_list( & $subject, $config ) 
	{	
		parent::__construct( $subject, $config );
	}

		
	function onRestCall($data) 
	{
		//wallPostId, groupId, userId, user Name & startdate/enddate
		$db 				= JFactory::getDBO();
		$start 				= JRequest::getInt('start', 0);
		$limit 				= JRequest::getInt('limit', 20);
		$wallpostid			= JRequest::getInt('wallpostid');
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

		if ($wallpostid!="" && $wallpostid!=0 && $wallpostid) { 
			$where[] = 'w.id = '.$wallpostid;
			$flag = false;
		}
				
		if ($groupid!="" && $groupid!=0 && $groupid) { 
			$where[] = 'w.contentid = '.$groupid;
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
		
		$where[]= " w.post_by = u.id AND w.published = 1 AND w.type = 'groups' ";
		$where 	= (count($where) ? ' WHERE '.implode(' AND ', $where) : '');
		
		if(!$flag)
		{				
			$qry = "SELECT w.id AS wallpostid, w. contentid AS groupid, w.post_by AS userid, w.comment, w.date
						FROM #__community_wall As w, #__users AS u 
						{$where} LIMIT {$start}, {$limit}";						
			$db->setQuery($qry);
			$wallrec = $db->loadAssocList();
			$wallscount = count($wallrec);

			if($wallscount) {				
				$walls = array('size' => $wallscount,'records'=>$wallrec);
			} else {
				$walls = array('size' => 0);
			}
			
		} else {
			$walls = false;
		}
		
		if(!$walls) {
			$wallserror[] = array("id"=>1,"fieldname"=>"groupid",'message'=>'Groupid is not available');
			$wallserror[] = array("id"=>1,"fieldname"=>"wallpostid",'message'=>'Wall post id is not available');
			$wallserror[] = array("id"=>1,"fieldname"=>"userid",'message'=>'Userid is not available');
			$wallserror[] = array("id"=>1,"fieldname"=>"username",'message'=>'Username is not available');
			$wallserror[] = array("id"=>1,"fieldname"=>"startdate",'message'=>'Start date is not valid');
			$wallserror[] = array("id"=>1,"fieldname"=>"enddate",'message'=>'End date is not valid');
			
			$walls = array('size' =>  -1,'errors'=>$wallserror);
		}
                
		return $walls;
	}
			

} // end class
