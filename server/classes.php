<?php
class ArrayToXML
{
	public static function toXml($data, $rootNodeName = 'data', $xml=null)
	{
		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		if (ini_get('zend.ze1_compatibility_mode') == 1)
		{
			ini_set ('zend.ze1_compatibility_mode', 0);
		}
		
		if ($xml == null)
		{
			$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
		}
		
		// loop through the data passed in.
		foreach($data as $key => $value)
		{
			// no numeric keys in our xml please!
			if (is_numeric($key))
			{
				// make string key...
				$key = "child_". (string) $key;
			}
			
			// replace anything not alpha numeric
			$key = preg_replace('/[^a-z]/i', '', $key);
			
			// if there is another array found recrusively call this function
			if (is_array($value))
			{
				$node = $xml->addChild($key);
				// recrusive call.
				ArrayToXML::toXml($value, $rootNodeName, $node);
			}
			else 
			{
				// add single node.
                                $value = htmlentities($value);
				$xml->addChild($key,$value);
			}
			
		}
		// pass back as string. or simple xml object if you want!
		return $xml->asXML();
	}
}

class JoomlaRest {

	function getList($arr) {
		$html = '<table width="100%" bgcolor="#f2f2f2" cellpadding="5">';
		foreach ($arr as $method) {
			$html .= '<tr>' . "\n";

			$html .= "<td><em>{$method->element}</em><br />{$method->name}</td>";

			$html .= '</tr>' . "\n";
		}
		
		$html .= '</table>';
		
		return $html;
	}
	
	function getInfo($method) {
	
		$html = '';
		$html .= '<html><head><title>API Information</title>' . "\n";
		$html .= '<style type="text/css">body{font-family: Arial; font-size: 10px}</style>' . "\n";
		$html .= '</head><body>' . "\n";
		
		$html .= '<table width="100%" bgcolor="#f2f2f2" cellpadding="5">';
		foreach ($method as $vars) {
			$html .= '<tr>' . "\n";
			
			if ($vars->required) { $vars->name = '<strong>' . $vars->name . '*</strong>'; }
			$html .= "<td>{$vars->name}<br /><small>{$vars->desc}</small></td>";

			$html .= '</tr>' . "\n";
		}
		
		$html .= '</table></body></html>';
		
		return $html;
	
	}
}
