<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/bewerbungstermin.class.php');
require_once('../../functions.php');

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];

$bewerbungstermin = mapDataToBewerbungstermin($data);
if($bewerbungstermin->save())
{
    returnAJAX(true, "Bewerbungstermin erfolgreich gepspeichert.");
}
else
{
    $error = array("message"=>"Fehler beim Speichern des Bewerbungstermins.", "detail"=>$bewerbungstermin->errormsg);
    returnAJAX(false, $error);
}

function mapDataToBewerbungstermin($data)
{
    $bt = new bewerbungstermin();
    $bt->new = true;
    $bt->studiengang_kz = $data->studiengang_kz;
    $bt->studiensemester_kurzbz = $data->studiensemester_kurzbz;
    $bt->anmerkung = $data->anmerkung;
    $bt->beginn = $data->beginn;
    $bt->ende = $data->ende;
    $bt->insertvon = get_uid();
    $bt->nachfrist_ende = $data->nachfrist_ende;
    $bt->nachfrist = parseBoolean($data->nachfrist);
    return $bt;
}

?>