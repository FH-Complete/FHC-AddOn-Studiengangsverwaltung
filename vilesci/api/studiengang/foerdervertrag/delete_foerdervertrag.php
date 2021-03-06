<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/foerdervertrag.class.php');

require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/deleteFoerdervertrag",null,"suid"))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Föerderverträge zu löschen.", "detail"=>"stgv/deleteFoerdervertrag");
    returnAJAX(FALSE, $error);
}

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];
$foerdervertrag = mapDataToFoerdervertrag($data);
$foerdervertrag_id = $foerdervertrag->foerdervertrag_id;
 
$foerdervertrag->getDokumente($foerdervertrag_id);
foreach($foerdervertrag->dokumente as $dms_id)
{
    if(!$foerdervertrag->deleteDokument($foerdervertrag_id, $dms_id))
    {
	$error = array("message"=>"Fehler beim Löschen der Dokumente.", "detail"=>$foerdervertrag->errormsg);
	returnAJAX(false, $error);
    }
}

if($foerdervertrag->delete($foerdervertrag_id))
{
    returnAJAX(true, "Fördervertrag erfolgreich gelöscht.");
}
else
{
    $error = array("message"=>"Fehler beim Löschen des Fördervertrags.", "detail"=>$foerdervertrag->errormsg);
    returnAJAX(false, $error);
}


function mapDataToFoerdervertrag($data)
{
    $fv = new foerdervertrag();
    $fv->foerdervertrag_id = $data->foerdervertrag_id;
    return $fv;
}

?>