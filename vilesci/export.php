<?php
/* Copyright (C) 2016 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>,
 * Alexei Karpenko <karpenko@technikum-wien.at>
 */

require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/dokument_export.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/lehreinheitmitarbeiter.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/studienordnung.class.php');
require_once('../../../include/akadgrad.class.php');
require_once('../../../include/organisationsform.class.php');
require_once('../../../include/standort.class.php');
require_once('../../../include/lehrform.class.php');
require_once('../../../include/sprache.class.php');
require_once('../include/studienordnungAddonStgv.class.php');
require_once('../include/studienplanAddonStgv.class.php');
require_once('../include/aenderungsvariante.class.php');
require_once('../include/beschluss.class.php');
require_once('../include/zugangsvoraussetzung.class.php');
require_once('../include/aufnahmeverfahren.class.php');
require_once('../include/taetigkeitsfeld.class.php');
require_once('../include/qualifikationsziel.class.php');
require_once('../include/auslandssemester.class.php');
require_once('../include/berufspraktikum.class.php');
require_once('../vendor/ezyang/htmlpurifier/library/HTMLPurifier.auto.php');

$uid = get_uid();

$datum_obj = new datum();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if (!$rechte->isBerechtigt('stgv/changeStudienplan'))
	die($rechte->errormsg);

$output = 'pdf';

if (isset($_GET['output']) && ($output = 'odt' || $output = 'doc'))
	$output = $_GET['output'];

if (isset($_GET['studienordnung_id']) && is_numeric($_GET['studienordnung_id']))
	$studienordnung_id = $_GET['studienordnung_id'];
else
	die('StudienordnungID muss uebergeben werden');

if (isset($_GET['lvinfo']))
{
	if ($_GET['lvinfo'] == 'true')
		$output_lvinfo = true;
	else
		$output_lvinfo = false;

	// Addon LVINFO
	require_once('../../lvinfo/include/lvinfo.class.php');

}
else
{
	$output_lvinfo = false;
}

$sprache_arr = array();
// Alle Sprachen laden
$sprache = new sprache();
$sprache->getAll();
foreach ($sprache->result as $row)
	$sprache_arr[$row->sprache] = $row->bezeichnung_arr;

$doc = new dokument_export('STGV_Sto');

$studienordnung = new StudienordnungAddonStgv();
$studienordnung->loadStudienordnung($studienordnung_id);

$studienordnungstatus = new studienordnung();
$studienordnungstatus->getStatus();
foreach ($studienordnungstatus->result as $row_status)
	$status_arr[$row_status->status_kurzbz] = $row_status->bezeichnung;

$studiengang = new studiengang();
$studiengang->load($studienordnung->studiengang_kz);

// Aenderungsvariante
$aenderungsvarianten = new Aenderungsvariante();
$aenderungsvarianten->getAll();
$av_arr = array();
foreach ($aenderungsvarianten->result as $row)
	$av_arr[$row->aenderungsvariante_kurzbz] = $row->bezeichnung;

// Beschluesse
$beschluss = new beschluss();
$beschluss->getAll($studienordnung_id);
$beschluesse = array('Studiengang' => '', 'Kollegium' => '', 'AQ Austria' => '');

foreach ($beschluss->result as $row_beschluss)
	$beschluesse[$row_beschluss->typ] = $datum_obj->formatDatum($row_beschluss->datum, 'd.m.Y');

// Akadgrad
$akadgrad = new akadgrad();
$akadgrad->load($studienordnung->akadgrad_id);

// Orgform
$orgform = new organisationsform();
$orgform->load($studiengang->orgform_kurzbz);

// Studiengangstyp
$studiengang->getAllTypes();

// Standort
$standort = new standort();
$standort->load($studienordnung->standort_id);

// ZGV
$zugangsvoraussetzung = new zugangsvoraussetzung();
$zugangsvoraussetzung->getAll($studienordnung_id);

// Aufnahmeverfahren
$aufnahmeverfahren = new aufnahmeverfahren();
$aufnahmeverfahren->getAll($studienordnung_id);

$lehrform = new lehrform();
$lehrform->getAll();
$lehrform_arr = array();
foreach ($lehrform->lehrform as $row)
	$lehrform_arr[$row->lehrform_kurzbz] = $row->bezeichnung;

// Studienplan
$stpl = new StudienplanAddonStgv();
$stpl->loadStudienplanSTO($studienordnung_id);
$stpl_arr = array();
foreach ($stpl->result as $row_stpl)
{
	$stpl_orgform = new organisationsform();
	$stpl_orgform->load($row_stpl->orgform_kurzbz);

	$summe_ects = 0;
	$summe_sws = 0;
	$summe_lvs = 0;

	$semester_arr = array();
	for ($sem = 1; $sem <= $row_stpl->regelstudiendauer; $sem++)
	{
		$semester_summe_ects = 0;
		$semester_summe_sws = 0;
		$semester_summe_lvs = 0;

		$lv = new lehrveranstaltung();
		$lv->loadLehrveranstaltungStudienplan($row_stpl->studienplan_id, $sem);
		$tree = $lv->getLehrveranstaltungTree();

		foreach ($tree as $lv)
			$semester_summe_ects += $lv->ects;

		$lv_arr = PrintLVTree($tree);

		$semester_arr[] = array('ausbildungssemester' =>
			array(
				'semester' => $sem,
				'semester_summe_ects' => $semester_summe_ects,
				'semester_summe_sws' => '', // TODO $semester_summe_sws,
				'semester_summe_lvs' => '', // TODO $semester_summe_lvs,
				'lehrveranstaltungen' => $lv_arr,
			));
		$summe_ects += $semester_summe_ects;
		$summe_sws += $semester_summe_sws;
		$summe_lvs += $semester_summe_lvs;
	}

	// Gueltigkeitdie()
	$gueltig_ab_studiensemester = '';
	$gueltig_ab_ausbildungssemester = '';
	$stpl_gueltig = new studienplan();
	$stpl_gueltig_stsem_arr = $stpl_gueltig->loadStudiensemesterFromStudienplan($row_stpl->studienplan_id);

	if (isset($stpl_gueltig_stsem_arr[0]))
	{
		$gueltig_ab_studiensemester = $stpl_gueltig_stsem_arr[0];
		$stpl_gueltig_ausbsem_arr = $stpl_gueltig->loadAusbildungsemesterFromStudiensemester($row_stpl->studienplan_id, $stpl_gueltig_stsem_arr[0]);
		$gueltig_ab_ausbildungssemester = implode($stpl_gueltig_ausbsem_arr, ' / ');
	}

	// Auslandssemester
	$auslandssemester = new auslandssemester();
	$auslandssemester->getAll($row_stpl->studienplan_id);
	$auslandssemester_semester = array();
	$auslandssemester_erlaeuterungen = '';
	if (isset($auslandssemester->result[0]))
	{
		$auslandssemester_erlaeuterungen = $auslandssemester->result[0]->erlaeuterungen;

		foreach ($auslandssemester->result[0]->data as $auslsem_sem => $row_auslsem)
		{
			if ($row_auslsem->verpflichtend || $row_auslsem->optional)
			{
				$auslandssemester_semester[] = array('semester' => array(
					'semester' => $auslsem_sem + 1,
					'verpflichtend' => ($row_auslsem->verpflichtend ? 'true' : 'false')
				));
			}
		}
	}

	// Berufspraktikum
	$berufspraktikum = new berufspraktikum();
	$berufspraktikum->getAll($row_stpl->studienplan_id);
	$berufspraktikum_erlaeuterungen = '';
	$berufspraktikum_semester = array();
	if (isset($berufspraktikum->result[0]))
	{
		$berufspraktikum_erlaeuterungen = $berufspraktikum->result[0]->erlaeuterungen;

		foreach ($berufspraktikum->result[0]->data as $bpraksem => $row_bprak)
		{
			if ($row_bprak->semester)
			{
				$berufspraktikum_semester[] = array('semester' => array(
					'semester' => $bpraksem + 1,
					'ects' => $row_bprak->ects,
					'wochen' => $row_bprak->dauer,
				));
			}
		}
	}

	$stpl_arr[] = array('studienplan' => array(
		'version' => $row_stpl->version,
		'bezeichnung' => $row_stpl->bezeichnung,
		'organisationsform' => $stpl_orgform->bezeichnung,
		'regelstudiendauer' => $row_stpl->regelstudiendauer,
		'pflicht_sws' => $row_stpl->pflicht_sws,
		'pflicht_lvs' => $row_stpl->pflicht_lvs,
		'sprache' => $row_stpl->sprache,
		'sprache_anzeige' => (isset($sprache_arr[$row_stpl->sprache][DEFAULT_LANGUAGE]) ? $sprache_arr[$row_stpl->sprache][DEFAULT_LANGUAGE] : $row_stpl->sprache),
		'sprache_kommentar' => $row_stpl->sprache_kommentar,
		'semester' => $semester_arr,
		'summe_ects' => $summe_ects,
		'summe_sws' => '', // TODO $summe_sws nicht korrekt bei WahlLVs,
		'summe_lvs' => '', // TODO $summe_lvs nicht korrekt bei WahlLVs,
		'gueltig_ab_studiensemester' => $gueltig_ab_studiensemester,
		'gueltig_ab_ausbildungssemester' => $gueltig_ab_ausbildungssemester,
		'erlaeuterungen' => $row_stpl->erlaeuterungen,
		'auslandssemester_erlaeuterungen' => $auslandssemester_erlaeuterungen,
		'auslandssemester' => $auslandssemester_semester,
		'berufspraktikum_erlaeuterungen' => $berufspraktikum_erlaeuterungen,
		'berufspraktikum' => $berufspraktikum_semester,

	));
}

// Taetigkeitsfeld
$taetigkeitsfeld_ueberblick = '';
$taetigkeitsfeld = new taetigkeitsfeld();
$taetigkeitsfeld->getAll($studienordnung_id);
$aufgaben_elements = array();
$positionen_elements = array();
$branchen_elements = array();
$branchen_fixed = '';
$positionen_fixed = '';
$aufgaben_fixed = '';
$qualifikation_bildungsauftrag = '';
$qualifikation_beschreibung = '';
$qualifikation_kompetenz1 = '';
$qualifikation_kompetenz2 = '';
$qualifikation_kompetenz1_elements = array();
$qualifikation_kompetenz2_elements = array();

if (isset($taetigkeitsfeld->result[0]))
{
	$taetigkeitsfeld_ueberblick = $taetigkeitsfeld->result[0]->ueberblick;
	$branchen_fixed = $taetigkeitsfeld->result[0]->data->branchen->fixed;
	foreach ($taetigkeitsfeld->result[0]->data->branchen->elements as $key => $elem)
	{
		$branchen_elements[$key] = array('elements' => array(
			'title' => $elem->title,
		));
		foreach ($elem->elements as $item)
			$branchen_elements[$key]['elements'][]['element'] = $item;
	}

	$positionen_fixed = $taetigkeitsfeld->result[0]->data->positionen->fixed;
	foreach ($taetigkeitsfeld->result[0]->data->positionen->elements as $key => $elem)
	{
		$positionen_elements[$key] = array('elements' => array(
			'title' => $elem->title,
		));
		foreach ($elem->elements as $item)
			$positionen_elements[$key]['elements'][]['element'] = $item;
	}

	$aufgaben_fixed = $taetigkeitsfeld->result[0]->data->aufgaben->fixed;
	foreach ($taetigkeitsfeld->result[0]->data->aufgaben->elements as $key => $elem)
	{
		$aufgaben_elements[$key] = array('elements' => array(
			'title' => $elem->title,
		));
		foreach ($elem->elements as $item)
			$aufgaben_elements[$key]['elements'][]['element'] = $item;
	}

}

// Qualifikationsziel
$qualifikationsziel = new qualifikationsziel();
$qualifikationsziel->getAll($studienordnung_id);
if (isset($qualifikationsziel->result[0]))
{
	$qualifikation_bildungsauftrag = $qualifikationsziel->result[0]->data[0]->fixed[0];

	$qualifikation_beschreibung = $qualifikationsziel->result[0]->data[1]->elements[0];
	$qualifikation_kompetenz1 = $qualifikationsziel->result[0]->data[1]->fixed[1];
	$qualifikation_kompetenz2 = $qualifikationsziel->result[0]->data[1]->fixed[2];

	foreach ($qualifikationsziel->result[0]->data[1]->elements[1] as $key => $row_kompetenz)
	{
		$qualifikation_kompetenz1_elements[$key] = array('element' => $row_kompetenz);
	}

	foreach ($qualifikationsziel->result[0]->data[1]->elements[2] as $key => $row_kompetenz)
	{
		$qualifikation_kompetenz2_elements[$key] = array('element' => $row_kompetenz);
	}
}
if (isset($zugangsvoraussetzung->result[0]))
	$zugangsvoraussetzung_data = $zugangsvoraussetzung->result[0]->data;
else
	$zugangsvoraussetzung_data = '';

if (isset($aufnahmeverfahren->result[0]))
	$aufnahmeverfahren_data = $aufnahmeverfahren->result[0]->data;
else
	$aufnahmeverfahren_data = '';

//echo "\nAFTER PURIFIER CLEAN:<br /><br />".htmlspecialchars(MSCleanWithPurifier($zugangsvoraussetzung_data));
//echo "\nAFTER 2odt:<br /><br />".htmlspecialchars(escapeGlobalP(html2odtNew($aufnahmeverfahren_data)));
//echo html2odtNew($zugangsvoraussetzung_data);
//die();

$data = array(
	'studienordnung_id' => $studienordnung->studienordnung_id,
	'version' => $studienordnung->version,
	'studiengangbezeichnung' => $studienordnung->studiengangbezeichnung,
	'studiengangbezeichnung_englisch' => $studienordnung->studiengangbezeichnung_englisch,
	'studiengangkurzbzlang' => $studienordnung->studiengangkurzbzlang,
	'akadgrad' => $akadgrad->titel,
	'organisationsform' => $orgform->bezeichnung,
	'studiengangstyp' => $studiengang->typ,
	'studiengangstyp_bezeichnung' => $studiengang->studiengang_typ_arr[$studiengang->typ],
	'standort' => $standort->bezeichnung,
	'status_kurzbz' => $studienordnung->status_kurzbz,
	'studiengang_kz' => sprintf('%04s', $studiengang->studiengang_kz),
	'status_bezeichnung' => (isset($status_arr[$studienordnung->status_kurzbz]) ? $status_arr[$studienordnung->status_kurzbz] : $studienordnung->status_kurzbz),
	'gueltigvon' => $studienordnung->gueltigvon,
	'gueltigbis' => $studienordnung->gueltigbis,
	'aenderungsvariante_kurzbz' => $studienordnung->aenderungsvariante_kurzbz,
	'aenderungsvariante_bezeichnung' => (isset($av_arr[$studienordnung->aenderungsvariante_kurzbz]) ? $av_arr[$studienordnung->aenderungsvariante_kurzbz] : $studienordnung->aenderungsvariante_kurzbz),
	'beschluss_studiengang' => $beschluesse['Studiengang'],
	'beschluss_kollegium' => $beschluesse['Kollegium'],
	'beschluss_aq' => $beschluesse['AQ Austria'],
	'begruendung' => escapeGlobalP(html2odt(json_decode($studienordnung->begruendung))),
	'zugangsvoraussetzung' => escapeGlobalP(html2odt($zugangsvoraussetzung_data)),
	'aufnahmeverfahren' => escapeGlobalP(html2odt($aufnahmeverfahren_data)),
	'studienplaene' => $stpl_arr,
	'taetigkeitsfeld_ueberblick' => escapeGlobalP(html2odt($taetigkeitsfeld_ueberblick)),
	'branchen_fixed' => html2odt($branchen_fixed),
	'branchen_elements' => $branchen_elements,
	'positionen_fixed' => $positionen_fixed,
	'positionen_elements' => $positionen_elements,
	'aufgaben_fixed' => $aufgaben_fixed,
	'aufgaben_elements' => $aufgaben_elements,
	'qualifikation_bildungsauftrag' => $qualifikation_bildungsauftrag,
	'qualifikation_beschreibung' => $qualifikation_beschreibung,
	'qualifikation_kompetenz1' => $qualifikation_kompetenz1,
	'qualifikation_kompetenz2' => $qualifikation_kompetenz2,
	'qualifikation_kompetenz1_elements' => $qualifikation_kompetenz1_elements,
	'qualifikation_kompetenz2_elements' => $qualifikation_kompetenz2_elements,
);

$files = array();
if ($output == 'zgv') // TODO REMOVE
{
	echo '<pre>'.htmlentities(OutputFormat($data['zugangsvoraussetzung'])).'</pre>';
	echo '<hr>';
	echo '<pre>'.htmlentities(OutputFormat(MsCleanWithPurifier($zugangsvoraussetzung->result[0]->data))).'</pre>';
	exit;
}
if ($output == 'auf') // TODO REMOVE
{
	echo '<pre>'.htmlentities(OutputFormat($data['aufnahmeverfahren'])).'</pre>';
	echo '<hr>';
	echo '<pre>'.htmlentities(OutputFormat(MsCleanWithPurifier($aufnahmeverfahren->result[0]->data))).'</pre>';
	exit;
}

$doc->addDataArray($data, 'studienordnung');
if ($output == 'xml')
{
	header("Content-type: application/xhtml+xml");
	echo $doc->getXML();
}
else
{
	if (!$doc->create($output))
		die($doc->errormsg);
	$doc->output();
	$doc->close();
}

function printLVTree($tree)
{
	global $semester_summe_sws, $semester_summe_lvs, $lehrform_arr;
	global $output_lvinfo, $sprache_arr;

	$data = array();
	$i = 0;
	foreach ($tree as $lv)
	{
		// Nicht studienplanrelevante ueberspringen
		if (!$lv->export)
			continue;

		$semester_summe_sws += $lv->sws;
		$semester_summe_lvs += $lv->lvs;

		$data[$i]['lehrveranstaltung'] = array(
			'lehrveranstaltung_id' => $lv->lehrveranstaltung_id,
			'kurzbz' => $lv->kurzbz,
			'bezeichnung' => $lv->bezeichnung,
			'bezeichnung_englisch' => $lv->bezeichnung_english,
			'ects' => $lv->ects,
			'sws' => $lv->sws,
			'lvs' => $lv->lvs,
			'lehrtyp' => $lv->lehrtyp_kurzbz,
			'lehrform_kurzbz' => $lv->lehrform_kurzbz,
			'lehrform_bezeichnung' => (isset($lehrform_arr[$lv->lehrform_kurzbz]) ? $lehrform_arr[$lv->lehrform_kurzbz] : $lv->lehrform_kurzbz),
			'genehmigung' => ($lv->genehmigung ? 'true' : 'false'),
			'pflicht' => ($lv->stpllv_pflicht ? 'true' : 'false'),
			'sprache' => $lv->sprache,
			'sprache_anzeige' => (isset($sprache_arr[$lv->sprache][DEFAULT_LANGUAGE]) ? $sprache_arr[$lv->sprache][DEFAULT_LANGUAGE] : $lv->sprache),
		);

		if ($output_lvinfo)
		{
			$lvinfo_found = false;
			$lvinfo = new lvinfo();
			$lvinfo->loadLastLvinfo($lv->lehrveranstaltung_id, true);

			foreach ($lvinfo->result as $row_lvinfo)
			{
				if ($row_lvinfo->sprache == $lv->sprache)
				{
					$lvinfo_obj = $row_lvinfo;
					$lvinfo_found = true;
					break;
				}
			}

			if ($lvinfo_found)
			{
				$lvinfo->load_lvinfo_set($lvinfo_obj->studiensemester_kurzbz);
				$lvinfo_data = array();
				foreach ($lvinfo->result as $row_set)
				{
					if ($row_set->lvinfo_set_typ == 'text')
					{
						$lvinfo_data[$row_set->lvinfo_set_kurzbz] = $lvinfo_obj->data[$row_set->lvinfo_set_kurzbz];
					}
					elseif ($row_set->lvinfo_set_typ == 'array')
					{
						$lvinfo_data[$row_set->lvinfo_set_kurzbz]['einleitungstext'] = $row_set->einleitungstext[$lv->sprache];

						if (isset($lvinfo_obj->data[$row_set->lvinfo_set_kurzbz]))
							foreach ($lvinfo_obj->data[$row_set->lvinfo_set_kurzbz] as $row_lvinfo_element)
								$lvinfo_data[$row_set->lvinfo_set_kurzbz]['elements'][] = array('element' => $row_lvinfo_element);
					}
				}

				// LV Informationen
				// TODO
				$data[$i]['lehrveranstaltung']['lvinfo'] = $lvinfo_data;
			}
		}

		// Darunterliegende LVs/Module
		if (isset($lv->childs) && count($lv->childs) > 0)
		{
			$data[$i]['lehrveranstaltung']['childs'] = printLVTree($lv->childs);
		}
		$i++;
	}
	return $data;
}

/**
 * Ersetzt HTML Markup durch odt Markup
 * @param $str der HTML String
 * @return gesaueberter|mixed der odt String
 */
function html2odt($str)
{

	// Word Markup entfernen
	$str = MSCleanWithPurifier($str);

	// Line Breaks
	$str = str_replace('<br>', '<text:line-break/>', $str);
	$str = str_replace('<br />', '<text:line-break/>', $str);

	// FETT <b>, s ist für single line mode, sodass auch zeilenumbrüche beim matchen berücksichtigt werden
	$str = preg_replace('/<b>(.*?)<\/b>/s', '<text:span text:style-name="FETT">$1</text:span>', $str);

	// Kursiv <i>
	$str = preg_replace('/<i>(.*?)<\/i>/s', '<text:span text:style-name="KURSIV">$1</text:span>', $str);

	// Unterstrichen <u>
	$str = preg_replace('/<u>(.*?)<\/u>/s', '<text:span text:style-name="UNTERSTRICHEN">$1</text:span>', $str);

	// Durchgestrichen <strike>
	$str = preg_replace('/<strike>(.*?)<\/strike>/s', '<text:span text:style-name="DURCHGESTRICHEN">$1</text:span>', $str);

	// DIV align ersetzten
	/*$str = preg_replace('/<div align="center">(.*?)<\/div>/s','<text:p text:style-name="ZENTRIERT">$1</text:p>',$str);
	$str = preg_replace('/<div align="left">(.*?)<\/div>/s','<text:p text:style-name="LINKSBUENDIG">$1</text:p>',$str);
	$str = preg_replace('/<div align="right">(.*?)<\/div>/s','<text:p text:style-name="RECHTSBUENDIG">$1</text:p>',$str);
	$str = preg_replace('/<div align="justify">(.*?)<\/div>/s','<text:p text:style-name="LINKSBUENDIG">$1</text:p>',$str);*/

	$str = str_replace('<ul>', '<text:list text:style-name="LIST_UNORDERED">', $str);
	$str = str_replace('</ul>', '</text:list>', $str);
	$str = str_replace('<ol>', '<text:list text:style-name="LIST_ORDERED">', $str);
	$str = str_replace('</ol>', '</text:list>', $str);

	//li Tag ersetzen - wenn mehrere Ebenen sicherstellen dass span und p innerhalb li vor dem nächsten "<" schließt
	$str = preg_replace("/<li>([^<]*)</", '<text:list-item><text:p text:style-name="PLIST"><text:span text:style-name="TLIST">$1</text:span></text:p><', $str);
	$str = str_replace('</li>', '</text:list-item>', $str);

	/*	$str = str_replace('<table>', '<table:table table:name="Tabelle2" table:style-name="Tabelle2">', $str);
		$str = str_replace('</table>', '</table:table>', $str);
		$str = str_replace('<tr>', '<table:table-row table:style-name="Tabelle2.1">', $str);
		$str = str_replace('</tr>', '</table:table-row>', $str);
		$str = str_replace('<td>', '<table:table-cell table:style-name="Tabelle2.B1" office:value-type="string">', $str);
		$str = str_replace('</td>', '</table:table-cell>', $str);*/

	// Font size Ersetzen TODO unterschiedliche groessen
	$str = str_replace('<font size="5">', '<text:span text:style-name="GROESSER">', $str);
	$str = str_replace('<font size="4">', '<text:span text:style-name="GROESSER">', $str);
	$str = str_replace('<font size="3">', '<text:span text:style-name="GROESSER">', $str);
	$str = str_replace('<font size="2">', '<text:span text:style-name="PNORMAL">', $str);
	$str = str_replace('<font size="1">', '<text:span text:style-name="PNORMAL">', $str);
	$str = str_replace('</font>', '</text:span>', $str);

	// Uebrige Font tags etnfernen
	$str = preg_replace('/<font.*?>/', '<text:span text:style-name="PNORMAL">', $str);

	// P Tags ersetzten
	$str = str_replace('<p>', '<text:p text:style-name="PNORMAL">', $str);
	$str = str_replace('</p>', '</text:p> ', $str);

	// Blockquote Tags ersetzen
	$str = str_replace('<blockquote>', '<text:p text:style-name="PEINGERUECKT">', $str);
	$str = str_replace('</blockquote>', '</text:p>', $str);

	// Aufzaehlungslisten korrigieren die nicht korrekt verschachtelt wurden
	// <list></p><list>
	$str = str_replace('<text:list text:style-name="LIST_UNORDERED"></text:p><text:list text:style-name="LIST_UNORDERED">', '<text:list text:style-name="LIST_UNORDERED"><text:list text:style-name="LIST_UNORDERED">', $str);
	//<p><span></List>
	$str = str_replace('<text:p text:style-name="PLIST"><text:span text:style-name="TLIST"></text:list>', '<text:p text:style-name="PLIST"><text:span text:style-name="TLIST"> </text:span></text:p></text:list>', $str);

	$str = str_replace('<text:p text:style-name="PLIST"><text:span text:style-name="TLIST"></text:p>', '<text:p text:style-name="PLIST"><text:span text:style-name="TLIST"> </text:span></text:p>', $str);

	// <p>..</span></p>
	$str = preg_replace('/<text:p text:style-name="PNORMAL">(((?!<text:span).)+?)<\/text:span><\/text:p>/s', '<text:p text:style-name="PNORMAL">$1</text:p>', $str);
	// <p></span></p>
	$str = str_replace('<text:p text:style-name="PNORMAL"></text:span></text:p>', '<text:p text:style-name="PNORMAL"></text:p>', $str);

	return $str;
}

/**
 * Schließt "globalen" Paragraph welcher standardmäßig vor dem Text geöffnet ist
 * @param $str Text nach dem Paragraph
 * @return string der den closing Tag fü den globalen Paragraphen am Anfang beinhaltet
 */
function escapeGlobalP($str)
{
	//Position des ersten Tags (Liste oder Paragraph)
	preg_match("/(<text:p|<text:list)/", $str, $matches, PREG_OFFSET_CAPTURE);
	if (array_key_exists(0, $matches))
	{
		$posFirstTag = $matches[0][1];
	}
	else
	{
		return $str;
	}
	//zusammengesetzter String: Alles bis zum ersten Tag + globales p Schließtag + Rest + globales p wieder öffnen
	return substr($str, 0, $posFirstTag)."</text:p>".substr($str, $posFirstTag).'<text:p text:style-name="PNORMAL">';
}

/**
 * Alte HTML-odt-Konvertierung, nur mit Regular Expressions
 * @param $str der HTML String
 * @return gesaueberter|mixed der odt String
 */
function html2odtOld($str)
{
	// Word Markup entfernen
	$str = MSClean($str);

	// Line Breaks
	$str = str_replace('<br>', '<text:line-break/>', $str);

	// FETT <b>
	$str = preg_replace('/<b>(.*?)<\/b>/s', '<text:span text:style-name="FETT">$1</text:span>', $str);

	// Kursiv <i>
	$str = preg_replace('/<i>(.*?)<\/i>/s', '<text:span text:style-name="KURSIV">$1</text:span>', $str);

	// Unterstrichen <u>
	$str = preg_replace('/<u>(.*?)<\/u>/s', '<text:span text:style-name="UNTERSTRICHEN">$1</text:span>', $str);

	// Durchgestrichen <strike>
	$str = preg_replace('/<strike>(.*?)<\/strike>/s', '<text:span text:style-name="DURCHGESTRICHEN">$1</text:span>', $str);

	// DIV align ersetzten
	$str = preg_replace('/<div align="center">(.*?)<\/div>/s', '<text:p text:style-name="ZENTRIERT">$1</text:p>', $str);
	$str = preg_replace('/<div align="left">(.*?)<\/div>/s', '<text:p text:style-name="LINKSBUENDIG">$1</text:p>', $str);
	$str = preg_replace('/<div align="right">(.*?)<\/div>/s', '<text:p text:style-name="RECHTSBUENDIG">$1</text:p>', $str);
	$str = preg_replace('/<div align="justify">(.*?)<\/div>/s', '<text:p text:style-name="LINKSBUENDIG">$1</text:p>', $str);
	$str = preg_replace('/<div>(.*?)<\/div>/s', '$1', $str);
	$str = preg_replace('/<div>(.*?)<\/div>/s', '$1', $str);


	// List item <li>

	// 3. Ebene <ul> <ul> <li>
	$str = preg_replace('/<ul>(.*?)<ul>(.*?)<li>(.*?)<\/li>(.*?)<\/ul>(.*?)<\/ul>/s', '<ul>$1<ul>$2<text:list-item><text:p text:style-name="PLIST"><text:span text:style-name="TLIST">$3</text:span></text:p></text:list-item>$4</ul>$5</ul>', $str);
	// 2. Ebene <ul> <li>
	$str = preg_replace('/<ul>(((?!<\/ul>).)+?)<li>(.*?)<\/li>(.*?)<\/ul>/s', '<ul>$1<text:list-item><text:p text:style-name="PLIST"><text:span text:style-name="TLIST">$3</text:span></text:p></text:list-item>$4</ul>', $str);

	// 1. Ebene
	$str = preg_replace('/<li>(.*?)<\/li>/s', '<text:list-item><text:p text:style-name="PLIST"><text:span text:style-name="TLIST">$1</text:span></text:p></text:list-item>', $str);
	$str = preg_replace('/<li>(.*?)<\/li>/s', '<text:list-item><text:p text:style-name="PLIST"><text:span text:style-name="TLIST">$1</text:span></text:p></text:list-item>', $str);

	/* Alle UL ersetzen die innerhalb eines andern UL vorkommen und versetzen
	   damit die ULs innerhalb des darueberliegenden LI sind

	<ul>
		<li></li>
		<ul>
			<li></li>
		</ul>
	</ul>
	=>
	<ul>
		<li>
			<ul>
				<li></li>
			</ul>
		</li>
	</ul>
	*/
	$str = preg_replace('/<\/text:list-item>(\s*)<ul>(.*?)<\/ul>/s', '$1<text:list text:style-name="LIST_UNORDERED">$2</text:list></text:list-item>', $str);
	// Ein 2. Mal ausfuehren weil es sonst bei Mehrfach verschachtelten eintraegen beim 1. Mal uebersprungen wird
	$str = preg_replace('/<\/text:list-item>(\s*)<ul>(.*?)<\/ul>/s', '$1<text:list text:style-name="LIST_UNORDERED">$2</text:list></text:list-item>', $str);
	$str = preg_replace('/<\/text:list-item>(\s*)<ul>(.*?)<\/ul>/s', '$1<text:list text:style-name="LIST_UNORDERED">$2</text:list></text:list-item>', $str);

	/*
	<LI>
		<UL> <--
			<LI>
		</UL> <--
	</LI>*/
	$str = preg_replace('/<ul>(.*?)<text:list-item>(.*?)<ul>(.*?)<\/ul>/s', '<ul>$1<text:list-item>$2<text:list text:style-name="LIST_UNORDERED">$3</text:list>', $str);
	$str = preg_replace('/<text:list-item>(.*?)<ul>(.*?)<\/ul>/s', '<text:list-item>$1<text:list text:style-name="LIST_UNORDERED">$2</text:list>', $str);


	/* Bei UL innerhalb von LI muss der span und p tag geschlossen werden
	<ol> $1
		<li> $2
			<ul> $3
				<li> $4
					<ul> $5
						<li> $6
							<ul>
								....$7
							</ul>
						</li>
					</ul>
				</li>
			</ul>
		</li>
	*/

//	$str = preg_replace('/<ol>(.*?)<text:list-item>(.*?)<ul>(.*?)<text:list-item>(.*?)<ul>(.*?)<text:list-item>(.*?)<ul>(.*?)<\/ul>/s','<ol>$1<text:list-item>$2<ul>$3<text:list-item>$4<ul>$5<text:list-item>$6</text:span></text:p><text:list text:style-name="LIST_UNORDERED">$7</text:list><text:p text:style-name="PLIST"><text:span text:style-name="TLIST">',$str);

	/* Bei UL innerhalb von LI muss der span und p tag geschlossen werden
	<ol>
		<li>...
			<ul>
				<li>
					<ul>
						...
					</ul>
				</li>
			</ul>
			...
		</li>
	*/
//	$str = preg_replace('/<ol>(.*?)<text:list-item>(.*?)<ul>(.*?)<text:list-item>(.*?)<ul>(.*?)<\/ul>/s','<ol>$1<text:list-item>$2<ul>$3<text:list-item>$4</text:span></text:p><text:list text:style-name="LIST_UNORDERED">$5</text:list><text:p text:style-name="PLIST"><text:span text:style-name="TLIST">',$str);

	/*
	<ul>
		<ul>
			<li>
			</li>
		</ul>
	</ul>
	*/
//	$str = preg_replace('/<ul>(\s*)<ul>(.*?)<text:list-item>(.*?)<\/ul>/s','<ul>$1<text:list-item>$2<text:list text:style-name="LIST_UNORDERED"><text:list-item>$3</text:list></text:list-item>',$str);

	/* Bei UL innerhalb von LI muss der span und p tag geschlossen werden
	<ol>
		<li>...
			<ul>
				...
			</ul>
			...
		</li>
	*/
	/*
	 (((?!<\/ol>).)+)
	 sorgt dafuer dass ol nicht geschlossen wird

	 */
//	$str = preg_replace('/<ol>(.*?)<text:list-item>(.*?)<ul>(.*?)<\/ul>/s','<ol>$1<text:list-item>$2</text:span></text:p><text:list text:style-name="LIST_UNORDERED">$3</text:list><text:p text:style-name="PLIST"><text:span text:style-name="TLIST">',$str);

	// Alle UL aussen herum ersetzen
	$str = preg_replace('/<ul>(.*?)<\/ul>/s', '</text:p><text:list text:style-name="LIST_UNORDERED">$1</text:list><text:p text:style-name="PNORMAL">', $str);

	// Aufzaehlung <ol>
	//$str = preg_replace('/<ol>(.*?)<\/ol>/s','<text:list text:style-name="LIST_ORDERED">$1</text:list>',$str);
	$str = preg_replace('/<\/text:list-item>(\s*)<ol>(.*?)<\/ol>/s', '$1<text:list text:style-name="LIST_ORDERED">$2</text:list></text:list-item>', $str);

	// Alle OL aussen herum ersetzen
	$str = preg_replace('/<ol>(.*?)<\/ol>/s', '</text:p><text:list text:style-name="LIST_ORDERED">$1</text:list><text:p text:style-name="PNORMAL">', $str);

	// Font size Ersetzen TODO unterschiedliche groessen
	$str = str_replace('<font size="5">', '<text:span text:style-name="GROESSER">', $str);
	$str = str_replace('<font size="4">', '<text:span text:style-name="GROESSER">', $str);
	$str = str_replace('<font size="3">', '<text:span text:style-name="GROESSER">', $str);
	$str = str_replace('<font size="2">', '<text:span text:style-name="PNORMAL">', $str);
	$str = str_replace('<font size="1">', '<text:span text:style-name="PNORMAL">', $str);
	$str = str_replace('</font>', '</text:span>', $str);

	// Uebrige Font tags etnfernen
	$str = preg_replace('/<font .*?>/', '<text:span text:style-name="PNORMAL">', $str);

	// Blockquote Tags ersetzen
	$str = str_replace('<blockquote>', '</text:p><text:p text:style-name="PEINGERUECKT">', $str);
	$str = str_replace('</blockquote>', '</text:p><text:p text:style-name="PNORMAL"> ', $str);

	// Sonstiges
	// P Tags innerhalb von List-Items unterbrechen den globalen P tag nicht desalb werden diese separat ersetzt
	$str = preg_replace('/<text:list-item>(.*?)<p>(.*?)<\/p>(.*?)<\/text:list-item>/s', '<text:list-item>$1<text:p text:style-name="PNORMAL">$2</text:p>$3</text:list-item>', $str);
	// P Tags ersetzten - dazu wird der global P geschlossen und dann wieder geoeffnet
	$str = str_replace('<p>', '</text:p><text:p text:style-name="PNORMAL"> ', $str);
	$str = str_replace('</p>', '</text:p><text:p text:style-name="PNORMAL"> ', $str);


	// Aufzaehlungslisten korrigieren die nicht korrekt verschachtelt wurden
	// <list></p><list>
	$str = str_replace('<text:list text:style-name="LIST_UNORDERED"></text:p><text:list text:style-name="LIST_UNORDERED">', '<text:list text:style-name="LIST_UNORDERED"><text:list text:style-name="LIST_UNORDERED">', $str);
	//<p><span></List>
	$str = str_replace('<text:p text:style-name="PLIST"><text:span text:style-name="TLIST"></text:list>', '<text:p text:style-name="PLIST"><text:span text:style-name="TLIST"> </text:span></text:p></text:list>', $str);

	$str = str_replace('<text:p text:style-name="PLIST"><text:span text:style-name="TLIST"></text:p>', '<text:p text:style-name="PLIST"><text:span text:style-name="TLIST"> </text:span></text:p>', $str);

	// <p>..</span></p>
	$str = preg_replace('/<text:p text:style-name="PNORMAL">(((?!<text:span).)+?)<\/text:span><\/text:p>/s', '<text:p text:style-name="PNORMAL">$1</text:p>', $str);
	// <p></span></p>
	$str = str_replace('<text:p text:style-name="PNORMAL"></text:span></text:p>', '<text:p text:style-name="PNORMAL"></text:p>', $str);
	return $str;
}

/**
 * MS Markup aus HTML String entfernen bzw durch neutrale Tags ersetzen
 * ohne HTML Purifier
 * @param string $str String mit HTML Code aus WYSIWYG Editor
 * @return gesaueberter String
 */
function MSClean($str)
{
	// Kommentar entfernen
	$str = preg_replace('/<!--(.*)-->/Uis', '', $str);

	// <b style="mso-bidi-font-weight:normal"> ... </b> -> <b>...</b>
	$str = preg_replace('/<b style=".*?">(.*?)<\/b>/s', '<b>$1</b>', $str);

	$str = preg_replace('/<ul .*?>(.*?)<\/ul>/s', '<ul>$1</ul>', $str);
	$str = preg_replace('/<li .*?>(.*?)<\/li>/s', '<li>$1</li>', $str);
	$str = preg_replace('/<o:p>(.*?)<\/o:p>/s', '$1', $str);

	// <p class="MsoNormal"> ... </p> -> <p>...</p>
	$str = preg_replace('/<p class="MsoNormal">(.*?)<\/p>/s', '<p>$1</p>', $str);

	// <p class="MsoNormal" style="...">
	$str = preg_replace('/<p class="MsoNormal" .*?>(.*?)<\/p>/s', '<p>$1</p>', $str);
	$str = preg_replace('/<p class=".*?" style=".*?">(.*?)<\/p>/s', '<p>$1</p>', $str);
	$str = preg_replace('/<p style=".*?">(.*?)<\/p>/s', '<p>$1</p>', $str);
	$str = preg_replace('/<p align=".*?" class=".*?">(.*?)<\/p>/s', '<p>$1</p>', $str);
	$str = preg_replace('/<p class=".*?" align=".*?">(.*?)<\/p>/s', '<p>$1</p>', $str);

	$str = preg_replace('/<blockquote style=".*?">(.*?)<\/blockquote>/s', '<blockquote>$1</blockquote>', $str);
	$str = preg_replace('/<blockquote style=".*?">(.*?)<\/blockquote>/s', '<blockquote>$1</blockquote>', $str);
	$str = preg_replace('/<blockquote style=".*?">(.*?)<\/blockquote>/s', '<blockquote>$1</blockquote>', $str);

	// <span style="font-family:&quot;Arial Unicode MS&quot;,sans-serif;mso-ascii-font-family:Arial" lang="DE"> -> ...
	$str = preg_replace('/<span .*?>(.*?)<\/span>/s', '$1', $str);
	$str = preg_replace('/<span style=".*?">(.*?)<\/span>/s', '$1', $str);

	$str = preg_replace('/<span style=".*?">(.*?)<\/span>/s', '$1', $str);

	// <span lang="DE"> .. </span> -> ...
	$str = preg_replace('/<span lang="DE">(.*?)<\/span>/s', '$1', $str);

	$str = str_replace('<br style="line-height: 1.4;">', '<br>', $str);
	$str = preg_replace('/<div style=".*?">(.*?)<\/div>/s', '<div>$1</div>', $str);

	// &nbsp; entfernen
	$str = str_replace('&nbsp;', ' ', $str);

	// Tabelle entfernen - kann derzeit nicht dargestellt werden
	$str = preg_replace('/<table .*?>(.*?)<\/table>/s', '!! TABELLE WURDE ENTFERNT - Diese kann nicht dargestellt werden !!', $str);
	return $str;
}

/**
 * MS Markup aus HTML String entfernen bzw durch neutrale Tags ersetzen
 * mit Verwendung des HTML Purifier
 * @param string $str String mit HTML Code aus WYSIWYG Editor
 * @return gesaueberter String
 */
function MSCleanWithPurifier($str)
{
	//Purifier initialisieren
	$config = HTMLPurifier_Config::createDefault();
	//$config->set('HTML.Doctype', 'XHTML 1.0 Strict');
	//$config->set('HTML.TidyLevel', "light");
	//DirectLex  lexer implementation verwenden für verschachtelte ul und ol (z.B. ul in ol)
	$config->set('Core.LexerImpl', 'DirectLex');
	//verwendete Tags einschränken, leere Tags entfernen etc.
	$config->set("HTML.Allowed", "p, br, div, ul, ol, li, font, b, u, i, strike, blockquote, table, tr, td");
	$config->set("HTML.ForbiddenAttributes", array("b@style", "p@style", "br@style", "div@style", "ul@style", "ul@type", "ol@style", "ol@type", "li@style", "table@style", "tr@style", "td@style", "blockquote@style", "span@xml:lang", "span@lang", "span@style"));
	$config->set("Attr.ForbiddenClasses", array("MsoNormal"));
	$config->set("AutoFormat.RemoveEmpty.RemoveNbsp", 1);
	$config->set("AutoFormat.RemoveEmpty.Predicate", array('p' => array(), 'th' => array(), 'td' => array()));

	//Purifier zum Säubern des HTML anwenden
	$purifier = new HTMLPurifier($config);
	$str = $purifier->purify($str);

	$str = preg_replace('/<p class=".*?" style=".*?">(.*?)<\/p>/s', '<p>$1</p>', $str);
	$str = preg_replace('/<p align=".*?" class=".*?">(.*?)<\/p>/s', '<p>$1</p>', $str);
	$str = preg_replace('/<p class=".*?" align=".*?">(.*?)<\/p>/s', '<p>$1</p>', $str);

	//remove paragraph tags in blockquotes or divs
	/*	$str = preg_replace_callback( "/<blockquote>(.*?)<\/blockquote>/s", function( $match){
			return str_replace(array("<p>", "</p>", "<div>", "</div>"), "", $match[0]);
		}, $str);*/

	//remove blockquotes in paragraphs
	/*	$str = preg_replace_callback( "/<p>(.*?)<\/p>/s", function( $match){
			return str_replace(array("<blockquote>", "</blockquote>"), "", $match[0]);
		}, $str);*/

	// Blockquote Tags ersetzen - ansonsten gibt es Paragraph in Paragraph, was nicht in odt angezeigt werden kann
	//$str = preg_replace('/<blockquote>([^<>]+)<\/blockquote>/s', '<p>(.*?)</p>', $str);
	$str = str_replace('<blockquote>', '', $str);
	$str = str_replace('</blockquote>', '<br />', $str);

	// Tabelle entfernen - kann derzeit nicht dargestellt werden
	$str = preg_replace('/<table.*?>(.*?)<\/table>/s', '!! TABELLE WURDE ENTFERNT - Diese kann nicht dargestellt werden !!', $str);

	//divs ersetzen wo keine ol oder ul oder p
	$str = preg_replace('/<div>(?!.*?(<ul|<ol|<p))(.*?)<\/div>/s', '<p>$2</p>', $str);

	//restliche divs ersetzen (mit br für Zeilenumbruch
	$str = str_replace('<div>', '', $str);
	$str = str_replace('</div>', '<br />', $str);

	//Aufzählungen in Paragraphen können nicht dargestellt werden - daher werden die Paragraphen vorher geschlossen und nach den Aufzählungen wieder geöffnet
	preg_match_all('/(<ul>(.*?)<\/ul>|<ol>(.*?)<\/ol>)/', $str, $matches, PREG_OFFSET_CAPTURE, PREG_PATTERN_ORDER);
	$addedParagraphs = 0;
	foreach ($matches[0] as $match)
	{
		$strBeforeMatch = substr($str, 0, $match[1] + $addedParagraphs * 7);

		//wenn mehr ps geöffnet als geschlossen
		if (sizeof(allPositionsInString($strBeforeMatch, "/<p>/")) - sizeof(allPositionsInString($strBeforeMatch, "/<\/p>/")) == 1)
		{
			//string neu zusammensetzen
			$str = $strBeforeMatch.'</p>'.substr($str, $match[1] + $addedParagraphs * 7, strlen($match[0])).'<p>'.substr($str, $match[1] + strlen($match[0]) + $addedParagraphs * 7);
			//hinzuefügte Paragraphen mitspeichern, um neue Länge des Strings zu berücksichtigen
			$addedParagraphs++;
		}
	}

	//tags innerhalb li Tags entfernen - andernfalls wird Text in li nicht angezeigt (z.B. wenn formatiert)!
	$str = preg_replace_callback("/<li>(.*?)<\/li>/s", function ($match)
	{
		return str_replace(array("<font>", "</font>", "<b>", "</b>", "<strike>", "</strike>", "<i>", "</i>", "<u>", "</u>", "<div>", "</div>", "<p>", "</p>"), "", $match[0]);
	}, $str);

	//Text, der nicht von p-tags umschlossen ist wird von odt nicht angezeigt - mit p-tags umschließen. Aber nur, wenn  innerhalb des Texts keine Aufzählung oder kein p ist!
	$str = preg_replace("/<\/ul>(?!<ul>|<\/ul>|<li>|<\/li>|<\/ol>|<ol>|<p>|<\/p>)(.+?)(<p>|<ul>|<ol>|<\/li>|$)/s", "</ul><p>$1</p>$2", $str);
	$str = preg_replace("/<\/ol>(?!<ul>|<\/ul>|<li>|<\/li>|<\/ol>|<ol>|<p>|<\/p>)(.+?)(<p>|<ul>|<ol>|<\/li>|$)/s", "</ol><p>$1</p>$2", $str);
	$str = preg_replace("/<\/p>(?!<ul>|<\/ul>|<li>|<\/li>|<\/ol>|<ol>|<p>|<\/p>)(.+?)(<p>|<ul>|<ol>|<\/li>|$)/s", "</p><p>$1</p>$2", $str);

	//unnötige brs entfernen
	$str = preg_replace("/(<br \/>|<br>)[^\w]*<\/p>/s", "</p>", $str);
	$str = preg_replace("/<p>[^\w]*(<br \/>|<br>)[^\w]*<\/p>/s", "<p></p>", $str);

	//doppelte brs entfernen
	//$str = preg_replace("/(<br \/>|<br>)+/s", "<br />", $str);

	//Abstand zwischen Paragraph und ul/ol
	$str = preg_replace("/(?<!<p>)<\/p>(<ul>|<ol>)/s", "</p><p></p>$1", $str);
	//Abstand zwischen schließendem ul/ol und Paragraph
	$str = preg_replace("/(<\/ul>|<\/ol>)<p>(?!<\/p>)/s", "$1<p></p><p>", $str);
	//Abstand zwischen Paragraph und anderem Paragraph
	$str = preg_replace("/(?<!<p>)<\/p><p>/s", "</p><p></p><p>", $str);
	$str = preg_replace("/<\/p><p>(?!<\/p>)/s", "</p><p></p><p>", $str);
	//Doppelparagraphen entfernen
	$str = preg_replace("/(<p>[^\w]*<\/p>)+/s", "<p></p>", $str);

	return $str;
}

function OutputFormat($str)
{
	$str = str_replace('<text:list ', "\n<text:list ", $str);
	$str = str_replace('</text:list>', "\n</text:list>", $str);
	$str = str_replace('<text:list-item>', "\n\t<text:list-item>", $str);
	$str = str_replace('<ul>', "\n<ul>", $str);
	$str = str_replace('</ul>', "\n</ul>", $str);
	$str = str_replace('<ol>', "\n<ol>", $str);
	$str = str_replace('</ol>', "\n</ol>", $str);
	$str = str_replace('<li>', "\n\t<li>", $str);
	return $str;
}

/**
 * liefert alle Positionen eines Patterns in einem String
 * @param $str
 * @param $pattern
 * @return array
 */
function allPositionsInString($str, $pattern)
{
	preg_match_all($pattern, $str, $matches, PREG_OFFSET_CAPTURE);
	$positions = array();
	$allmatches = $matches[0];
	foreach ($allmatches as $match)
	{
		array_push($positions, $match[1]);
	}
	return $positions;
}

?>
