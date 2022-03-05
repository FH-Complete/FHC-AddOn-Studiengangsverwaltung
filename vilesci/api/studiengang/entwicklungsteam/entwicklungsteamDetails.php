<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/entwicklungsteam.class.php');
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

$mitarbeiter_uid = filter_input(INPUT_GET, "mitarbeiter_uid");

if(is_null($mitarbeiter_uid))
{
    returnAJAX(false, "Variable mitarbeiter_uid nicht gesetzt");
}
elseif($mitarbeiter_uid == false)
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");
}

if($mitarbeiter_uid == "null")
    $mitarbeiter_uid = null;

$entwicklungsteam = new entwicklungsteam($mitarbeiter_uid, $studiengang_kz);
//$entwicklungsteam->load($mitarbeiter_uid, $studiengang_kz);

//$data = $entwicklungsteam->result;


$data = array(
    'mitarbeiter_uid' => $entwicklungsteam->mitarbeiter_uid,
    'studiengang_kz' => $entwicklungsteam->studiengang_kz,
    'besqualcode' => $entwicklungsteam->besqualcode,
    'beginn' => $entwicklungsteam->beginn,
    'ende' => $entwicklungsteam->ende,
    'insertamum' => $entwicklungsteam->insertamum,
    'insertvon' => $entwicklungsteam->insertvon,
    'updateamum' => $entwicklungsteam->updateamum,
    'updatevon' => $entwicklungsteam->updatevon
);

returnAJAX(true, $data);
