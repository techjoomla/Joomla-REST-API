<?php
error_reporting(E_ALL);
//ini_set('display_errors', 'On');

require_once 'RESTclient.php';
require_once 'functions.php';
$inputs		        = array();

$inputs['auth_user']  = 'admin';
$inputs['auth_pass']  = 'abcd1234';

$inputs["jspt"]	= 2;
$inputs["email"] 	= "joomdaday6@pune.com";
$inputs["name"]	= "Joomla";
$inputs["password"]	= "123456";

$inputs = array_merge($inputs, $_POST);

$url = "http://example.com/rest/user_create.json";
$rest= new RESTclient();
$rest->createRequest($url,"POST",$inputs);
$rest->sendRequest();
$output = $rest->getResponse();

echo '<h2>Create</h2>';
echo $output;
$op = json_decode($output);
prx($op);
// End create

// Read
$url = "http://example.com/rest/user_list.json?id=" . $op->id;
$rest= new RESTclient();
$rest->createRequest($url,"GET",array());
$rest->sendRequest();
$output = $rest->getResponse();

echo '<h2>Read</h2>';
echo $output;
$op = json_decode($output);
prx($op);
// End Read

// update
$inputs['userid'] = $op->records[0]->id;
$inputs['jspt'] = 1;
$inputs["name"]	= "Ashwin Date";

$url = "http://example.com/rest/user_update.json";
$rest= new RESTclient();
$rest->createRequest($url,"POST",$inputs);
$rest->sendRequest();
$output = $rest->getResponse();

echo '<h2>Update</h2>';
echo $output;
$op = json_decode($output);
prx($op);
// End update

// Read
$url = "http://example.com/rest/user_list.json?id=" . $op->id;
$rest= new RESTclient();
$rest->createRequest($url,"GET",array());
$rest->sendRequest();
$output = $rest->getResponse();

echo '<h2>Read</h2>';
echo $output;
$op = json_decode($output);
prx($op);
// End Read

// Delete
$inputs['id'] = $op->records[0]->id;
$url = "http://example.com/rest/user_delete.json";
$rest= new RESTclient();
$rest->createRequest($url,"POST",$inputs);
$rest->sendRequest();
$output = $rest->getResponse();

echo '<h2>Delete</h2>';
echo $output;
$op = json_decode($output);
prx($op);
// End Delete
