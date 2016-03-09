<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/lehrveranstaltung.class.php');
require_once('../../../../../include/dms.class.php');
require_once('../../../include/studienordnungAddonStgv.class.php');
require_once('../../../include/taetigkeitsfeld.class.php');
require_once('../../../include/qualifikationsziel.class.php');
require_once('../../../include/zugangsvoraussetzung.class.php');
require_once('../../../include/aufnahmeverfahren.class.php');
require_once('../../../include/studienplanAddonStgv.class.php');
require_once('../../../include/auslandssemester.class.php');
require_once('../../../include/berufspraktikum.class.php');
require_once('../../../../../include/studienjahr.class.php');

require_once('../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if (!$berechtigung->isBerechtigt("stgv/createStudienordnung", null, "suid"))
{
    $error = array("message" => "Sie haben nicht die Berechtigung um Studienordnungen anzulegen.", "detail" => "stgv/createStudienordnung");
    returnAJAX(FALSE, $error);
}

$data = filter_input_array(INPUT_POST, array("data" => array('flags' => FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];

$studienordnung = mapDataToStudienordnung($data);

if (($data->vorlage_studienordnung_id != 'null') && $data->vorlage_studienordnung_id != "")
{
    $sto_vorlage = new StudienordnungAddonStgv();
    $sto_vorlage->loadStudienordnung($data->vorlage_studienordnung_id);

//    $studienordnung->studiengangsart = $sto_vorlage->studiengangsart;
    $studienordnung->orgform_kurzbz = $sto_vorlage->orgform_kurzbz;
    $studienordnung->standort_id = $sto_vorlage->standort_id;
    $studienordnung->ects = $sto_vorlage->ects;
    $studienordnung->studiengangbezeichnung = $sto_vorlage->studiengangbezeichnung;
    $studienordnung->studiengangbezeichnung_englisch = $sto_vorlage->studiengangbezeichnung_englisch;
    $studienordnung->studiengangkurzbzlang = $sto_vorlage->studiengangkurzbzlang;
    $studienordnung->akadgrad_id = $sto_vorlage->akadgrad_id;
    
    $sto_vorlage->getDokumente($sto_vorlage->studienordnung_id);    
}

if ($studienordnung->save())
{
    if (($data->vorlage_studienordnung_id != 'null') && $data->vorlage_studienordnung_id != "")
    {
	$taetigkeitsfeld = new taetigkeitsfeld();
	$taetigkeitsfeld->getAll($sto_vorlage->studienordnung_id);
	if (!empty($taetigkeitsfeld->result))
	{
	    $taetigkeitsfeld = $taetigkeitsfeld->result[0];
	    $taetigkeitsfeld->new = true;
	    $taetigkeitsfeld->taetigkeitsfeld_id = null;
	    $taetigkeitsfeld->studienordnung_id = $studienordnung->studienordnung_id;
	    $taetigkeitsfeld->data = json_encode($taetigkeitsfeld->data);
	    $taetigkeitsfeld->save();
	}

	$qualifikationsziel = new qualifikationsziel();
	$qualifikationsziel->getAll($sto_vorlage->studienordnung_id);
	if (!empty($qualifikationsziel->result))
	{
	    $qualifikationsziel = $qualifikationsziel->result[0];
	    $qualifikationsziel->new = true;
	    $qualifikationsziel->qualifikationsziel_id = null;
	    $qualifikationsziel->studienordnung_id = $studienordnung->studienordnung_id;
	    $qualifikationsziel->data = json_encode($qualifikationsziel->data);
	    $qualifikationsziel->save();
	}

	foreach($sto_vorlage->dokumente as $dok_id)
	{
	    $studienordnung->saveDokument($dok_id);
	}
	
	$zugangsvoraussetzung = new zugangsvoraussetzung();
	$zugangsvoraussetzung->getAll($sto_vorlage->studienordnung_id);
	
	foreach($zugangsvoraussetzung->result as $z)
	{
	    $z->new = true;
	    $z->zugangsvoraussetzung_id = null;
	    $z->studienordnung_id = $studienordnung->studienordnung_id;
	    $z->data = json_encode($z->data);
	    $z->save();
	}
	
	$aufnahmeverfahren = new aufnahmeverfahren();
	$aufnahmeverfahren->getAll($sto_vorlage->studienordnung_id);
	
	foreach($aufnahmeverfahren->result as $a)
	{
	    $a->new = true;
	    $a->aufnahmeverfahren_id = null;
	    $a->studienordnung_id = $studienordnung->studienordnung_id;
	    $a->data = json_encode($a->data);
	    $a->save();
	}

	$studienplan = new StudienplanAddonStgv();
	$studienplan->loadStudienplanSTO($sto_vorlage->studienordnung_id);

	if (!empty($studienplan->result))
	{
	    foreach ($studienplan->result as $key => $value)
	    {
		$stpl = new StudienplanAddonStgv();
		$stpl->new = true;
		$stpl->version = $value->version;
		$stpl->bezeichnung = $value->bezeichnung;
		$stpl->studienordnung_id = $studienordnung->studienordnung_id;
		$stpl->orgform_kurzbz = $value->orgform_kurzbz;
		$stpl->regelstudiendauer = $value->regelstudiendauer;
		$stpl->sprache = $value->sprache;
		$stpl->aktiv = $value->aktiv;
		$stpl->semesterwochen = $value->semesterwochen;
		$stpl->testtool_sprachwahl = $value->testtool_sprachwahl;
		$stpl->ects_stpl = $value->ects_stpl;
		$stpl->pflicht_sws = $value->pflicht_sws;
		$stpl->pflicht_lvs = $value->pflicht_lvs;
		$stpl->erlaeuterungen = $value->erlaeuterungen;
		$stpl->insertvon = get_uid();

		$stpl->save();
		$zuordnung = array();
		$studiensemester = $stpl->loadStudiensemesterFromStudienplan($value->studienplan_id);
		if ($studiensemester != FALSE)
		{
		    foreach ($studiensemester as $sem)
		    {
			$semester = $stpl->loadAusbildungsemesterFromStudiensemester($value->studienplan_id, $sem);
			if ($semester != FALSE)
			{
			    foreach ($semester as $s)
			    {
				$z = array("studienplan_id" => $stpl->studienplan_id, "studiensemester_kurzbz" => $sem, "ausbildungssemester" => $s);
				array_push($zuordnung, $z);
			    }
			}
		    }
		    //check ob gueltigkeit zwischen gueltigvon und gueltigbis der sto
		    foreach ($zuordnung as $index=>$v)
		    {
			if (!isZuordnungGuelitg($v["studienplan_id"], $v["studiensemester_kurzbz"]))
			{
			    unset($zuordnung[$index]);
			}
			if($studienplan->isSemesterZugeordnet($v["studienplan_id"], $v["studiensemester_kurzbz"], $v["ausbildungssemester"]))
			{
			    unset($zuordnung[$index]);
			}
		    }
		    $zuordnung = array_values($zuordnung);
		    $stpl->saveSemesterZuordnung($zuordnung);
		}

		$lv = new lehrveranstaltung();
		$data = $lv->getLvTree($value->studienplan_id);

		saveStudienplanLehrveranstaltung($data, $stpl->studienplan_id, null);
		
		$auslandssemester = new auslandssemester();
		$auslandssemester->getAll($value->studienplan_id);
		if(!empty($auslandssemester->result))
		{
		    foreach($auslandssemester->result as $aSem)
		    {
			$aSem->studienplan_id = $stpl->studienplan_id;
			$aSem->new = true;
			$aSem->insertvon = get_uid();
			$aSem->data = json_encode($aSem->data);
			$aSem->save();
		    }
		}
		
		$berufspraktikum = new berufspraktikum();
		$berufspraktikum->getAll($value->studienplan_id);
		if(!empty($berufspraktikum->result))
		{
		    foreach($berufspraktikum->result as $bPraktikum)
		    {
			$bPraktikum->studienplan_id = $stpl->studienplan_id;
			$bPraktikum->new = true;
			$bPraktikum->insertvon = get_uid();
			$bPraktikum->data = json_encode($bPraktikum->data);
			$bPraktikum->save();
		    }
		}
		
		$studienjahr = new studienjahr();
		$studienjahr->getAll($value->studienplan_id);
		if(!empty($studienjahr->result))
		{
		    foreach($studienjahr->result as $s)
		    {
			$s->studienplan_id = $stpl->studienplan_id;
			$s->new = true;
			$s->insertvon = get_uid();
			$s->data = json_encode($s->data);
			$s->save();
		    }
		}
		
		//TODO copy gemeinsames Studienprogramm
	    }
	}
    }
    returnAJAX(true, "Studienordnung erfolgreich gespeichert");
} else
{
    $error = array("message" => "Fehler beim Speichern der Studienordnung.", "detail" => $studienordnung->errormsg);
    returnAJAX(false, $error);
}

function mapDataToStudienordnung($data)
{
    $sto = new StudienordnungAddonStgv();
    $sto->new = true;
    $sto->studiengang_kz = $data->stg_kz;
    $sto->version = $data->version;
    $sto->bezeichnung = $data->version;
    $sto->aenderungsvariante_kurzbz = $data->aenderungsvariante_kurzbz;
    $sto->status_kurzbz = $data->status_kurzbz;
    $sto->begruendung = $data->begruendung;
    $sto->gueltigvon = $data->gueltigvon;
    $sto->gueltigbis = $data->gueltigbis;
    $sto->insertvon = get_uid();
    return $sto;
}

function saveStudienplanLehrveranstaltung($data, $studienplan_id, $parent_id)
{
    $stpllv = new StudienplanAddonStgv();
    $stpllv->new = true;
    $stpllv->studienplan_id = $studienplan_id;
    foreach ($data as $lv)
    {
	if($lv->lehrveranstaltung_id != null)
	{
	    $stpllv->lehrveranstaltung_id = $lv->lehrveranstaltung_id;
	    $stpllv->semester = $lv->stpllv_semester;
	    $stpllv->studienplan_lehrveranstaltung_id_parent = $parent_id;
	    $stpllv->pflicht = $lv->stpllv_pflicht;
	    $stpllv->koordinator = $lv->stpllv_koordinator;
	    $stpllv->insertvon = get_uid();

	    if (($stpllv->saveStudienplanLehrveranstaltung() != false) && (count($lv->children) > 0))
	    {
		saveStudienplanLehrveranstaltung($lv->children, $stpllv->studienplan_id, $stpllv->studienplan_lehrveranstaltung_id);
	    }
	}
    }
}

function isZuordnungGuelitg($studienplan_id, $studiensemester_kurzbz)
{
    $stpl = new StudienplanAddonStgv();
    $stpl->loadStudienplan($studienplan_id);
    $studienordnung = new StudienordnungAddonStgv();
    $studienordnung->loadStudienordnung($stpl->studienordnung_id);
    $studiensemester = new studiensemester();
    $studiensemester->getTimestamp($studiensemester_kurzbz);

    $semGueltigVon = $studiensemester->begin->start;

    $studiensemester = new studiensemester();
    $studiensemester->getTimestamp($studienordnung->gueltigvon);

    $stoGueltigVon = $studiensemester->begin->start;

    if ($studienordnung->gueltigbis != null)
    {
	$studiensemester = new studiensemester();
	$studiensemester->getTimestamp($studienordnung->gueltigbis);
	$stoGueltigBis = $studiensemester->ende->ende;
    } else
    {
	$stoGueltigBis = null;
    }
    if (($semGueltigVon >= $stoGueltigVon && $semGueltigVon <= $stoGueltigBis) || ($semGueltigVon >= $stoGueltigVon && $stoGueltigBis == null))
    {
	return true;
    }
    return false;
}

?>