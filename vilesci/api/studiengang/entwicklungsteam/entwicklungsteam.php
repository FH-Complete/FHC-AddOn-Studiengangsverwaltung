<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/dms.class.php');
require_once('../../../../../../include/entwicklungsteam.class.php');
require_once('../../../../../../include/besqualcode.class.php');
require_once('../../functions.php');

header("Content-Type: application/json");
$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if (!$rechte->isBerechtigt('addon/studiengangsverwaltung'))
{
	$error = array("message" => "Sie haben keine Berechtigung für diese Aktion.", "detail" => $rechte->errormsg);
	returnAJAX(false, $error);
}

$studiengang_kz = filter_input(INPUT_GET, "stgkz");

if (is_null($studiengang_kz))
{
    returnAJAX(false, "Variable stgkz nicht gesetzt");
}
elseif($studiengang_kz == false)
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");
}

$sort_mapping = array(
	'mitarbeiter_uid' => 'mitarbeiter_uid',
	'mitarbeiter_label' => 'nachname',
	'beginn' => 'beginn',
	'ende' => 'ende',
	'besqualbez' => 'besqualcode'
);
$order_enum = array(
	'asc',
	'desc'
);

$sort = array();
if (isset($_GET['sort']))
{
	$sortinput = explode(',', $_GET['sort']);
	foreach ($sortinput as $idx => $value)
	{
		if (array_key_exists($value, $sort_mapping))
		{
			$sort[$idx] = $sort_mapping[$value];
		}
	}
}

if (count($sort) === 0)
{
		$sort[] = $sort_mapping['mitarbeiter_label'];
}

if (isset($_GET['order']))
{
	$orderinput = explode(',', $_GET['order']);
	foreach ($orderinput as $idx => $value)
	{
		if (isset($sort[$idx]) && in_array($value, $order_enum))
		{
			$sort[$idx] .= ' '. $value;
		}
	}
}

$sortstring = implode(', ', $sort);

if($studiengang_kz == "null")
    $studiengang_kz = null;

$entwicklungsteam = new entwicklungsteam();
$entwicklungsteam->getAll($studiengang_kz, $sortstring);
$besqualcode = new besqualcode();

$data = array();
foreach ($entwicklungsteam->result as $ewt)
{
		$besqualcode->load($ewt->besqualcode);
		$now = new DateTime('today');
		$now = $now->format('Y-m-d');
		$tmp = array(
			'mitarbeiter_uid' => $ewt->mitarbeiter_uid,
			'mitarbeiter_label' => $ewt->nachname. ' '. $ewt->vorname,
			'besqualcode' => $ewt->besqualcode,
			'besqualbez'	=> $besqualcode->besqualbez,
			'beginn' => $ewt->beginn,
			'ende' => $ewt->ende,
			'studiengang_kz' => $ewt->studiengang_kz,
			'entwicklungsteam_id' => $ewt->entwicklungsteam_id
		);
		$data[] = $tmp;
}

returnAJAX(true, $data);
