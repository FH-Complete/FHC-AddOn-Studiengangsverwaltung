<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/reihungstest.class.php');
//TODO functions from core?
require_once('../../functions.php');

//TODO
$DEBUG = true;
$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];

$reihungstest = mapDataToReihungstest($data);
if($reihungstest->save())
{
    returnAJAX(true, "Reihungstest erfolgreich gepspeichert.");
}
else
{
    $error = array("message"=>"Fehler beim Speichern des Reihungstests.", "detail"=>$reihungstest->errormsg);
    returnAJAX(false, $error);
}

function mapDataToReihungstest($data)
{
    $rt = new reihungstest();
    $rt->new = true;
    $rt->studiengang_kz = $data->studiengang_kz;
    $rt->ort_kurzbz = $data->ort_kurzbz;
    $rt->anmerkung = $data->anmerkung;
    $rt->datum = $data->datum;
    $rt->uhrzeit = $data->uhrzeit;
    $rt->inservon = get_uid();
    $rt->max_teilnehmer = $data->max_teilnehmer;
    $rt->oeffentlich = parseBoolean($data->oeffentlich);
    $rt->freigeschaltet = parseBoolean($data->freigeschaltet);
    return $rt;
}

?>