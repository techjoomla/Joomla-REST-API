<?php

require_once "RESTclient.php";
$inputs		        = array();
$inputs['categoryid'] 				= 1;
$inputs['email'] 					= 'joomladay@joomladay.in.th';
$inputs['website'] 					= "http://www.joomladay.in.th";
$inputs['discussordering'] 			= 1;
$inputs['photopermission'] 			= 1;
$inputs['videopermission'] 			= 1;
$inputs['grouprecentphotos'] 		= 1;
$inputs['grouprecentvideos'] 		= 1;
$inputs['wallnotification'] 		= 1;
$inputs['newmembernotification']  	= 1;
$inputs['joinrequestnotification']	= 1;


$inputs['auth_user']  			= 'ashwin';
$inputs['auth_pass']    		= '123456';

$inputs = array_merge($inputs, $_POST);
$url = "http://example.com/rest/group_create.json";
$rest= new RESTclient();
$rest->createRequest($url,"POST",$inputs);
$rest->sendRequest();
$output = $rest->getResponse();

echo $output;
prx($output);

