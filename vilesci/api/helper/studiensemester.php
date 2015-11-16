<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/studiensemester.class.php');
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



$studiensemester = new studiensemester();
if (method_exists($studiensemester, $method))
{
	$studiensemester->$method(); 
	$data = $studiensemester->studiensemester;
}
else
{
	returnAJAX(false, "Methode ".$method." existiert nicht.");
}

returnAJAX(true, $data);
?>