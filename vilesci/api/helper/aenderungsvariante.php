<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../include/Aenderungsvariante.class.php');

require_once('../functions.php');

$method = filter_input(INPUT_GET, "method");

if(is_null($method))
{
    $method = "getAll";   
}
elseif(($method == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$aenderungsvariante = new Aenderungsvariante();
if (method_exists($aenderungsvariante, $method))
{
	$aenderungsvariante->$method(); 
	$data = $aenderungsvariante->result;
}
else
{
	returnAJAX(false, "Methode ".$method." existiert nicht.");
}

returnAJAX(true, $data);
?>