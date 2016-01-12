<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/konto.class.php');

require_once('../functions.php');

$konto = new konto();
$konto->getBuchungstyp(true);
$data = $konto->result;

returnAJAX(true, $data);