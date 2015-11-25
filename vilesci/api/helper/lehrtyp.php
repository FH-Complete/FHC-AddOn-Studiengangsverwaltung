<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/lehrtyp.class.php');
//TODO functions from core?
require_once('../functions.php');

//TODO
$DEBUG = true;

$lehrtyp = new lehrtyp();
$lehrtyp->getAll(true, true);

$data =  $lehrtyp->result;
returnAJAX(true, $data);
?>