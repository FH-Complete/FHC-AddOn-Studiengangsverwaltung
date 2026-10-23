<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/karrierepfadAddonStgv.class.php');

require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if (!$berechtigung->isBerechtigt("stgv/deleteKarrierepfad", null, "suid"))
{
	$error = array("message"=>"Sie haben nicht die Berechtigung um Karrierepfade zu löschen.", "detail"=>"stgv/deleteKarrierepfad");
	returnAJAX(false, $error);
}

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];

$karrierepfad = new karrierepfadAddonStgv();
if ($karrierepfad->delete(isset($data->karrierepfad_id) ? $data->karrierepfad_id : null))
{
	returnAJAX(true, "Karrierepfad erfolgreich gelöscht.");
}
else
{
	$error = array("message"=>"Fehler beim Löschen des Karrierepfads.", "detail"=>$karrierepfad->errormsg);
	returnAJAX(false, $error);
}
