<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/studienplan.class.php');
//TODO functions from core?
require_once('../../functions.php');

//TODO
$DEBUG = true;
//TODO PHP get_last_error()
$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];

$studienplan = mapDataToStudienplan($data);
if($studienplan->save())
{
    returnAJAX(true, "Studienplan erfolgreich aktualisiert");
}
else
{
    $error = array("message"=>"Fehler beim Speichern des Studienplans.", "detail"=>$studienplan->errormsg);
    returnAJAX(false, $error);
}




function mapDataToStudienplan($data)
{
    $stpl = new studienplan();
    $stpl->loadStudienplan($data->studienplan_id);
    $stpl->version = $data->version;
    $stpl->bezeichnung = $data->bezeichnung;
    $stpl->aktiv = parseBoolean($data->aktiv);
    $stpl->updatevon = get_uid();
    $stpl->orgform_kurzbz = $data->orgform_kurzbz;
    //$stpl->regelstudiendauer = $data->regelstudiendauer;
    //$stpl->semesterwochen = $data->semesterwochen;
    //$stpl->sprache = $data->sprache;
    $stpl->testtool_sprachwahl = parseBoolean($data->testtool_sprachwahl);
    return $stpl;
}

?>