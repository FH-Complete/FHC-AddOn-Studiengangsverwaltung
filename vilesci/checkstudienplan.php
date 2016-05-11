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


	echo '<h2>Bei folgenden kMod ist die "Bewertung" mit "ja" angegeben. (Muss "nein" sein)</h2>';
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
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
		{
			echo '<span class="ok">OK</span>';
		}
	}

	// Integratives Pflichtmodul das nicht bewertet wird
	echo '<h2>Bei folgenden iMod ist die "Bewertung" mit "nein" angegeben. (Muss "ja" sein)</h2>';

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
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
		{
			echo '<span class="ok">OK</span>';
		}
	}

	// Pflichtmodule bei denen die Attribute nicht passen
	echo '<h2>Bei folgenden Pflichtmodulen sind die Attribute "StudPlan", "Pflicht", "Gen" usw nicht korrekt kodiert.</h2>';
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester
			FROM 
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND	tbl_studienplan_lehrveranstaltung.pflicht=true

				AND (tbl_lehrveranstaltung.lehrform_kurzbz='kMod' 
					AND (benotung=true OR lehrauftrag=true OR lehre=false OR lvinfo=false OR genehmigung=false OR curriculum=false)
					)
				AND (tbl_lehrveranstaltung.lehrform_kurzbz='iMod' 
					AND (benotung=false OR lehrauftrag=true OR lehre=false OR lvinfo=false OR genehmigung=false OR curriculum=false)
					)
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			echo '<span class="ok">OK</span>';
	}

	// Bei folgenden Wahlmodulen passt die Attributskodierung nicht
	echo '<h2>Bei folgenden Wahlmodulen sind die Attribute "StudPlan","Pflicht","Gen", usw nicht korrekt kodiert.</h2>';
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester
			FROM 
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND	tbl_studienplan_lehrveranstaltung.pflicht=true
				AND tbl_lehrveranstaltung.lehrform_kurzbz='kMod'
				AND (benotung=true OR lehrauftrag=true OR lehre=false OR lvinfo=false OR genehmigung=false OR curriculum=false)
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			echo '<span class="ok">OK</span>';
	}

	// Bei folgenden Wahlmodulen passt die Attributskodierung nicht
	echo '<h2>Bei folgenden Sonstigen Modulen sind die Attribute "StudPlan","Pflicht","Gen", usw nicht korrekt kodiert.</h2>';
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester
			FROM 
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND	tbl_studienplan_lehrveranstaltung.curriculum=false
				AND tbl_lehrveranstaltung.lehrform_kurzbz='kMod'
				AND (pflicht=true OR genehmigung=true OR benotung=true OR zeugnis=true OR lehrauftrag=true)
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			echo '<span class="ok">OK</span>';
	}

	// Module in denen zu viele Pflich LVs sind
	echo '<h2>Bei folgenden Modulen sind die Modul-ECTS kleiner als die ECTS-Summe der dazugehörigen Pflich-Lehrveranstaltungen</h2>';
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
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			echo '<span class="ok">OK</span>';
	}

	echo '<h2>Bei folgenden Modulen sind die Modul-ECTS größer als die ECTS-Summe der dazugehörgien Lehrveranstaltungen.</h2>';
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
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			echo '<span class="ok">OK</span>';
	}


	// Pruefen ob ECTS, LVS, ALVS, ... leer sind
	echo '<h2>Bei folgenden LVs sind eines oder mehrere der folgenden Attribute nicht angegeben: ECTS, SWS, LVS, ALVS, LAS, LVPLS</h2>';
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester, tbl_lehrveranstaltung.lehrveranstaltung_id
			FROM 
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND (ects is null OR sws is null OR lvs is null OR alvs is null OR lvps is null)
				AND lehrtyp_kurzbz='lv'
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung.' ('.$row->lehrveranstaltung_id.')';
			}
		}
		else
			echo '<span class="ok">OK</span>';
	}
	

	// Pruefen ob ECTS>=SWS
	echo '<h2>Bei folgenden LVs sind die ECTS &lt; SWS</h2>';
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
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			echo '<span class="ok">OK</span>';
	}
	
	// Pruefen ob ALVS>=LVS
	echo '<h2>Bei folgenden LVs sind die ALVS &lt; LVS</h2>';
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
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung .'( ALVS: '.$row->alvs.' / LVS: '.$row->lvs.' )';
			}
		}
		else
			echo '<span class="ok">OK</span>';
	}

	// Pruefen ob unterschiedliche Wochenteiler vorhanden sind
	echo '<h2>Bei folgenden LVs ergeben sich unterschiedliche Werte für die Semesterwochen (Berechnung: LVS / SWS)</h2>';
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
		else
			echo '<span class="ok">OK</span>';
	}	

	// Pruefen ob LVPLS>ALVS
	echo '<h2>Bei folgenden LVs sind die LVPLS &gt; ALVS</h2>';
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester, 
				tbl_lehrveranstaltung.alvs, tbl_lehrveranstaltung.lvps
			FROM 
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND tbl_lehrveranstaltung.lvps>tbl_lehrveranstaltung.alvs
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung .'( ALVS: '.$row->alvs.' / LVPLS: '.$row->lvps.' )';
			}
		}
		else
			echo '<span class="ok">OK</span>';
	}


	// Lehrveranstaltungen bei denen Studienplan=True muss eine Englische Bezeichnung vorhanden sein
	echo '<h2>Bei folgenden Pflicht- und Wahl- LVs fehlt die englische Bezeichnung</h2>';
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester
			FROM 
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND	tbl_studienplan_lehrveranstaltung.curriculum=true
				AND (tbl_lehrveranstaltung.bezeichnung_english is null OR tbl_lehrveranstaltung.bezeichnung_english is null)
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			echo '<span class="ok">OK</span>';
	}

	// Lehrveranstaltungen bei denen Studienplan=True muss eine Englische Bezeichnung vorhanden sein
	echo '<h2>Bei folgenden LVs ist die Lehrform nicht angegeben</h2>';
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester
			FROM 
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND	lehrtyp_kurzbz='lv' AND lehrform_kurzbz is null
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			echo '<span class="ok">OK</span>';
	}

	// Bei folgenden PflichtLVs passt die Attributskodierung nicht
	echo '<h2>Bei folgenden Pflicht-LVs sind die Attribute "StudPlan","Pflicht","Gen", usw nicht korrekt kodiert.</h2>';
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester
			FROM 
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND tbl_lehrveranstaltung.lehrtyp_kurzbz='lv'
				AND tbl_studienplan_lehrveranstaltung.pflicht=true
				AND	tbl_studienplan_lehrveranstaltung.curriculum=true
				AND (genehmigung=false OR benotung=false OR benotung=false OR zeugnis=false OR lehrauftrag=false OR curriculum=false OR lehre=false OR lvinfo=false)
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			echo '<span class="ok">OK</span>';
	}

	// Bei folgenden WahlLVs passt die Attributskodierung nicht
	echo '<h2>Bei folgenden Wahl-LVs sind die Attribute "StudPlan","Pflicht","Gen", usw nicht korrekt kodiert.</h2>';
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester
			FROM 
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND tbl_lehrveranstaltung.lehrtyp_kurzbz='lv'
				AND tbl_studienplan_lehrveranstaltung.pflicht=false
				AND tbl_studienplan_lehrveranstaltung.curriculum=true
				AND (lehre=false OR lvinfo=false OR benotung=false OR zeugnis=false OR lehrauftrag=false)
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			echo '<span class="ok">OK</span>';
	}

	// Bei folgenden Sonstigen LVs passt die Attributskodierung nicht
	echo '<h2>Bei folgenden Sonstigen LVs sind die Attribute "StudPlan","Pflicht","Gen", usw nicht korrekt kodiert.</h2>';
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester
			FROM 
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND tbl_lehrveranstaltung.lehrtyp_kurzbz='lv'
				AND tbl_studienplan_lehrveranstaltung.pflicht=false
				AND tbl_studienplan_lehrveranstaltung.curriculum=false
				AND (genehmigung=true OR benotung=true OR zeugnis=true)
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			echo '<span class="ok">OK</span>';
	}
	
	// ZUSATZPRUEFUNG: LAS > ALVS
	echo '<h2>ZUSATZPRÜFUNG: Bei folgenden LVs sind LAS &gt; ALVS</h2>';
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester
			FROM 
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND	tbl_lehrveranstaltung.las>tbl_lehrveranstaltung.alvs 
				AND las is not null AND las>0 AND alvs is not null AND alvs>0				
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				echo '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			echo '<span class="ok">OK</span>';
	}


	if($fehler==0)
		echo '<br><br><span class="ok">Keine Fehler gefunden - Studienplan OK</span>';
	else
		echo '<br><br><span class="error">Es wurden '.$fehler.' Fehler gefunden</span>';
}

