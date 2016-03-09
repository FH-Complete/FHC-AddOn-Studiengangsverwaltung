<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');

require_once('../../functions.php');
require_once('../../../../include/studiengangsgruppe.class.php');

$studiengangsgruppe = new studiengangsgruppe();
$studiengangsgruppe->getAll();
$data = $studiengangsgruppe->result;

returnAJAX(true, $data);