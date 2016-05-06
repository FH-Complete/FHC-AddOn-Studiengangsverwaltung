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
 * Authors: Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Prueft nicht genehmigte Studienplaene auf Gueltigkeit
 */
require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/studienplan.class.php');
require_once('../../../include/benutzerberechtigung.class.php');


$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('stgv/changeStplAdmin',null,'s'))
	die($rechte->errormsg);

$db = new basis_db();
$studienplan = new studienplan();

if(isset($_GET['studienplan_id']))
{
	$studienplan->loadStudienplan($_GET['studienplan_id']);
}

$fehler=0;
	
echo '<doctype html>
<html>
<head>
		<meta charset="utf-8">
		<link rel="stylesheet" href="../../../skin/fhcomplete.css" type="text/css">
		<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
		
</head>
<body>
<h1>Plausibilitätsprüfung von Studienplänen</h1>
	<form method="GET" action="checkstudienplan.php">';

$qry = "SELECT distinct tbl_studienplan.studienplan_id, tbl_studienplan.bezeichnung
		FROM
			lehre.tbl_studienplan 
			JOIN lehre.tbl_studienordnung USING(studienordnung_id)
		WHERE
			tbl_studienordnung.status_kurzbz NOT IN('approved','expired','notApproved')
		ORDER BY tbl_studienplan.bezeichnung";

echo '<select name="studienplan_id">';
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		if($row->studienplan_id==$studienplan->studienplan_id)
			$selected = 'selected';
		else
			$selected = '';
		echo '<option value="'.$row->studienplan_id.'" '.$selected.'>'.$row->bezeichnung.'</option>';
	}
}
echo '</select>';
echo '	<input type="submit" value="pruefen">
	</form>
';

if($studienplan->studienplan_id!='')
{
	echo 'Prüfe Studienplan '.$studienplan->bezeichnung.'...<br><br>';

	// Module in denen nicht genügend ECTS sind um das Modul abzuschließen
	$qry = "
	SELECT
		tbl_lehrveranstaltung.bezeichnung, sl2.semester
	FROM
		lehre.tbl_studienordnung 
		JOIN lehre.tbl_studienplan using(studienordnung_id)
		JOIN lehre.tbl_studienplan_lehrveranstaltung sl2 USING(studienplan_id)
		JOIN lehre.tbl_lehrveranstaltung using(lehrveranstaltung_id)
	WHERE
		tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id, FHC_INTEGER)."
		AND tbl_lehrveranstaltung.ects>(
			select sum(ects) 
			FROM 
				lehre.tbl_studienplan_lehrveranstaltung sl1 join lehre.tbl_lehrveranstaltung l1 using(lehrveranstaltung_id)
				where sl1.studienplan_lehrveranstaltung_id_parent=sl2.studienplan_lehrveranstaltung_id)";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			echo '<b>In folgenden Modulen sind nicht genügend ECTS vorhanden um das Modul abzuschließen:</b><br><br>';
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
	}


	// Module in denen zu viele Pflich LVs sind
	$qry="
	SELECT
		tbl_studienplan.bezeichnung, tbl_lehrveranstaltung.bezeichnung, sl2.semester
	FROM
		lehre.tbl_studienordnung 
		JOIN lehre.tbl_studienplan using(studienordnung_id)
		JOIN lehre.tbl_studienplan_lehrveranstaltung sl2 USING(studienplan_id)
		JOIN lehre.tbl_lehrveranstaltung using(lehrveranstaltung_id)
	WHERE
		tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id, FHC_INTEGER)."
		AND tbl_lehrveranstaltung.ects<(
			select sum(ects) 
			FROM 
				lehre.tbl_studienplan_lehrveranstaltung sl1 join lehre.tbl_lehrveranstaltung l1 using(lehrveranstaltung_id)
				where sl1.studienplan_lehrveranstaltung_id_parent=sl2.studienplan_lehrveranstaltung_id
				and sl1.pflicht)";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			echo '<br><br><b>In folgenden Modulen sind mehr PflichtLVs (ECTS) vorhanden als gesamt-ECTS im Modul:</b><br><br>';
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
	}

	// Pruefen ob ECTS>=SWS
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester
			FROM 
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND tbl_lehrveranstaltung.ects<sws
				AND sws is not null AND ects is not null AND ects>0
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			echo '<br><br><b>Bei folgenden Lehrveranstaltungen sind ECTS kleiner als die SWS</b><br><br>';
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
	}
	
	// Pruefen ob ALVS>=LVS
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester, 
				tbl_lehrveranstaltung.alvs, tbl_lehrveranstaltung.lvs
			FROM 
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND tbl_lehrveranstaltung.alvs<lvs
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			echo '<br><br><b>Bei folgenden Lehrveranstaltungen sind die ALVS kleiner als die LVS</b><br><br>';
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung .'( ALVS: '.$row->alvs.' / LVS: '.$row->lvs.' )';
			}
		}
	}

	// Pruefen ob unterschiedliche Wochenteiler vorhanden sind
	$qry = "SELECT
				distinct tbl_lehrveranstaltung.lvs/tbl_lehrveranstaltung.sws as wochenteiler
			FROM 
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND lvs is not null and lvs>0 and sws is not null and sws>0
			";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>1)
		{
			echo '<br><br><b>Es sind unterschiedliche Wochenteiler vorhanden</b><br><br>';
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br><u>'.number_format($row->wochenteiler,2).'</u>';

				$qry = "SELECT
						tbl_studienplan_lehrveranstaltung.semester, tbl_lehrveranstaltung.bezeichnung
					FROM 
						lehre.tbl_studienplan
						JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
						JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
					WHERE
						tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
						AND (tbl_lehrveranstaltung.lvs/tbl_lehrveranstaltung.sws)=".$db->db_add_param($row->wochenteiler)."
						AND lvs is not null and lvs>0 and sws is not null and sws>0
					ORDER BY tbl_studienplan_lehrveranstaltung.semester
					";
				$cnt=0;
				if($result_lv = $db->db_query($qry))
				{
					while($row_lv = $db->db_fetch_object($result_lv))
					{
						$cnt++;
						echo "<br>&nbsp;&nbsp;&nbsp;Semester ".$row_lv->semester.' - '.$row_lv->bezeichnung;

						if($cnt>5)
						{
							echo "<br>&nbsp;&nbsp;&nbsp;... noch ".($db->db_num_rows($result_lv)-5).' weitere';
							break;
						}
					}
				}
			}
		}
	}

	// Integratives Pflichtmodul das nicht bewertet wird
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester
			FROM 
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND	tbl_lehrveranstaltung.benotung=false
				AND tbl_lehrveranstaltung.lehrform_kurzbz='iMod'
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			echo '<br><br><b>Integratives Modul muss bewertet werden hat aber Bewertung auf Nein gesetzt</b><br><br>';
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
	}

	// Kumulatives Modul das bewertet wird
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester
			FROM 
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND	tbl_lehrveranstaltung.benotung=true
				AND tbl_lehrveranstaltung.lehrform_kurzbz='kMod'
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			echo '<br><br><b>Kum. Modul darf nicht bewertet werden hat aber Bewertung auf Ja gesetzt</b><br><br>';
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
	}
	
	if($fehler==0)
		echo '<br><br><span class="ok">Keine Fehler gefunden - Studienplan OK</span>';
	else
		echo '<br><br><span class="error">Es wurden '.$fehler.' Fehler gefunden</span>';
}

