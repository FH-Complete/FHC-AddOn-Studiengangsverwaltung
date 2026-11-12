<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/karrierepfadAddonStgv.class.php');

require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if (!$berechtigung->isBerechtigt("stgv/editKarrierepfad", null, "suid"))
{
	$error = array("message"=>"Sie haben nicht die Berechtigung um Karrierepfade anzulegen oder zu editieren.", "detail"=>"stgv/editKarrierepfad");
	returnAJAX(false, $error);
}

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];

$studiengang_kz = isset($data->studiengang_kz) ? $data->studiengang_kz : '';
$karrierepfad_ids = isset($data->karrierepfad_ids) && is_array($data->karrierepfad_ids) ? array_values($data->karrierepfad_ids) : array();

$karrierepfad = new karrierepfadAddonStgv();
if (!$karrierepfad->saveSort($studiengang_kz, $karrierepfad_ids, $uid))
{
	$error = array("message"=>"Fehler beim Speichern der Reihenfolge.", "detail"=>$karrierepfad->errormsg);
	returnAJAX(false, $error);
}

returnAJAX(true, "Reihenfolge gespeichert.");
