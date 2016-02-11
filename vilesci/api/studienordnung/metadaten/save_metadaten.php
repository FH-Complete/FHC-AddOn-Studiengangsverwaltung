<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/akadgrad.class.php');
require_once('../../../../../../include/studiensemester.class.php');

require_once('../../../../include/StudienordnungAddonStgv.class.php');
require_once('../../../../include/Beschluss.class.php');
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
    if(!empty($data->beschluesse))
    {
	foreach($data->beschluesse as $b)
	{
//	    var_dump($b);
	    $beschluss = new beschluss();
	    if(isset($b["beschluss_id"]))
	    {
		$beschluss->new = false;
		$beschluss->beschluss_id = $b["beschluss_id"];
	    }
	    else
	    {
		$beschluss->new = true;
	    }
	    $beschluss->studienordnung_id = $studienordnung->studienordnung_id;
	    $beschluss->datum = $b['datum'];
	    $beschluss->typ = $b['typ'];
//	    var_dump($beschluss);
	    if(!$beschluss->save())
	    {
		$error = array("message"=>"Fehler beim Speichern des Beschlusses.", "detail"=>$beschluss->errormsg);
		returnAJAX(false, $error);
	    }
	}
    }

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
    $sto->version = $data->version;
    $sto->bezeichnung = $data->bezeichnung;
    $sto->ects = $data->ects;
    $sto->studiengangbezeichnung = $data->studiengangbezeichnung;
    $sto->studiengangbezeichnung_englisch = $data->studiengangbezeichnung_englisch;
    $sto->studiengangkurzbzlang	= $data->studiengangkurzbzlang;
    $sto->gueltigvon = $data->gueltigvon;
    $sto->gueltigbis = $data->gueltigbis;
    $sto->akadgrad_id = $data->akadgrad_id;
    $sto->aenderungsvariante_kurzbz = $data->aenderungsvariante_kurzbz;
    $sto->status_kurzbz = $data->status_kurzbz;
    $sto->begruendung = $data->begruendung;
    $sto->updatevon = get_uid();
    return $sto;
}

?>
