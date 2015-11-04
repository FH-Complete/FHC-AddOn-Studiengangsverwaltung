<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/reihungstest.class.php');
//TODO functions from core?
require_once('../functions.php');

//TODO
$DEBUG = true;
//TODO PHP get_last_error()
$data = json_decode(file_get_contents('php://input'));
$reihungstest = mapDataToReihungstest($data);
if($reihungstest->save())
{
    returnAJAX(true, "Reihungstest erfolgreich aktualisiert");
}
else
{
    $error = array("message"=>"Fehler beim Speichern des Reihungstests.", "detail"=>$reihungstest->errormsg);
    returnAJAX(false, $error);
}




function mapDataToReihungstest($data)
{
    $rt = new reihungstest($data->reihungstest_id);
//    $rt->studiengang_kz = $data->studiengang_kz;
//    $rt->ort_kurzbz = $data->ort_kurzbz;
    $rt->anmerkung = $data->anmerkung;
//    $rt->datum = $data->datum;
//    $rt->uhrzeit = $data->uhrzeit;
    $rt->updatevon = get_uid();
    $rt->max_teilnehmer = $data->max_teilnehmer;
    $rt->oeffentlich = $data->oeffentlich;
    $rt->freigeschaltet = $data->freigeschaltet;
    return $rt;
}

?>