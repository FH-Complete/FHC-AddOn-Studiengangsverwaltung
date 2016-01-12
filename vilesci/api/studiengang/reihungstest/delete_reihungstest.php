<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/reihungstest.class.php');

require_once('../../functions.php');

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];
$reihungstest = mapDataToReihungstest($data);
$reihungstest_id = $reihungstest->reihungstest_id;
 

if($reihungstest->delete($reihungstest_id))
{
    returnAJAX(true, "Reihungstest erfolgreich gelöscht.");
}
else
{
    $error = array("message"=>"Fehler beim Löschen des Reihungstests.", "detail"=>$reihungstest->errormsg);
    returnAJAX(false, $error);
}


function mapDataToReihungstest($data)
{
    $rt = new reihungstest();
    $rt->reihungstest_id = $data->reihungstest_id;
    return $rt;
}

?>