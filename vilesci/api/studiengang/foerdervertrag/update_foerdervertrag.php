<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/foerdervertrag.class.php');

require_once('../../functions.php');

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];
$foerdervertrag = mapDataToFoerdervertrag($data);
if($foerdervertrag->save())
{
    returnAJAX(true, "Fördervertrag erfolgreich aktualisiert");
}
else
{
    $error = array("message"=>"Fehler beim Speichern des Fördervertrags.", "detail"=>$foerdervertrag->errormsg);
    returnAJAX(false, $error);
}




function mapDataToFoerdervertrag($data)
{
    $fv = new foerdervertrag($data->foerdervertrag_id);
    //$fv->studiengang_kz = $data->studiengang_kz;
    $fv->foerdergeber = $data->foerdergeber;
    $fv->foerdersatz = $data->foerdersatz;
    $fv->foerdergruppe = $data->foerdergruppe;
    $fv->gueltigvon = $data->gueltigvon;
    $fv->gueltigbis = $data->gueltigbis;
    $fv->erlaeuterungen = $data->erlaeuterungen;
    $fv->updatevon = get_uid();
    return $fv;
}

?>