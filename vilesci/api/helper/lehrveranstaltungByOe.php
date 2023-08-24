<?php
/* Copyright (C) 2018 fhcomplete.org
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
 */
require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/lehrveranstaltung.class.php');
require_once('../../../../../include/studienplan.class.php');
require_once('../functions.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if (!$rechte->isBerechtigt('addon/studiengangsverwaltung'))
{
	returnAJAX(FALSE, array("message"=>"Sie haben keine Berechtigung.", "detail"=>$rechte->errormsg));
}

$oe_kurzbz = filter_input(INPUT_GET, "oe_kurzbz");
$lehrtyp_kurzbz = filter_input(INPUT_GET, "lehrtyp_kurzbz");
$semester = filter_input(INPUT_GET, "semester");
$lehrveranstaltung_id = filter_input(INPUT_GET, "lv_id");
$studiengang_kz = filter_input(INPUT_GET, "studiengang_kz");
$sort = filter_input(INPUT_GET, "sort");
$order = filter_input(INPUT_GET, "order");

$sort = explode(",",$sort);
$order = explode(",",$order);

$sortString = null;

foreach($sort as $key=>$s)
{
	$sortString .= $s." ".$order[$key].", ";
}

$sortString = substr($sortString,0,-2);

if($sortString == " ")
	$sortString = null;

if(is_null($oe_kurzbz))
{
	returnAJAX(false, "Variable oe_kurzbz nicht gesetzt");
}
elseif(is_null($lehrtyp_kurzbz))
{
	returnAJAX(false, "Variable lehrtyp_kurzbz nicht gesetzt");
}
elseif(is_null($semester))
{
	returnAJAX(false, "Variable semester nicht gesetzt");
}
elseif(is_null($studiengang_kz))
{
	returnAJAX(false, "Variable studiengang_kz nicht gesetzt");
}
elseif($oe_kurzbz === false || $lehrtyp_kurzbz === false || $semester === false || $studiengang_kz == false)
{
	returnAJAX(false, "Fehler beim lesen der GET Variablen");
}

if($semester == "null")
{
	$semester = null;
}

if($lehrveranstaltung_id == "undefined")
{
	$lehrveranstaltung_id = null;
}

$lehrveranstaltung = new lehrveranstaltung();
$lv_array = array();

if ($lehrveranstaltung_id != null)
{
	if(!$lehrveranstaltung->load_lv_from_id($lehrveranstaltung_id))
	{
		returnAJAX(false, $lehrveranstaltung->errormsg);
	}
}
elseif(($oe_kurzbz == "alle") && ($studiengang_kz != "alle"))
{
	if(!$lehrveranstaltung->load_lva($studiengang_kz, $semester, null, null, true, $sortString, null, $lehrtyp_kurzbz))
	{
		returnAJAX(false, $lehrveranstaltung->errormsg);
	}
}
elseif (($oe_kurzbz != "alle") && ($studiengang_kz == "alle"))
{
	if(!$lehrveranstaltung->load_lva_oe($oe_kurzbz, true, $lehrtyp_kurzbz, $sortString, $semester))
	{
		returnAJAX(false, $lehrveranstaltung->errormsg);
	}
}
else
{
	if(!$lehrveranstaltung->load_lva($studiengang_kz, $semester, null, null, true, $sortString, $oe_kurzbz, $lehrtyp_kurzbz))
	{
		returnAJAX(false, $lehrveranstaltung->errormsg);
	}
}

foreach($lehrveranstaltung->lehrveranstaltungen as $key=>$lv)
{
	$temp = new stdClass();
	$temp->id = $lv->lehrveranstaltung_id;
	$temp->lehrveranstaltung_id = $lv->lehrveranstaltung_id;
	$temp->studiengang_kz = $lv->studiengang_kz;
	$temp->bezeichnung = $lv->bezeichnung;
	$temp->ects = $lv->ects;
	$temp->type = $lv->lehrtyp_kurzbz;
	$temp->lehrtyp_kurzbz = $lv->lehrtyp_kurzbz;
	$temp->kurzbz = $lv->kurzbz;
	$temp->semester = $lv->semester;
	$temp->sprache = $lv->sprache;
	$temp->semesterstunden = $lv->semesterstunden;
	$temp->lehrform_kurzbz = $lv->lehrform_kurzbz;
	$temp->lehrmodus_kurzbz = $lv->lehrmodus_kurzbz;
	$temp->bezeichnung_english = $lv->bezeichnung_english;
	$temp->orgform_kurzbz = $lv->orgform_kurzbz;
	$temp->incoming = $lv->incoming;
	$temp->oe_kurzbz = $lv->oe_kurzbz;
	$temp->semesterwochen = $lv->semesterwochen;
	$temp->lvnr = $lv->lvnr;
	$temp->sws = $lv->sws;
	$temp->lvs = $lv->lvs;
	$temp->alvs = $lv->alvs;
	$temp->lvps = $lv->lvps;
	$temp->las = $lv->las;
	$temp->benotung = $lv->benotung;
	$temp->lvinfo = $lv->lvinfo;
	$temp->zeugnis = $lv->zeugnis;
	$temp->lehre = $lv->lehre;
	$temp->lehrauftrag = $lv->lehrauftrag;
	$temp->anmerkung = $lv->anmerkung;

	$studienplan = new studienplan();
	$studienplan->getStudienplanLehrveranstaltung($lv->lehrveranstaltung_id);
	$temp->zugewieseneStudienplaene = '';
	foreach ($studienplan->result as $row_stpl)
		$temp->zugewieseneStudienplaene .= $row_stpl->bezeichnung.' ';
	
	array_push($lv_array, $temp);
}


returnAJAX(true, $lv_array)


?>
