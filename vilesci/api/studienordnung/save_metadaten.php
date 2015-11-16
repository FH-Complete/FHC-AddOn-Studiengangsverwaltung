<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/studienordnung.class.php');
require_once('../../../../../include/akadgrad.class.php');
require_once('../../../../../include/studiensemester.class.php');
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
    
    returnAJAX(true, "Studienordnung erfolgreich aktualisiert");
}
else
{
    $error = array("message"=>"Fehler beim Speichern der Studienordnung.", "detail"=>$studienordnung->errormsg);
    returnAJAX(false, $error);
}




function mapDataToStudienordnung($data)
{
    $sto = new studienordnung();
    $sto->loadStudienordnung($data->studienordnung_id);
    $sto->version = $data->version;
    $sto->bezeichnung = $data->bezeichnung;
    $sto->ects = $data->ects;
    $sto->studiengangbezeichnung = $data->studiengangbezeichnung;
    $sto->studiengangbezeichnung_englisch = $data->studiengangbezeichnung_englisch;
    $sto->studiengangkurzbzlang	= $data->studiengangkurzbzlang;
    $sto->gueltigvon = $data->gueltigvon;
    $sto->gueltigbis = $data->gueltigbis;
    $sto->akadgrad_id = $data->akadgrad_id;
    $sto->updatevon = get_uid();
    return $sto;
}

?>
