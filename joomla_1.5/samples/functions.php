<?php

function prx($array, $print=true) {
	
$op = '<pre>';
$op .= print_r($array, true);
$op .= '</pre>';

if ($print)
	echo $op;
else
	return $op;

}
