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
{
	$stg_arr=array();

	$stg_arr = $rechte->getStgKz('stgv/createLehrveranstaltung');
}


$db = new basis_db();
$studienplan = new studienplan();

if(isset($_GET['studienplan_id']))
{
	$studienplan->loadStudienplan($_GET['studienplan_id']);
}

$fehler=0;
$output = '';

$nummerierung = 1;
if($studienplan->studienplan_id!='')
{
	$output .= '<br><h2>'.$nummerierung.'. Bei folgenden kumulativen Modulen ist die "Bewertung" mit "ja" angegeben. Bitte auf "nein" ändern.</h2>';
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
				$output .= '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
		{
			$output .= '<span class="ok">OK</span>';
		}
	}

	// Integratives Pflichtmodul das nicht bewertet wird
	$output .= '<br><br><br><h2>'.++$nummerierung.'. Bei folgenden integrativen Modulen ist die "Bewertung" mit "nein" angegeben. Bitte auf "ja" ändern</h2>';

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
				$output .= 'Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
		{
			$output .= '<span class="ok">OK</span>';
		}
	}

	// Pflichtmodule bei denen die Attribute nicht passen
	$output .= '<br><br><br><h2>'.++$nummerierung.'. Bei folgenden <b><u>Pflichtmodulen</u></b> sind die Attribute "StudPlan", "Pflicht", "Gen" usw nicht korrekt kodiert.<br>';
	$output .= 'Bitte die Änderungen gemäß dem <a href="https://wiki.fhcomplete.info/doku.php?id=addons:stgvt-sto_content" target="_blank">vorgesehenen Schema</a> vornehmen</h2>';
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester
			FROM
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND	tbl_studienplan_lehrveranstaltung.pflicht=true
				AND
				(
					(tbl_lehrveranstaltung.lehrform_kurzbz='kMod'
					AND (benotung=true OR lehrauftrag=true OR lehre=false OR lvinfo=false OR genehmigung=false OR export=false)
					)
				OR (tbl_lehrveranstaltung.lehrform_kurzbz='iMod'
					AND (benotung=false OR lehrauftrag=true OR lehre=false OR lvinfo=false OR genehmigung=false OR export=false)
					)
				)
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				$output .= '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			$output .= '<span class="ok">OK</span>';
	}

	// Bei folgenden Wahlmodulen passt die Attributskodierung nicht
	$output .= '<br><br><br><h2>'.++$nummerierung.'. Bei folgenden <b><u>Wahlmodulen</u></b> sind die Attribute "StudPlan","Pflicht","Gen" usw nicht korrekt kodiert.<br>';
	$output .= 'Bitte die Änderungen gemäß dem <a href="https://wiki.fhcomplete.info/doku.php?id=addons:stgvt-sto_content" target="_blank">vorgesehenen Schema</a> vornehmen</h2>';
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester
			FROM
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND	tbl_studienplan_lehrveranstaltung.pflicht=false
				AND tbl_lehrveranstaltung.lehrform_kurzbz='kMod'
				AND tbl_studienplan_lehrveranstaltung.export=true
				AND (benotung=true OR lehrauftrag=true OR lehre=false OR lvinfo=false)
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				$output .= '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			$output .= '<span class="ok">OK</span>';
	}

	// Bei folgenden sonstigen Modulen passt die Attributskodierung nicht
	$output .= '<br><br><br><h2>'.++$nummerierung.'. Bei folgenden <b><u>Sonstigen Modulen</u></b> sind die Attribute "StudPlan","Pflicht","Gen" usw nicht korrekt kodiert.<br>';
	$output .= 'Bitte die Änderungen gemäß dem <a href="https://wiki.fhcomplete.info/doku.php?id=addons:stgvt-sto_content" target="_blank">vorgesehenen Schema</a> vornehmen</h2>';
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester
			FROM
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND	tbl_studienplan_lehrveranstaltung.export=false
				AND tbl_lehrveranstaltung.lehrform_kurzbz='kMod'
				AND (pflicht=true OR genehmigung=true OR zeugnis=true)
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				$output .= '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			$output .= '<span class="ok">OK</span>';
	}

	$output .= '<br><br><br><h2>'.++$nummerierung.'. Bei folgenden Modulen ist der ECTS-Wert leer. Zulässig sind ganze Zahlen oder Komma-5 Werte. Falls es keine ECTS gibt, bitte "0" angeben</h2>';
	// ECTS null
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester
			FROM
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND	tbl_lehrveranstaltung.ects is null
				AND tbl_lehrveranstaltung.lehrform_kurzbz in('kMod','iMod')
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				$output .= '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
		{
			$output .= '<span class="ok">OK</span>';
		}
	}

	// Module in denen zu viele Pflicht LVs sind
	$output .= '<br><br><br><h2>'.++$nummerierung.'. Bei folgenden Modulen sind die Modul-ECTS kleiner als die ECTS-Summe der dazugehörigen Pflicht-Lehrveranstaltungen.<br>
	Bitte entsprechend ändern oder - falls keine Änderungen möglich sind - eine EMail an <a href="mailto: fhcomplete@'.DOMAIN.'">fhcomplete@'.DOMAIN.'</a> übermitteln</h2>';
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
				$output .= '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			$output .= '<span class="ok">OK</span>';
	}

	$output .= '<br><br><br><h2>'.++$nummerierung.'. Bei folgenden Modulen sind die Modul-ECTS größer als die ECTS-Summe der dazugehörgien Lehrveranstaltungen.<br>
	Bitte entsprechend ändern oder - falls keine Änderungen möglich sind - eine EMail an <a href="mailto: fhcomplete@'.DOMAIN.'">fhcomplete@'.DOMAIN.'</a> übermitteln</h2>';
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
				$output .= '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			$output .= '<span class="ok">OK</span>';
	}

	$output .= '<br><br><br><h2>'.++$nummerierung.'. Es gibt LVs die keinem Modul zugeordnet sind. Bitte diese einem Modul zuordnen.<br>
	 Falls kein entsprechendes Modul vorhanden ist, ist ein neues Modul zu erstellen.<br>
	 Falls die LVs nicht mehr relevant sind, diese aus dem Studienplan entfernen.</h2>';
	// ECTS null
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester
			FROM
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND	tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id_parent is null
				AND tbl_lehrveranstaltung.lehrform_kurzbz NOT in('kMod','iMod')
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				$output .= '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
		{
			$output .= '<span class="ok">OK</span>';
		}
	}

	// Pruefen ob ECTS, LVS, ALVS, ... leer sind
	$output .= '<br><br><br><h2>'.++$nummerierung.'. Bei folgenden LVs sind eines oder mehrere der folgenden Attribute nicht angegeben: ECTS, SWS, LVS, ALVS, LAS, LVPLS<br>
	Falls keine ECTS, SWS, ... vorgesehen sind, bitte die Zahl "0" eintragen. <br>Falls keine Änderungen möglich sind, eine EMail an <a href="mailto: fhcomplete@'.DOMAIN.'">fhcomplete@'.DOMAIN.'</a> übermitteln.</h2>';
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
				$output .= '<br>Semester '.$row->semester.' - '.$row->bezeichnung.' ('.$row->lehrveranstaltung_id.')';
			}
		}
		else
			$output .= '<span class="ok">OK</span>';
	}


	// Pruefen ob ECTS>=SWS
	$output .= '<br><br><br><h2>'.++$nummerierung.'. Bei folgenden LVs sind die ECTS &lt; SWS (Siehe <a href="https://wiki.fhcomplete.info/doku.php?id=addons:stgvt-sto-content-attr#berechnungsbeispiele" target="_blank">Berechnungsbeispiele</a>)<br>
	Falls es sich dabei um einen Fehler handelt, bitte die entsprechenden Änderungen vornehmen.<br>
	Falls keine Änderungen möglich sind, eine EMail an <a href="mailto: fhcomplete@'.DOMAIN.'">fhcomplete@'.DOMAIN.'</a> übermitteln</h2>';
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
				$output .= '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			$output .= '<span class="ok">OK</span>';
	}

	// Pruefen ob ALVS>=LVS
	$output .= '<br><br><br><h2>'.++$nummerierung.'. Bei folgenden LVs sind die ALVS &lt; LVS (Siehe <a href="https://wiki.fhcomplete.info/doku.php?id=addons:stgvt-sto-content-attr#berechnungsbeispiele" target="_blank">Berechnungsbeispiele</a>)<br>
	Falls keine Änderungen möglich sind, eine EMail an <a href="mailto: fhcomplete@'.DOMAIN.'">fhcomplete@'.DOMAIN.'</a> übermitteln</h2>';
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
				$output .= '<br>Semester '.$row->semester.' - '.$row->bezeichnung .'( ALVS: '.$row->alvs.' / LVS: '.$row->lvs.' )';
			}
		}
		else
			$output .= '<span class="ok">OK</span>';
	}

	// Pruefen ob unterschiedliche Wochenteiler vorhanden sind
	/*
	$output .= '<br><br><br><h2>'.++$nummerierung.'. Bei folgenden LVs ergeben sich unterschiedliche Werte für die Semesterwochen (Berechnung: LVS / SWS)<br>
	Die Semesterwochen sollten jedoch überall gleich sein. Bitte die entsprechenden Änderungen durchführen oder - falls keine Änderungen möglich sind - eine EMail an <a href="mailto: fhcomplete@'.DOMAIN.'">fhcomplete@'.DOMAIN.'</a> übermitteln.</h2>';
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
				$output .= '<br><u>'.number_format($row->wochenteiler,2).'</u>';

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
						$output .= "<br>&nbsp;&nbsp;&nbsp;Semester ".$row_lv->semester.' - '.$row_lv->bezeichnung;

						if($cnt>5)
						{
							$output .= "<br><b>&nbsp;&nbsp;&nbsp;... noch ".($db->db_num_rows($result_lv)-5).' weitere</b>';
							break;
						}
					}
				}
			}
		}
		else
			$output .= '<span class="ok">OK</span>';
	}
	*/

	// Pruefen ob LVPLS>ALVS
	$output .= '<br><br><br><h2>'.++$nummerierung.'. Bei folgenden LVs sind die LVPLS &gt; ALVS (Siehe <a href="https://wiki.fhcomplete.info/doku.php?id=addons:stgvt-sto-content-attr#berechnungsbeispiele" target="_blank">Berechnungsbeispiele</a>)<br>
	Dies ist nur selten der Fall bzw. korrekt. Bitte erforderlichenfalls die entsprechenden Änderungen vornehmen oder - falls keine Änderungen möglich sind - eine EMail an <a href="mailto: fhcomplete@'.DOMAIN.'">fhcomplete@'.DOMAIN.'</a> übermitteln</h2>';
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
				$output .= '<br>Semester '.$row->semester.' - '.$row->bezeichnung .'( ALVS: '.$row->alvs.' / LVPLS: '.$row->lvps.' )';
			}
		}
		else
			$output .= '<span class="ok">OK</span>';
	}


	// Lehrveranstaltungen bei denen Studienplan=True muss eine Englische Bezeichnung vorhanden sein
	$output .= '<br><br><br><h2>'.++$nummerierung.'. Bei folgenden Pflicht- und Wahl- LVs fehlt die englische Bezeichnung<br>
	Bitte ergänzen oder - falls keine Änderungen möglich sind - eine EMail an <a href="mailto: fhcomplete@'.DOMAIN.'">fhcomplete@'.DOMAIN.'</a> übermitteln.</h2>';
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester
			FROM
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND	tbl_studienplan_lehrveranstaltung.export=true
				AND (tbl_lehrveranstaltung.bezeichnung_english is null OR trim(both ' ' FROM tbl_lehrveranstaltung.bezeichnung_english)='')
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				$output .= '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			$output .= '<span class="ok">OK</span>';
	}

	// Lehrveranstaltungen bei denen Studienplan=True muss eine Englische Bezeichnung vorhanden sein
	$output .= '<br><br><br><h2>'.++$nummerierung.'. Bei folgenden LVs ist die Lehrform nicht angegeben<br>
	Bitte ergänzen oder - falls keine Änderungen möglich sind - eine EMail an <a href="mailto: fhcomplete@'.DOMAIN.'">fhcomplete@'.DOMAIN.'</a> übermitteln.</h2>';
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
				$output .= '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			$output .= '<span class="ok">OK</span>';
	}

	// Bei folgenden PflichtLVs passt die Attributskodierung nicht
	$output .= '<br><br><br><h2>'.++$nummerierung.'. Bei folgenden Pflicht-LVs sind die Attribute "StudPlan","Pflicht","Gen" usw nicht korrekt kodiert.<br>';
	$output .= 'Bitte die Änderungen gemäß dem <a href="https://wiki.fhcomplete.info/doku.php?id=addons:stgvt-sto_content" target="_blank">vorgesehenen Schema</a> vornehmen</h2>';
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
				AND	tbl_studienplan_lehrveranstaltung.export=true
				AND (genehmigung=false OR benotung=false OR zeugnis=false OR lehrauftrag=false OR export=false OR lehre=false OR lvinfo=false)
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				$output .= '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			$output .= '<span class="ok">OK</span>';
	}

	// Bei folgenden WahlLVs passt die Attributskodierung nicht
	$output .= '<br><br><br><h2>'.++$nummerierung.'. Bei folgenden Wahl-LVs sind die Attribute "StudPlan","Pflicht","Gen" usw nicht korrekt kodiert.<br>';
	$output .= 'Bitte die Änderungen gemäß dem <a href="https://wiki.fhcomplete.info/doku.php?id=addons:stgvt-sto_content" target="_blank">vorgesehenen Schema</a> vornehmen</h2>';
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
				AND tbl_studienplan_lehrveranstaltung.export=true
				AND (lehre=false OR lvinfo=false OR benotung=false OR zeugnis=false OR lehrauftrag=false)
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				$output .= '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			$output .= '<span class="ok">OK</span>';
	}

	// Bei folgenden Sonstigen LVs passt die Attributskodierung nicht
	$output .= '<br><br><br><h2>'.++$nummerierung.'. Bei folgenden Sonstigen LVs sind die Attribute "StudPlan","Pflicht","Gen" usw nicht korrekt kodiert.<br>';
	$output .= 'Bitte die Änderungen gemäß dem <a href="https://wiki.fhcomplete.info/doku.php?id=addons:stgvt-sto_content" target="_blank">vorgesehenen Schema</a> vornehmen</h2>';
	$qry = "SELECT
				tbl_lehrveranstaltung.bezeichnung, tbl_studienplan_lehrveranstaltung.semester
			FROM
				lehre.tbl_studienplan
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				tbl_studienplan.studienplan_id=".$db->db_add_param($studienplan->studienplan_id)."
				AND tbl_lehrveranstaltung.lehrtyp_kurzbz='lv'
				/*AND tbl_studienplan_lehrveranstaltung.pflicht=false*/
				AND tbl_studienplan_lehrveranstaltung.export=false
				AND (genehmigung=true OR pflicht=true OR zeugnis=true)
			ORDER BY tbl_studienplan_lehrveranstaltung.semester";

	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			while($row = $db->db_fetch_object($result))
			{
				$fehler++;
				$output .= '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			$output .= '<span class="ok">OK</span>';
	}

	// ZUSATZPRUEFUNG: LAS > ALVS
	$output .= '<br><br><br><h2>'.++$nummerierung.'. Bei folgenden LVs sind LAS &gt; ALVS. (Siehe <a href="https://wiki.fhcomplete.info/doku.php?id=addons:stgvt-sto-content-attr#berechnungsbeispiele" target="_blank">Berechnungsbeispiele</a>)<br>
	Dies ist nur selten der Fall bzw. korrekt.(z.B. wenn mehrere Lehrpersonen gleichzeitig Lehrstunden abhalten).<br>
	Bitte ggf. die entsprechenden Änderungen vornehmen oder - falls keine Änderungen möglich sind - eine EMail an <a href="mailto: fhcomplete@'.DOMAIN.'">fhcomplete@'.DOMAIN.'</a> übermitteln.</h2>';
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
				$output .= '<br>Semester '.$row->semester.' - '.$row->bezeichnung;
			}
		}
		else
			$output .= '<span class="ok">OK</span>';
	}

    //Prüft ob die Semester übereinstimmen
    $output .= '<br><br><br><h2>' . ++$nummerierung . '. Folgende LVs sind dem falschen Semester zugeordnet.</h2>';
    $qry = "SELECT
                lehre.tbl_lehrveranstaltung.bezeichnung as Bezeichnung,
                lehre.tbl_lehrveranstaltung.semester as LV_Semester,
                lehre.tbl_studienplan_lehrveranstaltung.semester as STPLV_Semester
            FROM
                lehre.tbl_studienplan
            JOIN lehre.tbl_studienplan_lehrveranstaltung using (studienplan_id)
            JOIN lehre.tbl_lehrveranstaltung using (lehrveranstaltung_id)
            WHERE
                tbl_studienplan.studienplan_id = " . $db->db_add_param($studienplan->studienplan_id) . "
            AND
                tbl_lehrveranstaltung.semester != tbl_studienplan_lehrveranstaltung.semester";

    if ($result = $db->db_query($qry))
    {
        if ($db->db_num_rows($result) > 0)
        {
            while ($row = $db->db_fetch_object($result))
            {
                $fehler++;
                $output .= '<br> LV Semester ' . $row->lv_semester . ' | Studienplan Semester ' . $row->stplv_semester . ' | ' . $row->bezeichnung . '';
            }
        }
        else
        {
            $output .= '<span class="ok">OK</span>';
        }
    }
}
$output .= '<br><br><br><br>';


echo '<doctype html>
<html>
<head>
		<meta charset="utf-8">
		<link rel="stylesheet" href="../../../skin/fhcomplete.css" type="text/css">
		<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
<style>
	h2
	{
		margin-bottom: 0;
	}
</style>
</head>
<body>
<h1>Plausibilitätsprüfung von Studienplänen</h1>
	<form method="GET" action="checkstudienplan.php">';

$qry = "SELECT distinct tbl_studienplan.studienplan_id, tbl_studienplan.bezeichnung
		FROM
			lehre.tbl_studienplan
			JOIN lehre.tbl_studienordnung USING(studienordnung_id)
		WHERE
			tbl_studienordnung.status_kurzbz NOT IN('approved','expired','notApproved')";

if(isset($stg_arr))
{
	$qry.=" AND studiengang_kz in(".$db->db_implode4SQL($stg_arr).")";
}
$qry.="	ORDER BY tbl_studienplan.bezeichnung";

echo '<select name="studienplan_id">';
echo '<option value="">-- Bitte Studienplan auswählen --</option>';
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
if (isset($_GET['studienplan_id']) && $_GET['studienplan_id']!='')
{
	echo '<br>Prüfe Studienplan '.$studienplan->bezeichnung.'...<br>';
	if($fehler==0)
		echo '<br><span class="ok">Keine Fehler gefunden - Studienplan OK</span>';
	else
		echo '<br><span class="error">Es wurden '.$fehler.' Fehler gefunden</span>';
}
echo '<br><br>';
echo $output;
