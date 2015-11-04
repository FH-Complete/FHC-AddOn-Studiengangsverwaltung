<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/studiengang.class.php');
//TODO functions from core?
require_once('../functions.php');

//TODO
$DEBUG = true;

$studiengang = new studiengang();
$studiengang->getAll("kurzbzlang");

$data =  $studiengang->result;
returnAJAX(true, $data);
?>