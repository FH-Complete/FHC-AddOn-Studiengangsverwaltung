<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/doktorat.class.php');
require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/editDoktorat",null,"suid"))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Doktoratsstudienverordnungen anzulegen oder zu editieren.", "detail"=>"stgv/editDoktorat");
    returnAJAX(FALSE, $error);
}

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];
$doktorat = mapDataToDoktorat($data);
if($doktorat->save())
{
    returnAJAX(true, "Doktorat erfolgreich aktualisiert");
}
else
{
    $error = array("message"=>"Fehler beim Speichern des Doktorats.", "detail"=>$doktorat->errormsg);
    returnAJAX(false, $error);
}




function mapDataToDoktorat($data)
{
    $d = new doktorat($data->doktorat_id);
    $d->bezeichnung= $data->bezeichnung;
    $d->datum_erlass = $data->datum_erlass;
    $d->gueltigvon = $data->gueltigvon;
    $d->gueltigbis = $data->gueltigbis;
    $d->erlaeuterungen = $data->erlaeuterungen;
    $d->updatevon = get_uid();
    return $d;
}

?>