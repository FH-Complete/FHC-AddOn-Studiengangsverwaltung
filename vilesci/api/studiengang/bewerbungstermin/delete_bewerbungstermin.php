<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/bewerbungstermin.class.php');
require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/deleteBewerbungsfrist",null,"suid"))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Bewerbungsfristen zu löschen.", "detail"=>"stgv/deleteBewerbungsfrist");
    returnAJAX(FALSE, $error);
}

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];
$bewerbungstermin = mapDataToBewerbungstermin($data);
$bewerbungstermin_id = $bewerbungstermin->bewerbungstermin_id;

if($bewerbungstermin->delete($bewerbungstermin_id))
{
    returnAJAX(true, "Bewerbungstermin erfolgreich gelöscht.");
}
else
{
    $error = array("message"=>"Fehler beim Löschen des Bewerbungstermins.", "detail"=>$bewerbungstermin->errormsg);
    returnAJAX(false, $error);
}


function mapDataToBewerbungstermin($data)
{
    $bt = new bewerbungstermin();
    $bt->bewerbungstermin_id = $data->bewerbungstermin_id;
    return $bt;
}

?>