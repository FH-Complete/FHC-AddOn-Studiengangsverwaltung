<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');

require_once('../../../../include/studienplanAddonStgv.class.php');
require_once('../../../../include/studienordnungAddonStgv.class.php');
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

$stpl = new StudienplanAddonStgv();
$stpl->loadStudienplan($studienplan->studienplan_id);

$studienordnung = new StudienordnungAddonStgv();
$studienordnung->loadStudienordnung($stpl->studienordnung_id);

if($studienordnung->status_kurzbz !== "development")
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Studienpläne in diesem Status zu ändern.", "detail"=>"stgv/changeStudienplan");
    returnAJAX(FALSE, $error);
}

$studienplan_lehrveranstaltung_id = $studienplan->saveStudienplanLehrveranstaltung();
if($studienplan_lehrveranstaltung_id != FALSE)
{
    returnAJAX(true, array($studienplan_lehrveranstaltung_id));
}
else
{
    $error = array("message"=>"Fehler beim Speichern des Studienplans.", "detail"=>$studienplan->errormsg);
    returnAJAX(false, $error);
}

function mapDataToStudienplan($data)
{
    $stpl = new StudienplanAddonStgv();
    $stpl->new = true;
    $stpl->studienplan_id = $data->studienplan_id;
    $stpl->semester = $data->semester;
    $stpl->lehrveranstaltung_id = $data->lehrveranstaltung_id;
    $stpl->studienplan_lehrveranstaltung_id_parent = $data->studienplan_lehrveranstaltung_id_parent;
    $stpl->pflicht = parseBoolean($data->pflicht);
    $stpl->insertvon = get_uid();

    return $stpl;
}

?>