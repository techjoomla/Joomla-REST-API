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
 * Example Eventlist Create Plugin
 *
 * @package Joomla
 * @subpackage JFramework
 * @since 1.5
 */
class plgRestapiEvents_List extends JPlugin {
	
	function plgRestapiEvents_List( & $subject, $config ) {	

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
		$event_title		= JRequest::getVar('title');
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
			$where[] = 'e.id = ' . $id;
			$flag = false;
		}
		
		if ($event_title) {
			$where[] = "e.title LIKE '%".$event_title."%'";
			$flag = false;
		}
		
		if ($username) {
			$where[] = "u.username LIKE '%".$username."%'";
			$flag = false;
		}		
		
		if($startdt && $enddt){
			$where[] = ' date(e.created) >= "'.$startdt. '" AND date(e.created) <= "'.$enddt.'"';
			$flag = false;
		}
		
		$where[]= "e.creator = u.id AND e.published = 1 ";
		$where 	= (count($where) ? ' WHERE '.implode(' AND ', $where) : '');
		
		if(!$flag)
		{
			$qry = "SELECT e.id AS eventid,e.catid,e.title AS eventtitle,e.description,e.location,e.startdate,e.enddate,e.created,e.creator
						FROM #__community_events As e, #__users AS u 
						{$where} LIMIT {$start}, {$limit}";	
			$db->setQuery($qry);
			$eventrec = $db->loadAssocList();
			$eventcount = count($eventrec);

			if($eventcount) {			
				$eventlist = array('size' => $eventcount,'records'=>$eventrec);
			}else{
				$eventlist = array('size' => 0);
			}
			
		}else{
			$eventlist=false;
		}
		
		if(!$eventlist){
			$eventlisterror[] = array("id"=>1,"fieldname"=>"id",'message'=>'Group id is not available');
			$eventlisterror[] = array("id"=>1,"fieldname"=>"groupname",'message'=>'Group Name is not available');
			$eventlisterror[] = array("id"=>1,"fieldname"=>"username",'message'=>'Username is not available');
			$eventlisterror[] = array("id"=>1,"fieldname"=>"startdate",'message'=>'Start date is not valid');
			$eventlisterror[] = array("id"=>1,"fieldname"=>"enddate",'message'=>'End date is not valid');
			
			$eventlist = array('size' =>  -1,'errors'=>$eventlisterror);
		}
                
		return $eventlist;
	}
}
