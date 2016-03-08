<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/studienjahr.class.php');

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

$studienjahr = new studienjahr();
if (method_exists($studienjahr, $method))
{
	$studienjahr->$method(); 
	$data = $studienjahr->result;
}
else
{
	returnAJAX(false, "Methode ".$method." existiert nicht.");
}

returnAJAX(true, $data);
?>