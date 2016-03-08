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
		
		INSERT INTO addon.tbl_stgv_aenderungsvariante (aenderungsvariante_kurzbz, bezeichnung) VALUES ('nichtGering','nicht geringfügig');
		INSERT INTO addon.tbl_stgv_aenderungsvariante (aenderungsvariante_kurzbz, bezeichnung) VALUES ('akkreditierungspflichtig','akkreditierungspflichtig');
		INSERT INTO addon.tbl_stgv_aenderungsvariante (aenderungsvariante_kurzbz, bezeichnung) VALUES ('gering','geringfügig');
	";

    if (!$db->db_query($qry))
	echo '<strong>addon.tbl_stgv_aenderungsvariante: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' addon.tbl_stgv_aenderungsvariante: Tabelle hinzugefuegt<br>';
}

//Tabelle addon.tbl_stgv_studienordnung
if (!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_studienordnung LIMIT 1;")) {
    $qry = "CREATE TABLE addon.tbl_stgv_studienordnung
			(
				studienordnung_id integer NOT NULL,
				aenderungsvariante_kurzbz varchar(32),
				begruendung jsonb
			);
			
		ALTER TABLE addon.tbl_stgv_studienordnung ADD CONSTRAINT studienordnung_id FOREIGN KEY (studienordnung_id) REFERENCES lehre.tbl_studienordnung (studienordnung_id) ON DELETE RESTRICT ON UPDATE CASCADE;

		INSERT INTO addon.tbl_stgv_studienordnung (studienordnung_id) SELECT studienordnung_id FROM lehre.tbl_studienordnung;

		GRANT SELECT ON addon.tbl_stgv_studienordnung TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_stgv_studienordnung TO vilesci;
	";

    if (!$db->db_query($qry))
	echo '<strong>addon.tbl_stgv_studienordnung: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' addon.tbl_stgv_studienordnung: Tabelle hinzugefuegt<br>';
}

//Tabelle addon.tbl_stgv_studienplan
if (!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_studienplan LIMIT 1;")) {
    $qry = "CREATE TABLE addon.tbl_stgv_studienplan
			(
				studienplan_id integer NOT NULL,
				erlaeuterungen text,
				sprache_kommentar text
			);
			
		ALTER TABLE addon.tbl_stgv_studienplan ADD CONSTRAINT studienplan_id FOREIGN KEY (studienplan_id) REFERENCES lehre.tbl_studienplan (studienplan_id) ON DELETE RESTRICT ON UPDATE CASCADE;

		INSERT INTO addon.tbl_stgv_studienplan (studienplan_id) SELECT studienplan_id FROM lehre.tbl_studienplan;

		GRANT SELECT ON addon.tbl_stgv_studienplan TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_stgv_studienplan TO vilesci;
	";

    if (!$db->db_query($qry))
	echo '<strong>addon.tbl_stgv_studienplan: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' addon.tbl_stgv_studienplan: Tabelle hinzugefuegt<br>';
}

//Rolle für Addon
if($result = @$db->db_query("SELECT 1 FROM system.tbl_rolle WHERE rolle_kurzbz='addonStgv' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_rolle(rolle_kurzbz, beschreibung) VALUES ('addonStgv','Rolle für Addon Studiengangsverwaltung');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_rolle: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_rolle: Rolle für Addon Studiengangsverwaltung hinzugefügt.<br>';
    }
}

//Berechtigung für Addon
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='addon/studiengangsverwaltung' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('addon/studiengangsverwaltung','Basisrecht für Addon Studiengangsverwaltung');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('addon/studiengangsverwaltung','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Basisrecht für Addon Studiengangsverwaltung hinzugefügt.<br>';
    }
}

//Berechtigung zum Erstellen von Studienordnungen
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/createStudienordnung' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/createStudienordnung','Recht zum Erstellen von Studienordnungen.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/createStudienordnung','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Erstellen von Studienordnungen.<br>';
    }
}

//Berechtigung zum Erstellen von Studienplänen
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/createStudienplan' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/createStudienplan','Recht zum Erstellen von Studienplänen.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/createStudienplan','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Erstellen von Studienplänen.<br>';
    }
}

//Berechtigung zum Ändern von Studienplänen
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/changeStudienplan' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/changeStudienplan','Recht zum Ändern von Studienplänen.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/changeStudienplan','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Ändern von Studienplänen.<br>';
    }
}

//Berechtigung zum löschen von STO
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/deleteStudienordnung' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/deleteStudienordnung','Löschen einer Studienordnung im Addon Studiengangsverwaltung');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/deleteStudienordnung','addonStgv','suid');";

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
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/deleteStudienplan','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Berechtigung zum löschen eines Studienplans hinzugefügt.<br>';
    }
}

//Berechtigung zum Erstellen/editieren von Bewerbungsfristen
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/editBewerbungsfrist' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/editBewerbungsfrist','Recht zum Erstellen/Editieren von Bewerbungsfristen.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/editBewerbungsfrist','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Erstellen/Editieren von Bewerbungsfristen.<br>';
    }
}

//Berechtigung zum Erstellen/editieren von Doktoratsstudienverordnungen
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/editDoktorat' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/editDoktorat','Recht zum Erstellen/Editieren von Doktoratsstudienverordnungen.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/editDoktorat','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Erstellen/Editieren von Doktoratsstudienverordnungen.<br>';
    }
}

//Berechtigung zum Erstellen/editieren von Förderverträgen
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/editFoerdervertrag' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/editFoerdervertrag','Recht zum Erstellen/Editieren von Förderverträgen.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/editFoerdervertrag','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Erstellen/Editieren von Förderverträgen.<br>';
    }
}

//Berechtigung zum Erstellen/editieren von Reihungstests
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/editReihungstest' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/editReihungstest','Recht zum Erstellen/Editieren von Reihungstests.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/editReihungstest','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Erstellen/Editieren von Reihungstests.<br>';
    }
}

//Berechtigung zum Erstellen/editieren von Studiengangsgruppen
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/editStudiengangsgruppen' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/editStudiengangsgruppen','Recht zum Erstellen/Editieren von Studiengangsgruppen.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/editStudiengangsgruppen','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Erstellen/Editieren von Studiengangsgruppen.<br>';
    }
}

//Berechtigung zum Löschen von Bewerbungsfristen
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/deleteBewerbungsfrist' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/deleteBewerbungsfrist','Recht zum Löschen von Bewerbungsfristen.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/deleteBewerbungsfrist','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Löschen von Bewerbungsfristen.<br>';
    }
}

//Berechtigung zum Löschen von Doktoratsstudienverordnungen
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/deleteDoktorat' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/deleteDoktorat','Recht zum Löschen von Doktoratsstudienverordnungen.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/deleteDoktorat','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Löschen von Doktoratsstudienverordnungen.<br>';
    }
}

//Berechtigung zum Löschen von Förderverträgen
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/deleteFoerdervertrag' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/deleteFoerdervertrag','Recht zum Löschen von Förderverträgen.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/deleteFoerdervertrag','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Löschen von Förderverträgen.<br>';
    }
}

//Berechtigung zum Löschen von Reihungstests
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/deleteReihungstest' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/deleteReihungstest','Recht zum Löschen von Reihungstests.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/deleteReihungstest','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Löschen von Reihungstests.<br>';
    }
}

//Berechtigung zum Löschen von Studiengangsgruppen
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/deleteStudiengangsgruppen' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/deleteStudiengangsgruppen','Recht zum Löschen von Studiengangsgruppen.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/deleteStudiengangsgruppen','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Löschen von Studiengangsgruppen.<br>';
    }
}

//Berechtigung zum Erstellen/editieren von Aufnahmeverfahren
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/editAufnahmeverfahren' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/editAufnahmeverfahren','Recht zum Erstellen/Editieren von Aufnahmeverfahren.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/editAufnahmeverfahren','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Erstellen/Editieren von Aufnahmeverfahren.<br>';
    }
}

//Berechtigung zum Erstellen/editieren von Zugangsvoraussetzung
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/editZugangsvoraussetzung' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/editZugangsvoraussetzung','Recht zum Erstellen/Editieren von Zugangsvoraussetzung.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/editZugangsvoraussetzung','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Erstellen/Editieren von Zugangsvoraussetzung.<br>';
    }
}

//Berechtigung zum Erstellen/editieren von Taetigkeitsfelder
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/editTaetigkeitsfelder' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/editTaetigkeitsfelder','Recht zum Erstellen/Editieren von Taetigkeitsfelder.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/editTaetigkeitsfelder','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Erstellen/Editieren von Taetigkeitsfelder.<br>';
    }
}

//Berechtigung zum Erstellen/editieren von Qualifikationsziel
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/editQualifikationsziel' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/editQualifikationsziel','Recht zum Erstellen/Editieren von Qualifikationsziel.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/editQualifikationsziel','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Erstellen/Editieren von Qualifikationsziel.<br>';
    }
}

//Berechtigung zum Editieren einer Studienordnung
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/editStudienordnung' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/editStudienordnung','Recht zum Erstellen/Editieren von Studienordnung.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/editStudienordnung','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Editieren einer Studienordnung.<br>';
    }
}

//Berechtigung zum Upload von Dokumenten
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/uploadDokumente' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/uploadDokumente','Recht zum Upload von Dokumenten.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/uploadDokumente','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Upload von Dokumenten.<br>';
    }
}

//Berechtigung zum Download von Dokumenten
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/downloadDokumente' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/downloadDokumente','Recht zum Download von Dokumenten.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/downloadDokumente','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Download von Dokumenten.<br>';
    }
}

//Berechtigung zum Erstellen von Lehrveranstaltungen
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/createLehrveranstaltung' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/createLehrveranstaltung','Recht zum Erstellen von Lehrveranstaltungen.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/createLehrveranstaltung','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Erstellen von Lehrveranstaltungen.<br>';
    }
}

//Berechtigung zum Ändern von Lehrveranstaltungen
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/editLehrveranstaltung' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/editLehrveranstaltung','Recht zum Ändern von Lehrveranstaltungen.');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/editLehrveranstaltung','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Recht zum Ändern von Lehrveranstaltungen..<br>';
    }
}

//Spalte benotung in lehre.tbl_lehrveranstaltung
if (!$result = @$db->db_query("SELECT benotung FROM lehre.tbl_lehrveranstaltung LIMIT 1;"))
{
    $qry = "ALTER TABLE lehre.tbl_lehrveranstaltung ADD COLUMN benotung boolean NOT NULL DEFAULT FALSE;";
    
    if (!$db->db_query($qry))
	echo '<strong>lehre.tbl_lehrveranstaltung: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' lehre.tbl_lehrveranstaltung: Spalte benotung hinzugefügt.<br>'; 
}

//Spalte lvinfo in lehre.tbl_lehrveranstaltung
if (!$result = @$db->db_query("SELECT lvinfo FROM lehre.tbl_lehrveranstaltung LIMIT 1;"))
{
    $qry = "ALTER TABLE lehre.tbl_lehrveranstaltung ADD COLUMN lvinfo boolean NOT NULL DEFAULT FALSE;";
    
    if (!$db->db_query($qry))
	echo '<strong>lehre.tbl_lehrveranstaltung: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' lehre.tbl_lehrveranstaltung: Spalte lvinfo hinzugefügt.<br>'; 
}

//zusätzliche Lehrform integratives Modul
if($result = @$db->db_query("SELECT 1 FROM lehre.tbl_lehrform WHERE lehrform_kurzbz='iMod' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO lehre.tbl_lehrform(lehrform_kurzbz, bezeichnung, verplanen, bezeichnung_kurz, bezeichnung_lang) VALUES ('iMod','integratives Modul',true,'{IMOD,IMOD}','{integratives Modul,integratives Modul}');";

	if (!$db->db_query($qry))
	    echo '<strong>lehre.tbl_lehrform: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' lehre.tbl_lehrform: Neue Lehrform integratives Modul hinzugefügt.<br>';
    }
}

//zusätzliche Lehrform kumulatives Modul
if($result = @$db->db_query("SELECT 1 FROM lehre.tbl_lehrform WHERE lehrform_kurzbz='kMod' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO lehre.tbl_lehrform(lehrform_kurzbz, bezeichnung, verplanen, bezeichnung_kurz, bezeichnung_lang) VALUES ('kMod','kumulatives Modul',true,'{KMOD,KMOD}','{kumulatives Modul,kumulatives Modul}');";

	if (!$db->db_query($qry))
	    echo '<strong>lehre.tbl_lehrform: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' lehre.tbl_lehrform: Neue Lehrform kumulatives Modul hinzugefügt.<br>';
    }
}

//Tabelle addon.tbl_stgv_lehrtyp_lehrform
if (!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_lehrtyp_lehrform LIMIT 1;")) {
    $qry = "CREATE TABLE addon.tbl_stgv_lehrtyp_lehrform
			(
				lehrtyp_lehrform_id integer NOT NULL,
				lehrtyp_kurzbz varchar(32) NOT NULL,
				lehrform_kurzbz varchar(8) NOT NULL
			);
			
		CREATE SEQUENCE addon.tbl_stgv_lehrtyp_lehrform_id_seq
			INCREMENT BY 1
			NO MAXVALUE
			NO MINVALUE
			CACHE 1;

		ALTER TABLE addon.tbl_stgv_lehrtyp_lehrform ADD CONSTRAINT pk_lehrtyp_lehrform PRIMARY KEY (lehrtyp_lehrform_id);
		ALTER TABLE addon.tbl_stgv_lehrtyp_lehrform ALTER COLUMN lehrtyp_lehrform_id SET DEFAULT nextval('addon.tbl_stgv_lehrtyp_lehrform_id_seq');
		
		GRANT SELECT ON addon.tbl_stgv_lehrtyp_lehrform TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_stgv_lehrtyp_lehrform TO vilesci;
		GRANT SELECT, UPDATE ON addon.tbl_stgv_lehrtyp_lehrform_id_seq TO vilesci;
		
		INSERT INTO addon.tbl_stgv_lehrtyp_lehrform(lehrtyp_kurzbz, lehrform_kurzbz)
		SELECT 'lv', lehrform_kurzbz FROM lehre.tbl_lehrform; 
	";

    if (!$db->db_query($qry))
	echo '<strong>addon.tbl_stgv_lehrtyp_lehrform: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' addon.tbl_stgv_lehrtyp_lehrform: Tabelle hinzugefuegt<br>';
}

//Tabelle addon.tbl_stgv_doktorat
if (!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_doktorat LIMIT 1;")) {
    $qry = "CREATE TABLE addon.tbl_stgv_doktorat
			(
				doktorat_id integer NOT NULL,
				studiengang_kz integer NOT NULL,
				bezeichnung varchar(256),
				datum_erlass timestamp,
				gueltigvon varchar(16),
				gueltigbis varchar(16),
				erlaeuterungen text,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

		CREATE SEQUENCE addon.tbl_stgv_doktorat_doktorat_id_seq
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE addon.tbl_stgv_doktorat ADD CONSTRAINT pk_doktorat PRIMARY KEY (doktorat_id);
		ALTER TABLE addon.tbl_stgv_doktorat ALTER COLUMN doktorat_id SET DEFAULT nextval('addon.tbl_stgv_doktorat_doktorat_id_seq');

		ALTER TABLE addon.tbl_stgv_doktorat ADD CONSTRAINT fk_doktorat_studiengang FOREIGN KEY (studiengang_kz) REFERENCES public.tbl_studiengang (studiengang_kz) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE addon.tbl_stgv_doktorat ADD CONSTRAINT fk_doktorat_studiensemester_gueltigvon FOREIGN KEY (gueltigvon) REFERENCES public.tbl_studiensemester (studiensemester_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE addon.tbl_stgv_doktorat ADD CONSTRAINT fk_doktorat_studiensemester_gueltigbis FOREIGN KEY (gueltigbis) REFERENCES public.tbl_studiensemester (studiensemester_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON addon.tbl_stgv_doktorat TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_stgv_doktorat TO vilesci;
		GRANT SELECT, UPDATE ON addon.tbl_stgv_doktorat_doktorat_id_seq TO vilesci;
	";

    if (!$db->db_query($qry))
	echo '<strong>addon.tbl_stgv_doktorat: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' addon.tbl_stgv_doktorat: Tabelle hinzugefuegt<br>';
}

//Berechtigung zum Ändern des Sto Status
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/changeStoStateSTG' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/changeStoStateSTG','Ändern des Sto Status im Addon Studiengangsverwaltung von Development zu Review');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/changeStoStateSTG','addonStgv','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Berechtigung zum Ändern des Sto Status hinzugefügt.<br>';
    }
}

//Berechtigung zum Ändern des Sto Status
if($result = @$db->db_query("SELECT 1 FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='stgv/changeStoStateAdmin' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) VALUES ('stgv/changeStoStateAdmin','Ändern des Sto Status im Addon Studiengangsverwaltung in alle Stati');
		INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) VALUES('stgv/changeStoStateAdmin','admin','suid');";

	if (!$db->db_query($qry))
	    echo '<strong>system.tbl_berechtigung: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' system.tbl_berechtigung: Berechtigung zum Ändern des Sto Status hinzugefügt.<br>';
    }
}

//Tabelle addon.tbl_stgv_taetigkeitsfelder
if (!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_taetigkeitsfelder LIMIT 1;")) {
    $qry = "CREATE TABLE addon.tbl_stgv_taetigkeitsfelder
			(
				taetigkeitsfeld_id integer NOT NULL,
				studienordnung_id integer NOT NULL,
				ueberblick text,
				data jsonb,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

		CREATE SEQUENCE addon.tbl_stgv_taetigkeitsfelder_taetigkeitsfeld_id_seq
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE addon.tbl_stgv_taetigkeitsfelder ADD CONSTRAINT pk_taetigkeitsfelder PRIMARY KEY (taetigkeitsfeld_id);
		ALTER TABLE addon.tbl_stgv_taetigkeitsfelder ALTER COLUMN taetigkeitsfeld_id SET DEFAULT nextval('addon.tbl_stgv_taetigkeitsfelder_taetigkeitsfeld_id_seq');

		ALTER TABLE addon.tbl_stgv_taetigkeitsfelder ADD CONSTRAINT fk_taetigkeitsfelder_studienordnung FOREIGN KEY (studienordnung_id) REFERENCES lehre.tbl_studienordnung (studienordnung_id) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON addon.tbl_stgv_taetigkeitsfelder TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_stgv_taetigkeitsfelder TO vilesci;
		GRANT SELECT, UPDATE ON addon.tbl_stgv_taetigkeitsfelder_taetigkeitsfeld_id_seq TO vilesci;
	";

    if (!$db->db_query($qry))
	echo '<strong>addon.tbl_stgv_taetigkeitsfelder: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' addon.tbl_stgv_taetigkeitsfelder: Tabelle hinzugefuegt<br>';
}

//Tabelle addon.tbl_stgv_studiengangsgruppen
if (!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_studiengangsgruppen LIMIT 1;")) {
    $qry = "CREATE TABLE addon.tbl_stgv_studiengangsgruppen
			(
				studiengangsgruppe_id integer NOT NULL,
				data jsonb,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

		CREATE SEQUENCE addon.tbl_stgv_studiengangsgruppen_studiengangsgruppe_id_seq
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE addon.tbl_stgv_studiengangsgruppen ADD CONSTRAINT pk_studiengangsgruppen PRIMARY KEY (studiengangsgruppe_id);
		ALTER TABLE addon.tbl_stgv_studiengangsgruppen ALTER COLUMN studiengangsgruppe_id SET DEFAULT nextval('addon.tbl_stgv_studiengangsgruppen_studiengangsgruppe_id_seq');

		GRANT SELECT ON addon.tbl_stgv_studiengangsgruppen TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_stgv_studiengangsgruppen TO vilesci;
		GRANT SELECT, UPDATE ON addon.tbl_stgv_studiengangsgruppen_studiengangsgruppe_id_seq TO vilesci;
	";

    if (!$db->db_query($qry))
	echo '<strong>addon.tbl_stgv_studiengangsgruppen: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' addon.tbl_stgv_studiengangsgruppen: Tabelle hinzugefuegt<br>';
}

//Tabelle addon.tbl_stgv_studiengangsgruppen
if (!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_studiengangsgruppe_studiengang LIMIT 1;")) {
    $qry = "CREATE TABLE addon.tbl_stgv_studiengangsgruppe_studiengang
			(
				studiengangsgruppe_studiengang_id integer NOT NULL,
				studiengang_kz integer NOT NULL,
				data jsonb,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

		CREATE SEQUENCE addon.tbl_stgv_studiengangsgruppe_studiengang_studiengangsgruppe_studiengang_id_seq
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE addon.tbl_stgv_studiengangsgruppe_studiengang ADD CONSTRAINT pk_studiengangsgruppe_studiengang PRIMARY KEY (studiengangsgruppe_studiengang_id);
		ALTER TABLE addon.tbl_stgv_studiengangsgruppe_studiengang ALTER COLUMN studiengangsgruppe_studiengang_id SET DEFAULT nextval('addon.tbl_stgv_studiengangsgruppe_studiengang_studiengangsgruppe_studiengang_id_seq');
		
		ALTER TABLE addon.tbl_stgv_studiengangsgruppe_studiengang ADD CONSTRAINT fk_studiengangsgruppe_studiengang FOREIGN KEY (studiengang_kz) REFERENCES public.tbl_studiengang (studiengang_kz) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON addon.tbl_stgv_studiengangsgruppe_studiengang TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_stgv_studiengangsgruppe_studiengang TO vilesci;
		GRANT SELECT, UPDATE ON addon.tbl_stgv_studiengangsgruppe_studiengang_studiengangsgruppe_studiengang_id_seq TO vilesci;
	";

    if (!$db->db_query($qry))
	echo '<strong>addon.tbl_stgv_studiengangsgruppe_studiengang: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' addon.tbl_stgv_studiengangsgruppe_studiengang: Tabelle hinzugefuegt<br>';
}

//DMS Kategorie studiengangsverwaltung hinzufügen
if($result = @$db->db_query("SELECT 1 FROM campus.tbl_dms_kategorie WHERE kategorie_kurzbz='studiengangsverwaltung' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO campus.tbl_dms_kategorie(kategorie_kurzbz, bezeichnung, beschreibung, parent_kategorie_kurzbz) VALUES ('studiengangsverwaltung','Studiengangsverwaltung', 'Dokumente aus Addon Studiengangsverwaltung 2','dokumente');";

	if (!$db->db_query($qry))
	    echo '<strong>campus.tbl_dms_kategorie: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' campus.tbl_dms_kategorie: DMS Dokumentkategorie "studiengangsverwaltung" hinzugefügt.<br>';
    }
}

//DMS Kategorie studiengangsverwaltung mit Gruppe CMS_LOCK verknüpfen
if($result = @$db->db_query("SELECT 1 FROM campus.tbl_dms_kategorie_gruppe WHERE kategorie_kurzbz='studiengangsverwaltung' AND gruppe_kurzbz='CMS_LOCK' LIMIT 1"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO campus.tbl_dms_kategorie_gruppe(kategorie_kurzbz, gruppe_kurzbz, insertamum, insertvon) VALUES ('studiengangsverwaltung','CMS_LOCK', now(), 'dbcheck');";

	if (!$db->db_query($qry))
	    echo '<strong>campus.tbl_dms_kategorie_gruppe: ' . $db->db_last_error() . '</strong><br>';
	else
	    echo ' campus.tbl_dms_kategorie_gruppe: DMS Dokumentkategorie "studiengangsverwaltung" mit Gruppe "CMS_LOCK" verknüpft.<br>';
    }
}

// Dokumentenupload für Studienordnung
if(!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_studienordnung_dokument LIMIT 1;"))
{
	$qry = "

	CREATE TABLE addon.tbl_stgv_studienordnung_dokument
	(
		studienordnung_id integer NOT NULL,
		dms_id integer NOT NULL
	);

	ALTER TABLE addon.tbl_stgv_studienordnung_dokument ADD CONSTRAINT pk_studienordnung_dokument PRIMARY KEY (studienordnung_id, dms_id);

	ALTER TABLE addon.tbl_stgv_studienordnung_dokument ADD CONSTRAINT fk_studienordnung_dokument_studienordnung FOREIGN KEY (studienordnung_id) REFERENCES lehre.tbl_studienordnung (studienordnung_id) ON UPDATE CASCADE ON DELETE CASCADE;
	ALTER TABLE addon.tbl_stgv_studienordnung_dokument ADD CONSTRAINT fk_studienordnung_dokument_dms FOREIGN KEY (dms_id) REFERENCES campus.tbl_dms (dms_id) ON UPDATE CASCADE ON DELETE CASCADE;

	GRANT SELECT, INSERT, UPDATE, DELETE ON addon.tbl_stgv_studienordnung_dokument TO vilesci;
	GRANT SELECT, INSERT, UPDATE, DELETE ON addon.tbl_stgv_studienordnung_dokument TO web;
	";

	if(!$db->db_query($qry))
		echo '<strong>Dokumentenupload fuer Studienordnung: '.$db->db_last_error().'</strong><br>';
	else
		echo ' Tabellen fuer Dokumentenupload fuer Studienordnung hinzugefuegt!<br>';
}

// Dokumentenupload für Fördervertrag
if(!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_foerdervertrag_dokument LIMIT 1;"))
{
	$qry = "

	CREATE TABLE addon.tbl_stgv_foerdervertrag_dokument
	(
		foerdervertrag_id integer NOT NULL,
		dms_id integer NOT NULL
	);

	ALTER TABLE addon.tbl_stgv_foerdervertrag_dokument ADD CONSTRAINT pk_foerdervertrag_dokument PRIMARY KEY (foerdervertrag_id, dms_id);

	ALTER TABLE addon.tbl_stgv_foerdervertrag_dokument ADD CONSTRAINT fk_foerdervertrag_dokument_foerdervertrag FOREIGN KEY (foerdervertrag_id) REFERENCES addon.tbl_stgv_foerdervertrag (foerdervertrag_id) ON UPDATE CASCADE ON DELETE CASCADE;
	ALTER TABLE addon.tbl_stgv_foerdervertrag_dokument ADD CONSTRAINT fk_foerdervertrag_dokument_dms FOREIGN KEY (dms_id) REFERENCES campus.tbl_dms (dms_id) ON UPDATE CASCADE ON DELETE CASCADE;

	GRANT SELECT, INSERT, UPDATE, DELETE ON addon.tbl_stgv_foerdervertrag_dokument TO vilesci;
	GRANT SELECT, INSERT, UPDATE, DELETE ON addon.tbl_stgv_foerdervertrag_dokument TO web;
	";

	if(!$db->db_query($qry))
		echo '<strong>Dokumentenupload fuer Foerdervertrag: '.$db->db_last_error().'</strong><br>';
	else
		echo ' Tabellen fuer Dokumentenupload fuer Foerdervertrag hinzugefuegt!<br>';
}

//Tabelle addon.tbl_stgv_qualifikationsziele
if (!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_qualifikationsziele LIMIT 1;")) {
    $qry = "CREATE TABLE addon.tbl_stgv_qualifikationsziele
			(
				qualifikationsziel_id integer NOT NULL,
				studienordnung_id integer NOT NULL,
				data jsonb,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

		CREATE SEQUENCE addon.tbl_stgv_qualifikationsziele_qualifikationsziel_id_seq
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE addon.tbl_stgv_qualifikationsziele ADD CONSTRAINT pk_qualifikationsziele PRIMARY KEY (qualifikationsziel_id);
		ALTER TABLE addon.tbl_stgv_qualifikationsziele ALTER COLUMN qualifikationsziel_id SET DEFAULT nextval('addon.tbl_stgv_qualifikationsziele_qualifikationsziel_id_seq');

		ALTER TABLE addon.tbl_stgv_qualifikationsziele ADD CONSTRAINT fk_qualifikationsziele_studienordnung FOREIGN KEY (studienordnung_id) REFERENCES lehre.tbl_studienordnung (studienordnung_id) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON addon.tbl_stgv_qualifikationsziele TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_stgv_qualifikationsziele TO vilesci;
		GRANT SELECT, UPDATE ON addon.tbl_stgv_qualifikationsziele_qualifikationsziel_id_seq TO vilesci;
	";

    if (!$db->db_query($qry))
	echo '<strong>addon.tbl_stgv_qualifikationsziele: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' addon.tbl_stgv_qualifikationsziele: Tabelle hinzugefuegt<br>';
}

//Tabelle addon.tbl_stgv_auslandssemester
if (!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_auslandssemester LIMIT 1;")) {
    $qry = "CREATE TABLE addon.tbl_stgv_auslandssemester
			(
				auslandssemester_id integer NOT NULL,
				studienplan_id integer NOT NULL,
				erlaeuterungen text,
				data jsonb,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

		CREATE SEQUENCE addon.tbl_stgv_auslandssemester_auslandssemester_id_seq
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE addon.tbl_stgv_auslandssemester ADD CONSTRAINT pk_auslandssemester PRIMARY KEY (auslandssemester_id);
		ALTER TABLE addon.tbl_stgv_auslandssemester ALTER COLUMN auslandssemester_id SET DEFAULT nextval('addon.tbl_stgv_auslandssemester_auslandssemester_id_seq');

		ALTER TABLE addon.tbl_stgv_auslandssemester ADD CONSTRAINT fk_auslandsemester_studienplan FOREIGN KEY (studienplan_id) REFERENCES lehre.tbl_studienplan (studienplan_id) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON addon.tbl_stgv_auslandssemester TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_stgv_auslandssemester TO vilesci;
		GRANT SELECT, UPDATE ON addon.tbl_stgv_auslandssemester_auslandssemester_id_seq TO vilesci;
	";

    if (!$db->db_query($qry))
	echo '<strong>addon.tbl_stgv_auslandssemester: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' addon.tbl_stgv_auslandssemester: Tabelle hinzugefuegt<br>';
}

//Tabelle addon.tbl_stgv_berufspraktikum
if (!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_berufspraktikum LIMIT 1;")) {
    $qry = "CREATE TABLE addon.tbl_stgv_berufspraktikum
			(
				berufspraktikum_id integer NOT NULL,
				studienplan_id integer NOT NULL,
				erlaeuterungen text,
				data jsonb,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

		CREATE SEQUENCE addon.tbl_stgv_berufspraktikum_berufspraktikum_id_seq
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE addon.tbl_stgv_berufspraktikum ADD CONSTRAINT pk_berufspraktikum PRIMARY KEY (berufspraktikum_id);
		ALTER TABLE addon.tbl_stgv_berufspraktikum ALTER COLUMN berufspraktikum_id SET DEFAULT nextval('addon.tbl_stgv_berufspraktikum_berufspraktikum_id_seq');

		ALTER TABLE addon.tbl_stgv_berufspraktikum ADD CONSTRAINT fk_berufspraktikum_studienplan FOREIGN KEY (studienplan_id) REFERENCES lehre.tbl_studienplan (studienplan_id) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON addon.tbl_stgv_berufspraktikum TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_stgv_berufspraktikum TO vilesci;
		GRANT SELECT, UPDATE ON addon.tbl_stgv_berufspraktikum_berufspraktikum_id_seq TO vilesci;
	";

    if (!$db->db_query($qry))
	echo '<strong>addon.tbl_stgv_berufspraktikum: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' addon.tbl_stgv_berufspraktikum: Tabelle hinzugefuegt<br>';
}

//Tabelle addon.tbl_stgv_studienordnung_beschluesse
if (!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_beschluesse LIMIT 1;")) {
    $qry = "CREATE TABLE addon.tbl_stgv_beschluesse
			(
				beschluss_id integer NOT NULL,
				studienordnung_id integer NOT NULL,
				datum timestamp,
				typ varchar(128),
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

		CREATE SEQUENCE addon.tbl_stgv_beschluesse_beschluss_id_seq
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE addon.tbl_stgv_beschluesse ADD CONSTRAINT pk_beschluss PRIMARY KEY (beschluss_id);
		ALTER TABLE addon.tbl_stgv_beschluesse ALTER COLUMN beschluss_id SET DEFAULT nextval('addon.tbl_stgv_beschluesse_beschluss_id_seq');

		ALTER TABLE addon.tbl_stgv_beschluesse ADD CONSTRAINT fk_beschluesse_studienordnung FOREIGN KEY (studienordnung_id) REFERENCES lehre.tbl_studienordnung (studienordnung_id) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON addon.tbl_stgv_beschluesse TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_stgv_beschluesse TO vilesci;
		GRANT SELECT, UPDATE ON addon.tbl_stgv_beschluesse_beschluss_id_seq TO vilesci;
	";

    if (!$db->db_query($qry))
	echo '<strong>addon.tbl_stgv_berufspraktikum: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' addon.tbl_stgv_berufspraktikum: Tabelle hinzugefuegt<br>';
}

//Tabelle addon.tbl_stgv_aufnahmeverfahren
if (!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_aufnahmeverfahren LIMIT 1;")) {
    $qry = "CREATE TABLE addon.tbl_stgv_aufnahmeverfahren
			(
				aufnahmeverfahren_id integer NOT NULL,
				studienordnung_id integer NOT NULL,
				data jsonb,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

		CREATE SEQUENCE addon.tbl_stgv_aufnahmeverfahren_aufnahmeverfahren_id_seq
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE addon.tbl_stgv_aufnahmeverfahren ADD CONSTRAINT pk_aufnahmeverfahren PRIMARY KEY (aufnahmeverfahren_id);
		ALTER TABLE addon.tbl_stgv_aufnahmeverfahren ALTER COLUMN aufnahmeverfahren_id SET DEFAULT nextval('addon.tbl_stgv_aufnahmeverfahren_aufnahmeverfahren_id_seq');

		ALTER TABLE addon.tbl_stgv_aufnahmeverfahren ADD CONSTRAINT fk_aufnahmeverfahren_studienordnung FOREIGN KEY (studienordnung_id) REFERENCES lehre.tbl_studienordnung (studienordnung_id) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON addon.tbl_stgv_aufnahmeverfahren TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_stgv_aufnahmeverfahren TO vilesci;
		GRANT SELECT, UPDATE ON addon.tbl_stgv_aufnahmeverfahren_aufnahmeverfahren_id_seq TO vilesci;
	";

    if (!$db->db_query($qry))
	echo '<strong>addon.tbl_stgv_aufnahmeverfahren: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' addon.tbl_stgv_aufnahmeverfahren: Tabelle hinzugefuegt<br>';
}

//Tabelle addon.tbl_stgv_zugangsvoraussetzung
if (!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_zugangsvoraussetzung LIMIT 1;")) {
    $qry = "CREATE TABLE addon.tbl_stgv_zugangsvoraussetzung
			(
				zugangsvoraussetzung_id integer NOT NULL,
				studienordnung_id integer NOT NULL,
				data jsonb,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

		CREATE SEQUENCE addon.tbl_stgv_zugangsvoraussetzung_zugangsvoraussetzung_id_seq
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE addon.tbl_stgv_zugangsvoraussetzung ADD CONSTRAINT pk_zugangsvoraussetzung PRIMARY KEY (zugangsvoraussetzung_id);
		ALTER TABLE addon.tbl_stgv_zugangsvoraussetzung ALTER COLUMN zugangsvoraussetzung_id SET DEFAULT nextval('addon.tbl_stgv_zugangsvoraussetzung_zugangsvoraussetzung_id_seq');

		ALTER TABLE addon.tbl_stgv_zugangsvoraussetzung ADD CONSTRAINT fk_zugangsvoraussetzung_studienordnung FOREIGN KEY (studienordnung_id) REFERENCES lehre.tbl_studienordnung (studienordnung_id) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON addon.tbl_stgv_zugangsvoraussetzung TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_stgv_zugangsvoraussetzung TO vilesci;
		GRANT SELECT, UPDATE ON addon.tbl_stgv_zugangsvoraussetzung_zugangsvoraussetzung_id_seq TO vilesci;
	";

    if (!$db->db_query($qry))
	echo '<strong>addon.tbl_stgv_zugangsvoraussetzung: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' addon.tbl_stgv_zugangsvoraussetzung: Tabelle hinzugefuegt<br>';
}

// Dokumentenupload für Doktoratsstudienverordnung
if(!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_doktorat_dokument LIMIT 1;"))
{
	$qry = "

	CREATE TABLE addon.tbl_stgv_doktorat_dokument
	(
		doktorat_id integer NOT NULL,
		dms_id integer NOT NULL
	);

	ALTER TABLE addon.tbl_stgv_doktorat_dokument ADD CONSTRAINT pk_doktorat_dokument PRIMARY KEY (doktorat_id, dms_id);

	ALTER TABLE addon.tbl_stgv_doktorat_dokument ADD CONSTRAINT fk_doktorat_dokument_foerervertrag FOREIGN KEY (doktorat_id) REFERENCES addon.tbl_stgv_doktorat (doktorat_id) ON UPDATE CASCADE ON DELETE CASCADE;
	ALTER TABLE addon.tbl_stgv_doktorat_dokument ADD CONSTRAINT fk_doktorat_dokument_dms FOREIGN KEY (dms_id) REFERENCES campus.tbl_dms (dms_id) ON UPDATE CASCADE ON DELETE CASCADE;

	GRANT SELECT, INSERT, UPDATE, DELETE ON addon.tbl_stgv_doktorat_dokument TO vilesci;
	GRANT SELECT, INSERT, UPDATE, DELETE ON addon.tbl_stgv_doktorat_dokument TO web;
	";

	if(!$db->db_query($qry))
		echo '<strong>Dokumentenupload fuer Doktoratsstudienverordnung: '.$db->db_last_error().'</strong><br>';
	else
		echo ' Tabellen fuer Dokumentenupload fuer Doktoratsstudienverordnung hinzugefuegt!<br>';
}

//Tabelle addon.tbl_stgv_studienjahr
if (!$result = @$db->db_query("SELECT 1 FROM addon.tbl_stgv_studienjahr LIMIT 1;")) {
    $qry = "CREATE TABLE addon.tbl_stgv_studienjahr
			(
				studienjahr_id integer NOT NULL,
				studienjahr_kurzbz varchar(16) NOT NULL,
				studienplan_id integer NOT NULL,
				bezeichnung varchar(32),
				data jsonb,
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

		CREATE SEQUENCE addon.tbl_stgv_studienjahr_studienjahr_id_seq
		 INCREMENT BY 1
		 NO MAXVALUE
		 NO MINVALUE
		 CACHE 1;

		ALTER TABLE addon.tbl_stgv_studienjahr ADD CONSTRAINT pk_studienjahr PRIMARY KEY (studienjahr_id);
		ALTER TABLE addon.tbl_stgv_studienjahr ALTER COLUMN studienjahr_id SET DEFAULT nextval('addon.tbl_stgv_studienjahr_studienjahr_id_seq');

		ALTER TABLE addon.tbl_stgv_studienjahr ADD CONSTRAINT fk_studienjahr_studienplan FOREIGN KEY (studienplan_id) REFERENCES lehre.tbl_studienplan (studienplan_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		ALTER TABLE addon.tbl_stgv_studienjahr ADD CONSTRAINT fk_stgv_studienjahr_studienjahr FOREIGN KEY (studienjahr_kurzbz) REFERENCES public.tbl_studienjahr (studienjahr_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;

		GRANT SELECT ON addon.tbl_stgv_studienjahr TO web;
		GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_stgv_studienjahr TO vilesci;
		GRANT SELECT, UPDATE ON addon.tbl_stgv_studienjahr_studienjahr_id_seq TO vilesci;
	";

    if (!$db->db_query($qry))
	echo '<strong>addon.tbl_stgv_studienjahr: ' . $db->db_last_error() . '</strong><br>';
    else
	echo ' addon.tbl_stgv_studienjahr: Tabelle hinzugefuegt<br>';
}

echo '<br>Aktualisierung abgeschlossen<br><br>';
echo '<h2>Gegenprüfung</h2>';


// Liste der verwendeten Tabellen / Spalten des Addons
$tabellen = array(
    "addon.tbl_stgv_foerdervertrag" => array("foerdervertrag_id", "studiengang_kz", "foerdergeber", "foerdersatz", "foerdergruppe", "gueltigvon", "gueltigbis", "erlaeuterungen", "insertamum", "insertvon", "updateamum", "updatevon"),
    "addon.tbl_stgv_aenderungsvariante" => array("aenderungsvariante_kurzbz","bezeichnung"),
    "addon.tbl_stgv_doktorat" => array("doktorat_id", "studiengang_kz", "bezeichnung", "datum_erlass", "gueltigvon", "gueltigbis", "erlaeuterungen", "insertamum", "insertvon", "updateamum", "updatevon"),
    "addon.tbl_stgv_taetigkeitsfelder" => array("taetigkeitsfeld_id", "studienordnung_id", "ueberblick", "data","insertamum", "insertvon", "updateamum", "updatevon"), 
    "addon.tbl_stgv_studiengangsgruppen" => array("studiengangsgruppe_id", "data","insertamum", "insertvon", "updateamum", "updatevon"), 
    "addon.tbl_stgv_studiengangsgruppe_studiengang" => array("studiengangsgruppe_studiengang_id", "studiengang_kz", "data","insertamum", "insertvon", "updateamum", "updatevon"),
    "addon.tbl_stgv_studienordnung_dokument" => array("studienordnung_id","dms_id"),
    "addon.tbl_stgv_qualifikationsziele" => array("qualifikationsziel_id", "studienordnung_id", "data","insertamum", "insertvon", "updateamum", "updatevon"),
    "addon.tbl_stgv_auslandssemester" => array("auslandssemester_id", "studienplan_id", "erlaeuterungen", "data","insertamum", "insertvon", "updateamum", "updatevon"),
    "addon.tbl_stgv_berufspraktikum" => array("berufspraktikum_id", "studienplan_id", "erlaeuterungen", "data","insertamum", "insertvon", "updateamum", "updatevon"),
    "addon.tbl_stgv_beschluesse" => array("beschluss_id", "studienordnung_id", "datum", "typ","insertamum", "insertvon", "updateamum", "updatevon"),
    "addon.tbl_stgv_aufnahmeverfahren" => array("aufnahmeverfahren_id", "studienordnung_id", "data","insertamum", "insertvon", "updateamum", "updatevon"),
    "addon.tbl_stgv_zugangsvoraussetzung" => array("zugangsvoraussetzung_id", "studienordnung_id", "data","insertamum", "insertvon", "updateamum", "updatevon"),
    "addon.tbl_stgv_studienjahr" => array("studienjahr_id", "studienplan_id","bezeichnung", "data","insertamum", "insertvon", "updateamum", "updatevon"),
    "addon.tbl_stgv_studienordnung" => array("studienordnung_id", "aenderungsvariante_kurzbz", "begruendung"),
    "addon.tbl_stgv_studienplan" => array("studienplan_id", "erlaeuterungen", "sprache_kommentar")
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
