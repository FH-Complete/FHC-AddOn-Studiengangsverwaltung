<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/studiensemester.class.php');

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

if (!in_array($method, array('getAll','getAkt', 'getAktorNext')))
	returnAJAX(false, "Method not allowed");

$studiensemester = new studiensemester();
if (method_exists($studiensemester, $method))
{
	if($method=='getAll')
		$studiensemester->$method('desc');
	else
		$studiensemester->$method();
	$data = $studiensemester->studiensemester;
}
else
{
	returnAJAX(false, "Methode ".$method." existiert nicht.");
}

returnAJAX(true, $data);
?>
