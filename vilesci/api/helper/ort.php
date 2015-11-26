<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/ort.class.php');
//TODO functions from core?
require_once('../functions.php');

//TODO
$DEBUG = true;

$ort = new ort();
if ($ort->getActive())
{
	$data = $ort->result;
}
else
{
	returnAJAX(false, "Methode ".$method." existiert nicht.");
}

returnAJAX(true, $data);
?>