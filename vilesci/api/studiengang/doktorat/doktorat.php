<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/dms.class.php');
require_once('../../../../include/doktorat.class.php');
require_once('../../functions.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if (!$rechte->isBerechtigt('addon/studiengangsverwaltung'))
{
	die($rechte->errormsg);
}

$studiengang_kz = filter_input(INPUT_GET, "stgkz");

if(is_null($studiengang_kz))
{
    returnAJAX(false, "Variable stgkz nicht gesetzt");
}
elseif($studiengang_kz == false)
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");
}

if($studiengang_kz == "null")
    $studiengang_kz = null;

$doktorat = new doktorat();
$doktorat->getAll($studiengang_kz);

foreach($doktorat->result as $d)
{
    $dokumente = array();
    $d->getDokumente($d->doktorat_id);
    foreach($d->dokumente as $dms_id)
    {
	$dms = new dms();
	$dms->load($dms_id);
	array_push($dokumente, $dms);
    }
    $d->dokumente = $dokumente;
}

$data = $doktorat->result;

returnAJAX(true, $data);
