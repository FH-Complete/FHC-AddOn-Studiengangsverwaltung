<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/studiensemester.class.php');

require_once('../functions.php');

$studiensemester = new studiensemester();

if(!$studiensemester->getFutureStudiensemester(NULL, 15))
{
	returnAJAX(false, $studiensemester->errormsg);
}

$data = $studiensemester->studiensemester;
returnAJAX(true, $data);
?>