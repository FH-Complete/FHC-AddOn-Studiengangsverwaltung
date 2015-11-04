<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/studiensemester.class.php');
//TODO functions from core?
require_once('../functions.php');

//TODO
$DEBUG = true;

$studiensemester = new studiensemester();
$studiensemester->getAll();

$data = $studiensemester->studiensemester;

returnAJAX(true, $data);
?>