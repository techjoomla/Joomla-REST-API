<?php
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
if(!class_exists('SearchModelSearch')) {
	require(JPATH_SITE.DS.'components'.DS.'com_search'.DS.'models'.DS.'search.php');
}
if(!class_exists('JSite')) {
	require(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_search'.DS.'helpers'.DS.'site.php');
}
if(!class_exists('SearchHelper')) {
	require(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_search'.DS.'helpers'.DS.'search.php');
}

class plgRestapiSearch extends JPlugin {
	
	function plgRestapiSearch( & $subject, $config ) {	
		parent::__construct( $subject, $config );
	}


	function onRestCall() {
		
		$searchword = JRequest::getVar('keyword');
		$match 		= JRequest::getVar('match');

		// Get the results
		$results = $this->doSearch();
		
		// Process them like Joomla does
		for ($i=0; $i < count($results); $i++)
		{
			$row = &$results[$i]->text;

			if ($match == 'exact')
			{
				$searchwords = array($searchword);
				$needle = $searchword;
			}
			else
			{
				$searchwords = preg_split("/\s+/u", $searchword);
				$needle = $searchwords[0];
			}

			$row = SearchHelper::prepareSearchContent( $row, 200, $needle );
			$searchwords = array_unique( $searchwords );
/*			$searchRegex = '#(';
			$x = 0;
			foreach ($searchwords as $k => $hlword)
			{
				$searchRegex .= ($x == 0 ? '' : '|');
				$searchRegex .= preg_quote($hlword, '#');
				$x++;
			}
			$searchRegex .= ')#iu';
*/
			$result =& $results[$i];
			if ($result->created) {
				$created = $result->created;
			}
			else {
				$created = '';
			}

			$result->created	= $created;
			$result->count		= $i + 1;
		}
		return $results;
	}

	
	function doSearch() 
	{
		$searchmodel = new SearchModelSearch();//JFactory::getModel('search');
		$results = $searchmodel->getData();
		
		return $results;
		
	}
}
