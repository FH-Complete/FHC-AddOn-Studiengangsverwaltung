<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/akadgrad.class.php');
//TODO functions from core?
require_once('../functions.php');

//TODO
$DEBUG = true;

$akadgrad = new akadgrad();
$akadgrad->getAll();

$data =  $akadgrad->result;
returnAJAX(true, $data);
?>