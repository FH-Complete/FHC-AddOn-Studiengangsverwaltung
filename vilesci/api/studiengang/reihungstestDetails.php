<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/reihungstest.class.php');
//TODO functions from core?
require_once('../functions.php');

//TODO
$DEBUG = true;

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
    'freigeschaltet' => $reihungstest->freigeschaltet
);

returnAJAX(true, $data);