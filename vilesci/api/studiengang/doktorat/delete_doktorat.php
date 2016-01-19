<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/doktorat.class.php');
require_once('../../functions.php');

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];
$doktorat = mapDataToDoktorat($data);
$doktorat_id = $doktorat->doktorat_id;
 

if($doktorat->delete($doktorat_id))
{
    returnAJAX(true, "Doktorat erfolgreich gelöscht.");
}
else
{
    $error = array("message"=>"Fehler beim Löschen des Doktorats.", "detail"=>$doktorat->errormsg);
    returnAJAX(false, $error);
}


function mapDataToDoktorat($data)
{
    $d = new doktorat();
    $d->doktorat_id = $data->doktorat_id;
    return $d;
}

?>