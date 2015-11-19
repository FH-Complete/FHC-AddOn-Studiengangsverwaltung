<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/organisationseinheit.class.php');
//TODO functions from core?
require_once('../functions.php');

//TODO
$DEBUG = true;

$oe = new organisationseinheit();
$oe->getAll(true, true);
//TODO nur Insitute anzeigen?
//$oe->getByTyp("Institut");

$data =  $oe->result;
returnAJAX(true, $data);
?>