<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/konto.class.php');
//TODO functions from core?
require_once('../functions.php');

//TODO
$DEBUG = true;

$konto = new konto();
$konto->getBuchungstyp(true);
$data = $konto->result;

returnAJAX(true, $data);