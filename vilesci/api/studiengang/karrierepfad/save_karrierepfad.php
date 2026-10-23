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

$karrierepfad = new karrierepfadAddonStgv();
if (isset($data->karrierepfad_id) && $data->karrierepfad_id != '')
{
	if (!$karrierepfad->load($data->karrierepfad_id))
	{
		$error = array("message"=>"Der Karrierepfad wurde nicht gefunden.", "detail"=>$karrierepfad->errormsg);
		returnAJAX(false, $error);
	}
	// kurzbz and Studiengang stay: the homepage links use the kurzbz
	$karrierepfad->new = false;
	$karrierepfad->updatevon = $uid;
}
else
{
	$karrierepfad->new = true;
	$karrierepfad->karrierepfad_kurzbz = isset($data->karrierepfad_kurzbz) ? trim($data->karrierepfad_kurzbz) : '';
	$karrierepfad->studiengang_kz = isset($data->studiengang_kz) ? $data->studiengang_kz : '';
	$karrierepfad->insertvon = $uid;
}

$bezeichnung = isset($data->bezeichnung_mehrsprachig) && is_array($data->bezeichnung_mehrsprachig) ? $data->bezeichnung_mehrsprachig : array();
$bezeichnung_mehrsprachig = is_array($karrierepfad->bezeichnung_mehrsprachig) ? $karrierepfad->bezeichnung_mehrsprachig : array();
foreach (array('German', 'English') as $sprache)
	$bezeichnung_mehrsprachig[$sprache] = isset($bezeichnung[$sprache]) ? trim($bezeichnung[$sprache]) : '';

$karrierepfad->bezeichnung_mehrsprachig = $bezeichnung_mehrsprachig;
$karrierepfad->karrierepfad_typ_kurzbz = isset($data->karrierepfad_typ_kurzbz) ? $data->karrierepfad_typ_kurzbz : '';
$karrierepfad->sort = isset($data->sort) ? trim($data->sort) : '';
$karrierepfad->aktiv = isset($data->aktiv) ? parseBoolean($data->aktiv) : false;
$karrierepfad->studienplan_ids = isset($data->studienplan_ids) && is_array($data->studienplan_ids) ? array_values($data->studienplan_ids) : array();

if ($karrierepfad->save())
{
	returnAJAX(true, $karrierepfad->karrierepfad_id);
}
else
{
	$error = array("message"=>"Fehler beim Speichern des Karrierepfads.", "detail"=>$karrierepfad->errormsg);
	returnAJAX(false, $error);
}
