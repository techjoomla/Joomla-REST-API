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
class plgRestapiGroup_List extends JPlugin {
	
	function plgRestapiGroup_List( & $subject, $config ) {	

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
		$group_name			= JRequest::getVar('groupname');
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
			$where[] = 'g.id = ' . $id;
			$flag = false;
		}
		
		if ($group_name) {
			$where[] = "g.name LIKE '%".$group_name."%'";
			$flag = false;
		}
		
		if ($username) {
			$where[] = "u.username LIKE '%".$username."%'";
			$flag = false;
		}		
		
		if($startdt && $enddt){
			$where[] = ' date(g.created) >= "'.$startdt. '" AND date(g.created) <= "'.$enddt.'"';
			$flag = false;
		}
		
		$where[]= " g.ownerid = u.id AND g.published = 1 ";
		$where 	= (count($where) ? ' WHERE '.implode(' AND ', $where) : '');
		
		if(!$flag)
		{
			$qry = "SELECT g.id AS groupid,g.name AS groupname,g.description,g.email,g.website,g.created
						FROM #__community_groups As g, #__users AS u 
						{$where} LIMIT {$start}, {$limit}";	
			$db->setQuery($qry);
			$grouprec = $db->loadAssocList();
			$groupcount = count($grouprec);

			if($groupcount) {			
				$grouplist = array('size' => $groupcount,'records'=>$grouprec);
			}else{
				$grouplist = array('size' => 0);
			}
			
		}else{
			$grouplist=false;
		}
		
		if(!$grouplist){
			$grouplisterror[] = array("id"=>1,"fieldname"=>"id",'message'=>'Group id is not available');
			$grouplisterror[] = array("id"=>1,"fieldname"=>"groupname",'message'=>'Group Name is not available');
			$grouplisterror[] = array("id"=>1,"fieldname"=>"username",'message'=>'Username is not available');
			$grouplisterror[] = array("id"=>1,"fieldname"=>"startdate",'message'=>'Start date is not valid');
			$grouplisterror[] = array("id"=>1,"fieldname"=>"enddate",'message'=>'End date is not valid');
			
			$grouplist = array('size' =>  -1,'errors'=>$grouplisterror);
		}
                
		return $grouplist;
	}
}
