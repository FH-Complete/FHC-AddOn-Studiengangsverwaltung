<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/akadgrad.class.php');
require_once('../../../../../../include/studiensemester.class.php');

require_once('../../../../include/studienplanAddonStgv.class.php');
require_once('../../../../include/studienordnungAddonStgv.class.php');
require_once('../../functions.php');

$sto_array = array();

$studienplan_id = filter_input(INPUT_GET, "studienplan_id");

if(is_null($studienplan_id))
{
	returnAJAX(false, "Variable studienplan_id nicht gesetzt");	
}
elseif(($studienplan_id == false))
{
	returnAJAX(false, "Fehler beim lesen der GET Variablen");	
}

$studienplan = new StudienplanAddonStgv();
$studienplan->loadStudienplan($studienplan_id);

$studienordnung = new StudienordnungAddonStgv();
$studienordnung->loadStudienordnung($studienplan->studienordnung_id);

$data = array(
	'studienplan_id'=> $studienplan->studienplan_id,
	'version'=> $studienplan->version,
	'orgform_kurzbz' => $studienplan->orgform_kurzbz,
	'bezeichnung' => $studienplan->bezeichnung,
	'regelstudiendauer' => $studienplan->regelstudiendauer,
	'sprache' => $studienplan->sprache,
	'aktiv' => $studienplan->aktiv,
	'semesterwochen' => $studienplan->semesterwochen,
	'testtool_sprachwahl' => $studienplan->testtool_sprachwahl,
	'ects_stpl' => $studienplan->ects_stpl,
	'pflicht_sws' => $studienplan->pflicht_sws,
	'pflicht_lvs' => $studienplan->pflicht_lvs,
	'onlinebewerbung_studienplan' => $studienplan->onlinebewerbung_studienplan,
	'erlaeuterungen' => $studienplan->erlaeuterungen,
	'sprache_kommentar' => $studienplan->sprache_kommentar,
	'updateamum' => $studienplan->updateamum,
	'updatevon' => $studienplan->updatevon,
	'insertamum' => $studienplan->insertamum,
	'insertvon' => $studienplan->insertvon,
	'status_kurzbz' => $studienordnung->status_kurzbz
);


returnAJAX(true, $data);
?>