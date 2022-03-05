<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/entwicklungsteam.class.php');
require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/deleteEntwicklungsteam",null,"suid"))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Mitglieder Entwicklungsteam zu löschen.", "detail"=>"stgv/deleteEntwicklungsteam");
    returnAJAX(FALSE, $error);
}

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];
$entwicklungsteam = mapDataToEntwicklungsteam($data);
$mitarbeiter_uid = $entwicklungsteam->mitarbeiter_uid;
$studiengang_kz = $entwicklungsteam->studiengang_kz;


if($entwicklungsteam->delete($mitarbeiter_uid, $studiengang_kz))
{
    returnAJAX(true, "Eintrag Entwicklungsteam erfolgreich gelöscht.");
}
else
{
    $error = array("message"=>"Fehler beim Löschen des Eintrags Entwicklungsteam.", "detail"=>$entwicklungsteam->errormsg);
    returnAJAX(false, $error);
}


function mapDataToEntwicklungsteam($data)
{
    $ew = new entwicklungsteam();
    $ew->mitarbeiter_uid = $data->mitarbeiter_uid;
	$ew->studiengang_kz = $data->studiengang_kz;
    return $ew;
}

?>
