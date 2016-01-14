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
    returnAJAX(true, $foerdervertrag->foerdervertrag_id);
}
else
{
    $error = array("message"=>"Fehler beim Speichern des Fördervertrags.", "detail"=>$foerdervertrag->errormsg);
    returnAJAX(false, $error);
}

function mapDataToFoerdervertrag($data)
{
    $fv = new foerdervertrag();
    $fv->new = true;
    $fv->studiengang_kz = $data->studiengang_kz;
    $fv->foerdergeber = $data->foerdergeber;
    $fv->foerdersatz = $data->foerdersatz;
    $fv->foerdergruppe = $data->foerdergruppe;
    $fv->gueltigvon = $data->gueltigvon;
    $fv->gueltigbis = $data->gueltigbis;
    $fv->erlaeuterungen = $data->erlaeuterungen;
    $fv->insertvon = get_uid();
    return $fv;
}

?>