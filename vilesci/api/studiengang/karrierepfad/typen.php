<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/karrierepfadAddonStgv.class.php');

require_once('../../functions.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if (!$rechte->isBerechtigt('addon/studiengangsverwaltung'))
{
	$error = array("message"=>"Sie haben keine Berechtigung für diese Aktion.", "detail"=>$rechte->errormsg);
	returnAJAX(false, $error);
}

$karrierepfad = new karrierepfadAddonStgv();
if (!$karrierepfad->getTypen())
{
	$error = array("message"=>"Fehler beim Laden der Karrierepfad-Typen.", "detail"=>$karrierepfad->errormsg);
	returnAJAX(false, $error);
}

returnAJAX(true, $karrierepfad->result);
