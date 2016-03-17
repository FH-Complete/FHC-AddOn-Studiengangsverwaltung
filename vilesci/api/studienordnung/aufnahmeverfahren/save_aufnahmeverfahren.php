<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');

require_once('../../../../include/aufnahmeverfahren.class.php');
require_once('../../../../include/studienordnungAddonStgv.class.php');
require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/editAufnahmeverfahren",null,"suid"))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Aufnahmeverfahren anzulegen oder zu editieren.", "detail"=>"stgv/editAufnahmeverfahren");
    returnAJAX(FALSE, $error);
}

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];

$aufnahmeverfahren = mapDataToAufnahmeverfahren($data);

$studienordnung = new studienordnungAddonStgv();
$studienordnung->loadStudienordnung($aufnahmeverfahren->studienordnung_id);

if($studienordnung->status_kurzbz != "development" && !($berechtigung->isBerechtigt("stgv/changeStoAdmin")))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Studienordnungen in diesem Status zu ändern.", "detail"=>"stgv/changeStoAdmin");
    returnAJAX(FALSE, $error);
}

if($aufnahmeverfahren->save())
{
    returnAJAX(true, array($aufnahmeverfahren->aufnahmeverfahren_id));
}
else
{
    $error = array("message"=>"Fehler beim Speichern der Tätigkeitsfelder.", "detail"=>$studienordnung->errormsg);
    returnAJAX(false, $error);
}

function mapDataToAufnahmeverfahren($data)
{
    $t = new aufnahmeverfahren();
    if($data->aufnahmeverfahren_id === "")
    {
	$t->new = true;
	$t->insertvon = get_uid();
    }
    else
    {
	$t->new = false;
	$t->load($data->aufnahmeverfahren_id);
	$t->updatevon = get_uid();
    }
    
    $t->studienordnung_id = $data->studienordnung_id;
    $t->data = $data->data;
    return $t;
}

?>
