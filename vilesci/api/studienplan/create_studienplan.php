<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');

require_once('../../../include/StudienplanAddonStgv.class.php');

//TODO functions from core?
require_once('../functions.php');

//TODO
$DEBUG = true;

//TODO PHP get_last_error()
$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];


$studienplan = mapDataToStudienplan($data);

if($studienplan->save())
{
    returnAJAX(true, "Studienplan erfolgreich gespeichert");
}
else
{
    $error = array("message"=>"Fehler beim Speichern des Studienplans.", "detail"=>$studienplan->errormsg);
    returnAJAX(false, $error);
}




function mapDataToStudienplan($data)
{
    $stpl = new StudienplanAddonStgv();
    $stpl->new = true;
    $stpl->studienordnung_id = $data->studienordnung_id;
    $stpl->orgform_kurzbz = $data->orgform_kurzbz;
    $stpl->version = $data->version;
    $stpl->bezeichnung = $data->version;
    $stpl->aktiv = parseBoolean($data->aktiv);
    $stpl->testtool_sprachwahl = parseBoolean($data->testtool_sprachwahl);
    $stpl->insertvon = get_uid();
    return $stpl;
}

?>