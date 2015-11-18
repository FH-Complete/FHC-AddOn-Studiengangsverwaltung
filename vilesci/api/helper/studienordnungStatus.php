<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../include/StudienordnungStatus.class.php');
//TODO functions from core?
require_once('../functions.php');

//TODO
$DEBUG = true;

$method = filter_input(INPUT_GET, "method");

if(is_null($method))
{
    $method = "getAll";   
}
elseif(($method == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}



$status = new StudienordnungStatus();
if (method_exists($status, $method))
{
	$status->$method(); 
	$data = $status->result;
}
else
{
	returnAJAX(false, "Methode ".$method." existiert nicht.");
}

returnAJAX(true, $data);
?>