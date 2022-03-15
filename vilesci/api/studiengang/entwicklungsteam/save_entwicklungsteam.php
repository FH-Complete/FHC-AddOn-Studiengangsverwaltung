<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/entwicklungsteam.class.php');
require_once('../../functions.php');
header("Content-Type: application/json");

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if (!$berechtigung->isBerechtigt("stgv/editEntwicklungsteam", null, "suid"))
{
    $error = array("message" => "Sie haben nicht die Berechtigung um Entwicklungsteameinträge anzulegen oder zu editieren.", "detail" => "stgv/editEntwicklungsteam");
    returnAJAX(false, $error);
}

$data = filter_input_array(INPUT_POST, array("data" => array('flags' => FILTER_REQUIRE_ARRAY)));
$data = (object)$data["data"];
$entwicklungsteam = mapDataToEntwicklungsteam($data);
if ($entwicklungsteam->save())
{
    returnAJAX(true, "Entwicklungsteam erfolgreich gespeichert");
}
else
{
    $error = array("message" => "Fehler beim Speichern des Entwicklungsteams.", "detail" => $entwicklungsteam->errormsg);
    returnAJAX(false, $error);
}

/**
 * @param array $data Datenarray, das gemappt wird.
 * @return $ew Daten Entwicklungsteam
 */
function mapDataToEntwicklungsteam($data)
{
    $ew = new entwicklungsteam($data->mitarbeiter_uid, $data->studiengang_kz);
    $ew->new = true;
	  $ew->mitarbeiter_uid = $data->mitarbeiter_uid;
    $ew->studiengang_kz = $data->studiengang_kz;
	  $ew->besqualcode = $data->besqualcode;
    $ew->beginn = $data->beginn;
    $ew->ende = $data->ende;
    $ew->insertvon = get_uid();
    $ew->insertamum = $data->insertamum;
    return $ew;
}

?>
