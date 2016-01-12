<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/organisationsform.class.php');

require_once('../functions.php');

$orgform = new organisationsform();
$orgform->getAll();

$data =  $orgform->result;
returnAJAX(true, $data);
?>