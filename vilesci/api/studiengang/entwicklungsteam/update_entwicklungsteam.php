<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/entwicklungsteam.class.php');
require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/editEntwicklungsteam",null,"suid"))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Entwicklungsteams anzulegen oder zu editieren.", "detail"=>"stgv/editEntwicklungsteam");
    returnAJAX(FALSE, $error);
}

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];
$entwicklungsteam = mapDataToEntwicklungsteam($data);
if($entwicklungsteam->save())
{
    returnAJAX(true, "Entwicklungsteam erfolgreich aktualisiert");
}
else
{
    $error = array("message"=>"Fehler beim Speichern des Entwicklungsteams.", "detail"=>$entwicklungsteam->errormsg);
    returnAJAX(false, $error);
}


function mapDataToentwicklungsteam($data)
{
    $ew = new entwicklungsteam($data->mitarbeiter_uid, $data->studiengang_kz);
	$ew->mitarbeiter_uid;
	$ew->studiengang_kz = $data->studiengang_kz;
	$ew->besqualcode = $data->besqualcode;
    $ew->beginn = $data->beginn;
    $ew->ende= $data->ende;
    $ew->updatevon = get_uid();
    return $ew;
}

?>
