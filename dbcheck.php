<?php

/* Copyright (C) 2013 FH Technikum-Wien
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
/**
 * FH-Complete Addon Template Datenbank Check
 *
 * Prueft und aktualisiert die Datenbank
 */
require_once('../../config/system.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');

// Datenbank Verbindung
$db = new basis_db();

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<title>Addon Datenbank Check</title>
</head>
<body>
<h1>Addon Datenbank Check</h1>';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if (!$rechte->isBerechtigt('basis/addon')) {
    exit('Sie haben keine Berechtigung für die Verwaltung von Addons');
}

echo '<h2>Aktualisierung der Datenbank</h2>';

// Code fuer die Datenbankanpassungen

/*
  if(!$result = @$db->db_query("SELECT 1 FROM addon.tbl_template_items"))
  {

  $qry = 'CREATE TABLE addon.tbl_template_items
  (
  template_items_kurzbz varchar(32),
  bezeichnung varchar(256)
  );';

  if(!$db->db_query($qry))
  echo '<strong>addon.tbl_template_items: '.$db->db_last_error().'</strong><br>';
  else
  echo ' addon.tbl_template_items: Tabelle addon.template_items hinzugefuegt!<br>';

  }
 */

//Tabelle addon.tbl_stgv_foerdervertrag
if (!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_foerdervertrag LIMIT 1;")) {
    $qry = "CREATE TABLE addon.tbl_stgv_foerdervertrag
			(
				foerdervertrag_id integer NOT NULL,
				studiengang_kz integer NOT NULL,
				foerdergeber varchar(256),
				foerdersatz numeric(8,2),
				foerdergruppe varchar(256),
				gueltigvon varchar(16),
				gueltigbis varchar(16),
				erlaeuterungen text,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

		CREATE SEQUENCE addon.tbl_stgv_foerdervertrag_foerdervertrag_id_seq
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE addon.tbl_stgv_foerdervertrag ADD CONSTRAINT pk_foerdervertrag PRIMARY KEY (foerdervertrag_id);
		ALTER TABLE addon.tbl_stgv_foerdervertrag ALTER COLUMN foerdervertrag_id SET DEFAULT nextval('addon.tbl_stgv_foerdervertrag_foerdervertrag_id_seq');

		ALTER TABLE addon.tbl_stgv_foerdervertrag ADD CONSTRAINT fk_foerdervertrag_studiengang FOREIGN KEY (studiengang_kz) REFERENCES public.tbl_studiengang (studiengang_kz) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE addon.tbl_stgv_foerdervertrag ADD CONSTRAINT fk_foerdervertrag_studiensemester_gueltigvon FOREIGN KEY (gueltigvon) REFERENCES public.tbl_studiensemester (studiensemester_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE addon.tbl_stgv_foerdervertrag ADD CONSTRAINT fk_foerdervertrag_studiensemester_gueltigbis FOREIGN KEY (gueltigbis) REFERENCES public.tbl_studiensemester (studiensemester_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON addon.tbl_stgv_foerdervertrag TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_stgv_foerdervertrag TO vilesci;
		GRANT SELECT, UPDATE ON addon.tbl_stgv_foerdervertrag_foerdervertrag_id_seq TO vilesci;
	";

    if (!$db->db_query($qry))
	echo '<strong>addon.tbl_stgv_foerdervertrag: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' addon.tbl_stgv_foerdervertrag: Tabelle hinzugefuegt<br>';
}

// Tabelle Studienplan_Semester
if (!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_studienplan_semester LIMIT 1;")) {
    $qry = "CREATE TABLE addon.tbl_stgv_studienplan_semester
			(
				studienplan_semester_id integer NOT NULL,
				studienplan_id integer NOT NULL,
				studiensemester_kurzbz varchar(16) NOT NULL,
				semester smallint NOT NULL
			);

		CREATE SEQUENCE addon.tbl_stgv_studienplan_semester_studienplan_semester_id
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE addon.tbl_stgv_studienplan_semester ADD CONSTRAINT pk_studienplan_semester PRIMARY KEY (studienplan_semester_id);
		ALTER TABLE addon.tbl_stgv_studienplan_semester ALTER COLUMN studienplan_semester_id SET DEFAULT nextval('addon.tbl_stgv_studienplan_semester_studienplan_semester_id');

		ALTER TABLE addon.tbl_stgv_studienplan_semester ADD CONSTRAINT fk_studienplan_semester_studienplan_id FOREIGN KEY (studienplan_id) REFERENCES lehre.tbl_studienplan (studienplan_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE addon.tbl_stgv_studienplan_semester ADD CONSTRAINT fk_studienplan_semester_studiensemester FOREIGN KEY (studiensemester_kurzbz) REFERENCES public.tbl_studiensemester (studiensemester_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON addon.tbl_stgv_studienplan_semester TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_stgv_studienplan_semester TO vilesci;
		GRANT SELECT, UPDATE ON addon.tbl_stgv_studienplan_semester_studienplan_semester_id TO vilesci;
	";

    if (!$db->db_query($qry))
	echo '<strong>addon.tbl_stgv_studienplan_semester: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' addon.tbl_stgv_studienplan_semester: Tabelle hinzugefuegt<br>';
}

//Tabelle addon.tbl_stgv_aenderungsvariante
if (!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_aenderungsvariante LIMIT 1;")) {
    $qry = "CREATE TABLE addon.tbl_stgv_aenderungsvariante
			(
				aenderungsvariante_kurzbz varchar(32) NOT NULL,
				bezeichnung varchar(256)
			);

		ALTER TABLE addon.tbl_stgv_aenderungsvariante ADD CONSTRAINT pk_aenderungsvariante PRIMARY KEY (aenderungsvariante_kurzbz);

		GRANT SELECT ON addon.tbl_stgv_aenderungsvariante TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_stgv_aenderungsvariante TO vilesci;
	";

    if (!$db->db_query($qry))
	echo '<strong>addon.tbl_stgv_aenderungsvariante: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' addon.tbl_stgv_aenderungsvariante: Tabelle hinzugefuegt<br>';
}

//Spalte Aenderungsvariante in lehre.tbl_studienordnung
if (!$result = @$db->db_query("SELECT aenderungsvariante_kurzbz FROM lehre.tbl_studienordnung LIMIT 1;"))
{
    $qry = "ALTER TABLE lehre.tbl_studienordnung ADD COLUMN aenderungsvariante_kurzbz varchar(32); 
	   
	    ALTER TABLE lehre.tbl_studienordnung ADD CONSTRAINT fk_studienordnung_aenderungsvariante_kurzbz FOREIGN KEY (aenderungsvariante_kurzbz) REFERENCES addon.tbl_stgv_aenderungsvariante (aenderungsvariante_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	   ";
    
    if (!$db->db_query($qry))
	echo '<strong>lehre.tbl_studienordnung: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' lehre.tbl_studienordnung: Spalte aenderungsvariante_kurzbz hinzugefügt.<br>';
    
}

//Tabelle addon.tbl_stgv_studienordnungstatus
if (!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_studienordnungstatus LIMIT 1;")) {
    $qry = "CREATE TABLE addon.tbl_stgv_studienordnungstatus
			(
				status_kurzbz varchar(32) NOT NULL,
				bezeichnung varchar(256)
				reihenfolge integer
			);

		ALTER TABLE addon.tbl_stgv_studienordnungstatus ADD CONSTRAINT pk_studienordnungstatus PRIMARY KEY (status_kurzbz);

		GRANT SELECT ON addon.tbl_stgv_studienordnungstatus TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_stgv_studienordnungstatus TO vilesci;
	";

    if (!$db->db_query($qry))
	echo '<strong>addon.tbl_stgv_studienordnungstatus: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' addon.tbl_stgv_studienordnungstatus: Tabelle hinzugefuegt<br>';
}

//Spalte status_kurzbz in lehre.tbl_studienordnung
if (!$result = @$db->db_query("SELECT status_kurzbz FROM lehre.tbl_studienordnung LIMIT 1;"))
{
    $qry = "ALTER TABLE lehre.tbl_studienordnung ADD COLUMN status_kurzbz varchar(32); 
	   
	    ALTER TABLE lehre.tbl_studienordnung ADD CONSTRAINT status_kurzbz FOREIGN KEY (status_kurzbz) REFERENCES addon.tbl_stgv_studienordnungstatus (status_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	   ";
    
    if (!$db->db_query($qry))
	echo '<strong>lehre.tbl_studienordnung: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' lehre.tbl_studienordnung: Spalte status_kurzbz hinzugefügt.<br>';
    
}

//Spalte Begruendung in lehre.tbl_studienordnung
if (!$result = @$db->db_query("SELECT begruendung FROM lehre.tbl_studienordnung LIMIT 1;"))
{
    $qry = "ALTER TABLE lehre.tbl_studienordnung ADD COLUMN begruendung text;";
    
    if (!$db->db_query($qry))
	echo '<strong>lehre.tbl_studienordnung: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' lehre.tbl_studienordnung: Spalte begruendung hinzugefügt.<br>';
    
}

//Spalte Studiengangsart in lehre.tbl_studienordnung
if (!$result = @$db->db_query("SELECT studiengangsart FROM lehre.tbl_studienordnung LIMIT 1;"))
{
    $qry = "ALTER TABLE lehre.tbl_studienordnung ADD COLUMN studiengangsart varchar(64);";
    
    if (!$db->db_query($qry))
	echo '<strong>lehre.tbl_studienordnung: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' lehre.tbl_studienordnung: Spalte studiengangsart hinzugefügt.<br>';
    
}

//Spalte orgform_kurzbz in lehre.tbl_studienordnung
if (!$result = @$db->db_query("SELECT orgform_kurzbz FROM lehre.tbl_studienordnung LIMIT 1;"))
{
    $qry = "ALTER TABLE lehre.tbl_studienordnung ADD COLUMN orgform_kurzbz varchar(3);
	    
	    ALTER TABLE lehre.tbl_studienordnung ADD CONSTRAINT studienordnung_orgform_kurzbz FOREIGN KEY (orgform_kurzbz) REFERENCES bis.tbl_orgform (orgform_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
	   ";
    
    if (!$db->db_query($qry))
	echo '<strong>lehre.tbl_studienordnung: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' lehre.tbl_studienordnung: Spalte orgform_kurzbz hinzugefügt.<br>';
    
}

//Spalte standort_id in lehre.tbl_studienordnung
if (!$result = @$db->db_query("SELECT standort_id FROM lehre.tbl_studienordnung LIMIT 1;"))
{
    $qry = "ALTER TABLE lehre.tbl_studienordnung ADD COLUMN standort_id integer;
	    
	    ALTER TABLE lehre.tbl_studienordnung ADD CONSTRAINT studienordnung_standort_id FOREIGN KEY (standort_id) REFERENCES public.tbl_standort (standort_id) ON DELETE RESTRICT ON UPDATE CASCADE;
	   ";
    
    if (!$db->db_query($qry))
	echo '<strong>lehre.tbl_studienordnung: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' lehre.tbl_studienordnung: Spalte standort_id hinzugefügt.<br>';
    
}

//Spalte ects_stpl in lehre.tbl_studienplan
if (!$result = @$db->db_query("SELECT ects_stpl FROM lehre.tbl_studienplan LIMIT 1;"))
{
    $qry = "ALTER TABLE lehre.tbl_studienplan ADD COLUMN ects_stpl numeric(5,2);";
    
    if (!$db->db_query($qry))
	echo '<strong>lehre.tbl_studienplan: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' lehre.tbl_studienplan: Spalte ects_stpl hinzugefügt.<br>';
    
}

//Spalte pflicht_sws in lehre.tbl_studienplan
if (!$result = @$db->db_query("SELECT pflicht_sws FROM lehre.tbl_studienplan LIMIT 1;"))
{
    $qry = "ALTER TABLE lehre.tbl_studienplan ADD COLUMN pflicht_sws integer;";
    
    if (!$db->db_query($qry))
	echo '<strong>lehre.tbl_studienplan: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' lehre.tbl_studienplan: Spalte pflicht_sws hinzugefügt.<br>';
    
}

//Spalte pflicht_lvs in lehre.tbl_studienplan
if (!$result = @$db->db_query("SELECT pflicht_lvs FROM lehre.tbl_studienplan LIMIT 1;"))
{
    $qry = "ALTER TABLE lehre.tbl_studienplan ADD COLUMN pflicht_lvs integer;";
    
    if (!$db->db_query($qry))
	echo '<strong>lehre.tbl_studienplan: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' lehre.tbl_studienplan: Spalte pflicht_lvs hinzugefügt.<br>';
    
}

//Spalte pflicht_lvs in lehre.tbl_studienplan
if (!$result = @$db->db_query("SELECT erlaeuterungen FROM lehre.tbl_studienplan LIMIT 1;"))
{
    $qry = "ALTER TABLE lehre.tbl_studienplan ADD COLUMN erlaeuterungen text;";
    
    if (!$db->db_query($qry))
	echo '<strong>lehre.tbl_studienplan: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' lehre.tbl_studienplan: Spalte erlaeuterungen hinzugefügt.<br>';
    
}

//Berechtigung zum löschen von STO
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/deleteStudienordnung' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/deleteStudienordnung','Löschen einer Studienordnung im Addon Studiengangsverwaltung');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/deleteStudienordnung','admin','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Berechtigung zum löschen einer Studienordnung hinzugefügt.<br>';
    }
}

//Berechtigung zum löschen von STPL
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/deleteStudienplan' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/deleteStudienplan','Löschen eines Studienplans im Addon Studiengangsverwaltung');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/deleteStudienplan','admin','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Berechtigung zum löschen eines Studienplans hinzugefügt.<br>';
    }
}

//Tabelle addon.tbl_stgv_bewerbungstermine
if (!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_bewerbungstermine LIMIT 1;")) {
    $qry = "CREATE TABLE addon.tbl_stgv_bewerbungstermine
			(
				bewerbungstermin_id integer NOT NULL,
				studiengang_kz integer NOT NULL,
				studiensemester_kurzbz varchar(16) NOT NULL,
				beginn timestamp,
				ende timestamp,
				nachfrist boolean default false,
				nachfrist_ende timestamp,
				anmerkung text,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);
			
		    CREATE SEQUENCE addon.tbl_stgv_bewerbungstermine_bewerbungstermin_id_seq
			INCREMENT BY 1
			NO MAXVALUE
			NO MINVALUE
			CACHE 1;

		ALTER TABLE addon.tbl_stgv_bewerbungstermine ADD CONSTRAINT pk_bewerbungstermin_id PRIMARY KEY (bewerbungstermin_id);
		ALTER TABLE addon.tbl_stgv_bewerbungstermine ALTER COLUMN bewerbungstermin_id SET DEFAULT nextval('addon.tbl_stgv_bewerbungstermine_bewerbungstermin_id_seq');
		ALTER TABLE addon.tbl_stgv_bewerbungstermine ADD CONSTRAINT fk_bewerbungstermin_studiensemester FOREIGN KEY (studiensemester_kurzbz) REFERENCES public.tbl_studiensemester (studiensemester_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE addon.tbl_stgv_bewerbungstermine ADD CONSTRAINT fk_bewerbungstermin_studiengang FOREIGN KEY (studiengang_kz) REFERENCES public.tbl_studiengang (studiengang_kz) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON addon.tbl_stgv_bewerbungstermine TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_stgv_bewerbungstermine TO vilesci;
		GRANT SELECT, UPDATE ON addon.tbl_stgv_bewerbungstermine_bewerbungstermin_id_seq TO vilesci;
	";

    if (!$db->db_query($qry))
	echo '<strong>addon.tbl_stgv_studienordnungstatus: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' addon.tbl_stgv_studienordnungstatus: Tabelle hinzugefuegt<br>';
}


echo '<br>Aktualisierung abgeschlossen<br><br>';
echo '<h2>Gegenprüfung</h2>';


// Liste der verwendeten Tabellen / Spalten des Addons
//TODO check lehre.tbl_studienordnung
$tabellen = array(
    "addon.tbl_stgv_foerdervertrag" => array("foerdervertrag_id", "studiengang_kz", "foerdergeber", "foerdersatz", "foerdergruppe", "gueltigvon", "gueltigbis", "erlaeuterungen", "insertamum", "insertvon", "updateamum", "updatevon"),
    "addon.tbl_stgv_studienplan_semester" => array("studienplan_semester_id", "studienplan_id", "studiensemester_kurzbz", "semester"),
    "addon.tbl_stgv_aenderungsvariante" => array("aenderungsvariante_kurzbz","bezeichnung"),
    "addon.tbl_stgv_studienordnungstatus" => array("status_kurzbz","bezeichnung","reihenfolge"),
    "addon.tbl_stgv_bewerbungstermine" => array("bewerbungstermin_id","studiengang_kz","studiensemester_kurzbz","beginn","ende","nachfrist","nachfrist_ende","anmerkung", "insertamum", "insertvon", "updateamum", "updatevon")
);


$tabs = array_keys($tabellen);
$i = 0;
foreach ($tabellen AS $attribute) {
    $sql_attr = '';
    foreach ($attribute AS $attr)
	$sql_attr.=$attr . ',';
    $sql_attr = substr($sql_attr, 0, -1);

    if (!@$db->db_query('SELECT ' . $sql_attr . ' FROM ' . $tabs[$i] . ' LIMIT 1;'))
	echo '<BR><strong>' . $tabs[$i] . ': ' . $db->db_last_error() . ' </strong><BR>';
    else
	echo $tabs[$i] . ': OK - <BR>';
    flush();
    $i++;
}
?>
