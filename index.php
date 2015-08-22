<?php
namespace OmniFlow;

	if (isset($_REQUEST['command']))
	{
		$_REQUEST['action']=$_REQUEST['command'];
	}

	$req=$_REQUEST;

include_once("_startup.php");
include_once("config.php");
include_once "views/views.php";
include_once "controllers/controller.php";


$contr=new Controller();

$contr->Action($req);

?>