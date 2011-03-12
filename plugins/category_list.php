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
class plgRestapiCategory_List extends JPlugin {
	
	function plgRestapiCategory_List( & $subject, $config ) {	

		parent::__construct( $subject, $config );
	}


	function onRestCall() {

		$id = $this->createnewcatlist();

		return $id;
	}

	
	function createnewcatlist() 
	{	
		
		
                $database = &JFactory::getDBO();
				$qer	= "SELECT * FROM #__categories";
				$database->setQuery($qer);
				$result = $database->loadObjectList();
				header('Content-type: application/json');
				foreach($result as $res)
				{
				   if(is_numeric($res->section)){
					$qer	= "SELECT title FROM #__sections WHERE id=".$res->section;
					$database->setQuery($qer);
					$sectionname = $database->loadResult();
					?>
					{
						"sectionid": "<?php echo $res->section ;?>",
						"categoryid": "<?php echo $res->id; ?>",
						"sectionid": "<?php echo $sectionname; ?>",
						"categoryname": "<?php echo $res->title; ?>"
			
					}<?php
				}
				}
		jexit();
		
		
	}
}
