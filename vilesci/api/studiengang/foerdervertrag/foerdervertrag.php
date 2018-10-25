<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/dms.class.php');
require_once('../../../../include/foerdervertrag.class.php');

require_once('../../functions.php');


$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if (!$rechte->isBerechtigt('addon/studiengangsverwaltung'))
{
	$error = array("message"=>"Sie haben keine Berechtigung für diese Aktion.", "detail"=>$rechte->errormsg);
	returnAJAX(false, $error);
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

$foerdervertrag = new foerdervertrag();
$foerdervertrag->getAll($studiengang_kz);

foreach($foerdervertrag->result as $f)
{
    $dokumente = array();
    $f->getDokumente($f->foerdervertrag_id);
    foreach($f->dokumente as $dms_id)
    {
	$dms = new dms();
	$dms->load($dms_id);
	array_push($dokumente, $dms);
    }
    $f->dokumente = $dokumente;
}

$data = $foerdervertrag->result;

returnAJAX(true, $data);
