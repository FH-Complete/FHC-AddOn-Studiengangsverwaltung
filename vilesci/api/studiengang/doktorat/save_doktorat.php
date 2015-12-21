<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/doktorat.class.php');
//TODO functions from core?
require_once('../../functions.php');

//TODO
$DEBUG = true;
$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];
$doktorat = mapDataToDoktorat($data);
if($doktorat->save())
{
    returnAJAX(true, "Doktorat erfolgreich gepspeichert.");
}
else
{
    $error = array("message"=>"Fehler beim Speichern des Doktorats.", "detail"=>$doktorat->errormsg);
    returnAJAX(false, $error);
}

function mapDataToDoktorat($data)
{
    $d = new doktorat();
    $d->new = true;
    $d->studiengang_kz = $data->studiengang_kz;
    $d->bezeichnung = $data->bezeichnung;
    $d->datum_erlass = $data->datum_erlass;
    $d->gueltigvon = $data->gueltigvon;
    $d->gueltigbis = $data->gueltigbis;
    $d->erlaeuterungen = $data->erlaeuterungen;
    $d->insertvon = get_uid();
    return $d;
}

?>