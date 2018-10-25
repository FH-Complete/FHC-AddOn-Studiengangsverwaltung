<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/reihungstest.class.php');
require_once('../../functions.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if (!$rechte->isBerechtigt('addon/studiengangsverwaltung'))
{
	$error = array("message"=>"Sie haben keine Berechtigung für diese Aktion.", "detail"=>$rechte->errormsg);
	returnAJAX(false, $error);
}

$reihungstest_id = filter_input(INPUT_GET, "reihungstest_id");

if(is_null($reihungstest_id))
{
    returnAJAX(false, "Variable reihungstest_id nicht gesetzt");
}
elseif($reihungstest_id == false)
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");
}

$reihungstest = new reihungstest($reihungstest_id);





$data = array(
    'reihungstest_id' => $reihungstest->reihungstest_id,
    'studiengang_kz' => $reihungstest->studiengang_kz,
    'ort_kurzbz' => $reihungstest->ort_kurzbz,
    'anmerkung' => $reihungstest->anmerkung,
    'datum' => $reihungstest->datum,
    'uhrzeit' => $reihungstest->uhrzeit,
    'ext_id' => $reihungstest->ext_id,
    'insertamum' => $reihungstest->insertamum,
    'insertvon' => $reihungstest->insertvon,
    'updateamum' => $reihungstest->updateamum,
    'updatevon' => $reihungstest->updatevon,
    'max_teilnehmer' => $reihungstest->max_teilnehmer,
    'oeffentlich' => $reihungstest->oeffentlich,
    'freigeschaltet' => $reihungstest->freigeschaltet,
    'studiensemester_kurzbz' => $reihungstest->studiensemester_kurzbz
);

returnAJAX(true, $data);
