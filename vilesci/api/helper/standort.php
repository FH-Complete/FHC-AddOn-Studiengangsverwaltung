<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/standort.class.php');

require_once('../functions.php');

$standort = new standort();
$standort->getAllStandorteWithOrt();

$data =  $standort->result;
returnAJAX(true, $data);
?>