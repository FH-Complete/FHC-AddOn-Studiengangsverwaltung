<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/studienplanAddonStgv.class.php');
require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/editLehrveranstaltung",null,"suid"))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Lehrveranstaltungen zu editieren.", "detail"=>"stgv/editLehrveranstaltung");
    returnAJAX(FALSE, $error);
}

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];

$lehrveranstaltung = mapDataToLehrveranstaltung($data);

$studienplan = new StudienplanAddonStgv();
$studienplan->getStudienplanLehrveranstaltung($lehrveranstaltung->lehrveranstaltung_id);

if(count($studienplan->result) > 1)
{
    $studienplanIds = "StplIds: ";
    foreach($studienplan->result as $stpl)
    {
	$studienplanIds .= $stpl->studienplan_id.", ";
    }
    
    $studienplanIds = rtrim($studienplanIds, ", ");
    
    $error = array("message"=>"Lehrveranstaltung ist in anderen Studienplänen vorhanden. Es konnten nicht alle Daten gepseichert werden.", "detail"=>$studienplanIds);
    returnAJAX(false, $error);
}

if($lehrveranstaltung->save())
{
    returnAJAX(true, array($lehrveranstaltung->lehrveranstaltung_id));
}
else
{
    $error = array("message"=>"Fehler beim Speichern der Lehrveranstaltung.", "detail"=>$lehrveranstaltung->errormsg);
    returnAJAX(false, $error);
}

function mapDataToLehrveranstaltung($data)
{
    $lv = new lehrveranstaltung($data->lehrveranstaltung_id);
    $lv->lehre = parseBoolean($data->lehre);
    $lv->updatevon = get_uid();
    $lv->zeugnis = parseBoolean($data->zeugnis);
    $lv->benotung = parseBoolean($data->benotung);
    $lv->lvinfo = parseBoolean($data->lvinfo);
    $lv->lehrauftrag = parseBoolean($data->lehrauftrag);

    return $lv;
}

?>