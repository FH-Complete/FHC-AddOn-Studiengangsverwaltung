<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/akadgrad.class.php');
require_once('../../../../../../include/studiensemester.class.php');

require_once('../../../../include/StudienordnungAddonStgv.class.php');
require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/editStudienordnung",null,"suid"))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Studienordnungen zu editieren.", "detail"=>"stgv/editStudienordnung");
    returnAJAX(FALSE, $error);
}

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];
$studienordnung = mapDataToStudienordnung($data);
if($studienordnung->save())
{
    
    returnAJAX(true, "Studienordnung erfolgreich aktualisiert");
}
else
{
    $error = array("message"=>"Fehler beim Speichern der Studienordnung.", "detail"=>$studienordnung->errormsg);
    returnAJAX(false, $error);
}

function mapDataToStudienordnung($data)
{
    $sto = new StudienordnungAddonStgv();
    $sto->loadStudienordnung($data->studienordnung_id);
    $sto->studiengangbezeichnung = $data->studiengangbezeichnung;
    $sto->studiengangkurzbzlang	= $data->studiengangkurzbzlang;
    $sto->studiengangsart = $data->studiengangsart;
    $sto->akadgrad_id = $data->akadgrad_id;
    $sto->orgform_kurzbz = $data->orgform_kurzbz;
    $sto->standort_id = $data->standort_id;
    $sto->updatevon = get_uid();
    return $sto;
}

?>
