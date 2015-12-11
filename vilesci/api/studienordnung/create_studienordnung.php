<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
//require_once('../../../../../include/studienordnung.class.php');
require_once('../../../include/StudienordnungAddonStgv.class.php');

//TODO functions from core?
require_once('../functions.php');

//TODO
$DEBUG = true;

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/createStudienordnung",null,"suid"))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Studienordnungen anzulegen.", "detail"=>"stgv/createStudienordnung");
    returnAJAX(FALSE, $error);
}

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
    $sto = new StudienordnungAddonStgv();
    $sto->new = true;
    $sto->studiengang_kz = $data->stg_kz;
    $sto->version = $data->version;
    $sto->bezeichnung = $data->version;
    $sto->aenderungsvariante_kurzbz = $data->aenderungsvariante_kurzbz;
    $sto->status_kurzbz = $data->status_kurzbz;
    $sto->begruendung = $data->begruendung;
    $sto->gueltigvon = $data->gueltigvon;
    $sto->gueltigbis = $data->gueltigbis;
    $sto->insertvon = get_uid();
    return $sto;
}

?>