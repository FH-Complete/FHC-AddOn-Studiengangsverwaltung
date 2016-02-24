<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');

require_once('../../../../include/taetigkeitsfeld.class.php');
require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/editTaetigkeitsfelder",null,"suid"))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Taetigkeitsfelder anzulegen oder zu editieren.", "detail"=>"stgv/editTaetigkeitsfelder");
    returnAJAX(FALSE, $error);
}

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];

$taetigkeitsfelder = mapDataToTaetigkeitsfelder($data);

if($taetigkeitsfelder->save())
{
    returnAJAX(true, array($taetigkeitsfelder->taetigkeitsfeld_id));
}
else
{
    $error = array("message"=>"Fehler beim Speichern der Tätigkeitsfelder.", "detail"=>$studienordnung->errormsg);
    returnAJAX(false, $error);
}

function mapDataToTaetigkeitsfelder($data)
{
    $t = new taetigkeitsfeld();
    if($data->taetigkeitsfeld_id === "")
	$t->new = true;
    else
    {
	$t->new = false;
	$t->load($data->taetigkeitsfeld_id);
    }
    
    $t->studienordnung_id = $data->studienordnung_id;
    $t->ueberblick = $data->ueberblick;
    $t->data = $data->data;
    $t->insertvon = get_uid();
    return $t;
}

?>
