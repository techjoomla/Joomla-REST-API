<?php
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
//require_once( JPATH_SITE .DS.'components'.DS.'com_xipt'.DS.'api.xipt.php');

class plgRestapiUser_List extends JPlugin
{


	function plgRestapiUser_List(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/*function _extendUsers($usersdata) {
		
		foreach ($usersdata as $k => $v) {
			$usersdata[$k]['jspt'] = XiptAPI::getUserProfiletype($v['id']);
			$usersdata[$k]['jsptname'] = XiptAPI::getUserProfiletype($v['id'], 'name');
		}
		
		return $usersdata;
	}*/

	function onRestCall ($data) {
		
		$db 				= JFactory::getDBO();
		$start 				= JRequest::getInt('start', 0);
		$limit 				= JRequest::getInt('limit', 20);
		$id		     		= JRequest::getInt('id');
		$email				= JRequest::getVar('email');
		$name				= JRequest::getVar('name');
		$username			= JRequest::getVar('username');
		$startdt			= JRequest::getVar('startdate');
		$enddt				= JRequest::getVar('enddate');
		$modifieddt			= JRequest::getVar('modifieddate');	
		$where				= array();
		$wheresql			= '';
		$flag 				= false;

    		if($id == 0)
		{
			$flag = true;
		}
		
		if ($id!="" && $id!=0 && $id) {
			$where[] = 'id = ' . $id;
			$flag = false;
		}
		
		if ($email) {
			$where[] = 'email = ' . $db->Quote($email);
			$flag = false;
		}
		
		if ($name) {
			$where[] = "name LIKE '%".$name."%'";
			$flag = false;
		}
		
		if ($username) {
			$where[] = "username LIKE '%" . $username . "%'";
			$flag = false;
		}
		
		if($startdt && $enddt){
			//$where[] = ' date(registerDate) >= "'.$startdt. '" AND date(registerDate) <= "'.$enddt.'"';
			$where[] = ' registerDate BETWEEN '.$db->quote($startdt).' AND '.$db->quote($enddt);
			$flag = false;
		}

		if($startdt) {
			$where[] = ' date(registerDate) ='.$db->quote($startdt);
			$flag = false;
		}
				
		if($modifieddt){
			$where[] = "date(lastvisitDate) LIKE '%".$modifieddt. "%'";
			$flag = false;
		}
		
		if (count($where)) {
			$wheresql = 'WHERE block = 0 AND ' . implode(' AND ', $where);
		}
		
		if(!$flag){
			$qry = "SELECT id, name, username, email, registerDate as registerdate, usertype 
			FROM #__users {$wheresql} LIMIT {$start}, {$limit}";
		
			$db->setQuery($qry);
			$usersrec = $db->loadAssocList();
			$userscount = count($usersrec);

			if($userscount){
				//$usersrec = $this->_extendUsers($usersrec);
				$users = array('size' => $userscount,'records'=>$usersrec);
			}else{
				$users = array('size' => 0);
			}
			
		}else{
			$users=false;
		}
		
		if(!$users){
			$userserror[] = array("id"=>1,"fieldname"=>"name",'message'=>'name is not available');
			$userserror[] = array("id"=>1,"fieldname"=>"id",'message'=>'id is not available');
			$userserror[] = array("id"=>1,"fieldname"=>"email",'message'=>'Email id is not available');
			$userserror[] = array("id"=>1,"fieldname"=>"username",'message'=>'Username is not available');
			$userserror[] = array("id"=>1,"fieldname"=>"startdate",'message'=>'Start date is not valid');
			$userserror[] = array("id"=>1,"fieldname"=>"enddate",'message'=>'End date is not valid');
			$userserror[] = array("id"=>1,"fieldname"=>"modifieddate",'message'=>'Modified date is not valid');
			$users = array('size' => -1,'errors'=>$userserror);
		}
                
		return $users;
	}
	

}
