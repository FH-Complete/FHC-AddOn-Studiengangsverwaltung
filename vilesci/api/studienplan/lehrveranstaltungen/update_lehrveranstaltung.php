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
 * Authors: Stefan Puraner	<puraner@technikum-wien.at>
 *			Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/studienplanAddonStgv.class.php');
require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/editLehrveranstaltung",null,"suid"))
{
	$error = array("message"=>"Sie haben nicht die Berechtigung um Lehrveranstaltungen zu editieren.", "detail"=>"stgv/editLehrveranstaltung");
	returnAJAX(FALSE, $error);
}

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];

$lehrveranstaltung = mapDataToLehrveranstaltung($data);

/*
$studienplan = new StudienplanAddonStgv();
$studienplan->getStudienplanLehrveranstaltung($lehrveranstaltung->lehrveranstaltung_id);

if(count($studienplan->result) > 1)
{
	$studienplanIds = "StplIds: ";
	foreach($studienplan->result as $stpl)
	{
		$studienplanIds .= $stpl->studienplan_id.", ";
	}

	$studienplanIds = rtrim($studienplanIds, ", ");

	$error = array("message"=>"Lehrveranstaltung ist in anderen Studienplänen vorhanden. Es konnten nicht alle Daten gepseichert werden.", "detail"=>$studienplanIds);
	returnAJAX(false, $error);
}
*/


if($lehrveranstaltung->save())
{
	returnAJAX(true, array($lehrveranstaltung->lehrveranstaltung_id));
}
else
{
	$error = array("message"=>"Fehler beim Speichern der Lehrveranstaltung.", "detail"=>$lehrveranstaltung->errormsg);
	returnAJAX(false, $error);
}

function mapDataToLehrveranstaltung($data)
{
	$lv = new lehrveranstaltung($data->lehrveranstaltung_id);

	$gesperrt = $lv->isGesperrt($data->lehrveranstaltung_id);
	if(!$gesperrt && isset($data->bezeichnung))
	{
		$lv->bezeichnung = $data->bezeichnung;
		$lv->studiengang_kz = $data->studiengang_kz;
		$lv->kurzbz = $data->kurzbz;
		$lv->lehrform_kurzbz = $data->lehrform_kurzbz;
		$lv->semester = $data->semester;
		$lv->ects = $data->ects;
		$lv->semesterstunden = $data->semesterstunden;
		$lv->anmerkung = $data->anmerkung;
		$lv->lehre = parseBoolean($data->lehre);
		$lv->lehreverzeichnis = $data->lehreverzeichnis;
		$lv->aktiv = parseBoolean($data->aktiv);
		$lv->planfaktor = $data->planfaktor;
		$lv->planlektoren = $data->planlektoren;
		$lv->planpersonalkosten = $data->planpersonalkosten;
		$lv->plankostenprolektor = $data->plankostenprolektor;
		$lv->sort = $data->sort;
		$lv->zeugnis = parseBoolean($data->zeugnis);
		$lv->projektarbeit = parseBoolean($data->projektarbeit);
		$lv->sprache = $data->sprache;
		$lv->koordinator = $data->koordinator;
		$lv->bezeichnung_english = $data->bezeichnung_english;
		$lv->orgform_kurzbz = $data->orgform_kurzbz;
		$lv->incoming = $data->incoming;
		$lv->lehrtyp_kurzbz = $data->lehrtyp_kurzbz;
		$lv->lehrmodus_kurzbz = $data->lehrmodus_kurzbz;
		$lv->oe_kurzbz = $data->oe_kurzbz;
		$lv->raumtyp_kurzbz = $data->raumtyp_kurzbz;
		$lv->anzahlsemester = $data->anzahlsemester;
		$lv->semesterwochen = $data->semesterwochen;
		$lv->lvnr = $data->lvnr;
		$lv->semester_alternativ = $data->semester_alternativ;
		$lv->farbe = $data->farbe;
		$lv->sws = $data->sws;
		$lv->lvs = $data->lvs;
		$lv->alvs = $data->alvs;
		$lv->lvps = $data->lvps;
		$lv->las = $data->las;
		$lv->benotung = parseBoolean($data->benotung);
		$lv->lvinfo = parseBoolean($data->lvinfo);
		$lv->lehrauftrag = parseBoolean($data->lehrauftrag);
	}
	elseif($gesperrt && isset($data->bezeichnung))
	{
		$lv->anmerkung = $data->anmerkung;
		$lv->lehre = parseBoolean($data->lehre);
		$lv->sort = $data->sort;
		$lv->zeugnis = parseBoolean($data->zeugnis);
		$lv->projektarbeit = parseBoolean($data->projektarbeit);
		$lv->incoming = $data->incoming;
		$lv->raumtyp_kurzbz = $data->raumtyp_kurzbz;
		$lv->lvs = $data->lvs;
		$lv->alvs = $data->alvs;
		$lv->lvps = $data->lvps;
		$lv->las = $data->las;
		$lv->benotung = parseBoolean($data->benotung);
		$lv->lvinfo = parseBoolean($data->lvinfo);
		$lv->lehrauftrag = parseBoolean($data->lehrauftrag);
	}
	else
	{
		$lv->lehre = parseBoolean($data->lehre);
		$lv->zeugnis = parseBoolean($data->zeugnis);
		$lv->benotung = parseBoolean($data->benotung);
		$lv->lvinfo = parseBoolean($data->lvinfo);
		$lv->lehrauftrag = parseBoolean($data->lehrauftrag);
	}
	$lv->updatevon = get_uid();

	return $lv;
}

?>
