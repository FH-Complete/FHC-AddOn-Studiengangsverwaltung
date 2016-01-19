<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/StudienplanAddonStgv.class.php');
require_once('../../../../include/StudienordnungAddonStgv.class.php');
require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/changeStudienplan",null,"suid"))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Studienpläne zu ändern.", "detail"=>"stgv/changeStudienplan");
    returnAJAX(FALSE, $error);
}

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];

$studienplan = mapDataToStudienplan($data);
$studienordnung = new StudienordnungAddonStgv();
$studienordnung->loadStudienordnung($studienplan->studienordnung_id);

if($studienordnung->status_kurzbz != "development")
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Studienpläne in diesem Status ändern.", "detail"=>"stgv/changeStudienplan");
    returnAJAX(FALSE, $error);
}

if($studienplan->save())
{
    returnAJAX(true, "Studienplan erfolgreich aktualisiert");
}
else
{
    $error = array("message"=>"Fehler beim Speichern des Studienplans.", "detail"=>$studienplan->errormsg);
    returnAJAX(false, $error);
}

function mapDataToStudienplan($data)
{
    $stpl = new StudienplanAddonStgv();
    $stpl->loadStudienplan($data->studienplan_id);
    
    $stpl->updatevon = get_uid();
    $stpl->regelstudiendauer = $data->regelstudiendauer;
    $stpl->sprache = $data->sprache;
    $stpl->ects_stpl = $data->ects_stpl;

    return $stpl;
}

?>