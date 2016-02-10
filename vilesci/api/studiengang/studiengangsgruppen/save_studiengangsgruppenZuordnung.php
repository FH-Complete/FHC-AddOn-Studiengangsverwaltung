<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/Studiengangsgruppe.class.php');

require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/editStudiengangsgruppen",null,"suid"))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Studiengangsgruppen anzulegen oder zu editieren.", "detail"=>"stgv/editStudiengangsgruppen");
    returnAJAX(FALSE, $error);
}


$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];
$studiengangsgruppe = mapDataToStudiengangsgruppe($data);
if($studiengangsgruppe->saveZuordnung())
{
    returnAJAX(true, "Studiengangsgruppe erfolgreich zugeordnet.");
}
else
{
    $error = array("message"=>"Fehler beim Speichern der Zurodnung.", "detail"=>$studiengangsgruppe->errormsg);
    returnAJAX(false, $error);
}

function mapDataToStudiengangsgruppe($data)
{
    $sg = new studiengangsgruppe();
    $sg->new = true;
    $sg->studiengang_kz = $data->studiengang_kz;
    $sg->data = $data->data;
    $sg->inservon = get_uid();
    return $sg;
}

?>