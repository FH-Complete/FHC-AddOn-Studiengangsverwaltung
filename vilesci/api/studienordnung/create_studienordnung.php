<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/studienordnung.class.php');

//TODO functions from core?
require_once('../functions.php');

//TODO
$DEBUG = true;

//TODO PHP get_last_error()
$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];
$studienordnung = mapDataToStudienordnung($data);

if($studienordnung->save())
{
    returnAJAX(true, "Studienordnung erfolgreich gespeichert");
}
else
{
    $error = array("message"=>"Fehler beim Speichern der Studienordnung.", "detail"=>$studienordnung->errormsg);
    returnAJAX(false, $error);
}




function mapDataToStudienordnung($data)
{
    $sto = new studienordnung();
    $sto->new = true;
    $sto->studiengang_kz = $data->stg_kz;
    $sto->version = $data->version;
    $sto->gueltigvon = $data->gueltigvon;
    $sto->gueltigbis = $data->gueltigbis;
    $sto->insertvon = get_uid();
    return $sto;
}

?>