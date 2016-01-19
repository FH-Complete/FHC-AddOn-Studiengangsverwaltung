<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');

require_once('../../../../include/Berufspraktikum.class.php');
require_once('../../functions.php');

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];

$berufspraktikum = mapDataToBerufspraktikum($data);

if($berufspraktikum->save())
{
    returnAJAX(true, array($berufspraktikum->berufspraktikum_id));
}
else
{
    $error = array("message"=>"Fehler beim Speichern der Berufspraktikum.", "detail"=>$berufspraktikum->errormsg);
    returnAJAX(false, $error);
}

function mapDataToBerufspraktikum($data)
{
    $t = new berufspraktikum();
    if($data->berufspraktikum_id === "")
	$t->new = true;
    else
    {
	$t->new = false;
	$t->load($data->berufspraktikum_id);
    }
    
    $t->studienplan_id = $data->studienplan_id;
    $t->erlaeuterungen = $data->erlaeuterungen;
    $t->data = $data->data;
    $t->insertvon = get_uid();
    return $t;
}

?>
