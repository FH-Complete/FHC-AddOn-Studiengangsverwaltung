<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/bewerbungstermin.class.php');
require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/editBewerbungsfrist",null,"suid"))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Bewerbungsfristen anzulegen oder zu editieren.", "detail"=>"stgv/editBewerbungsfrist");
    returnAJAX(FALSE, $error);
}

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];

$bewerbungstermin = mapDataToBewerbungstermin($data);
if($bewerbungstermin->save())
{
    returnAJAX(true, "Bewerbungstermin erfolgreich aktualisiert");
}
else
{
    $error = array("message"=>"Fehler beim Speichern des Bewerbungstermins.", "detail"=>$bewerbungstermin->errormsg);
    returnAJAX(false, $error);
}

function mapDataToBewerbungstermin($data)
{
    $bt = new bewerbungstermin($data->bewerbungstermin_id);
    $bt->studiensemester_kurzbz = $data->studiensemester_kurzbz;
    $bt->anmerkung = $data->anmerkung;
    $bt->beginn = $data->beginn;
    $bt->ende = $data->ende;
    $bt->nachfrist_ende = $data->nachfrist_ende;
    $bt->nachfrist = parseBoolean($data->nachfrist);
    $bt->updatevon = get_uid();
    return $bt;
}

?>