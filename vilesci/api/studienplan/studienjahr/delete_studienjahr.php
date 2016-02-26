<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/studienjahr.class.php');
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
$studienjahr = mapDataToStudienjahr($data);
$studienjahr_id = $studienjahr->studienjahr_id;

if($studienjahr->delete($studienjahr_id))
{
    returnAJAX(true, "Studienjahr erfolgreich gelöscht.");
}
else
{
    $error = array("message"=>"Fehler beim Löschen des Studienjahrs.", "detail"=>$studienjahr->errormsg);
    returnAJAX(false, $error);
}


function mapDataToStudienjahr($data)
{
    $bt = new studienjahr();
    $bt->studienjahr_id = $data->studienjahr_id;
    return $bt;
}

?>