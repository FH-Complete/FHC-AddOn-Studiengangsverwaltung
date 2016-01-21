<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/akadgrad.class.php');

require_once('../functions.php');

$akadgrad = new akadgrad();
$akadgrad->getAll();

$data =  array_unique($akadgrad->result);
returnAJAX(true, $data);
?>