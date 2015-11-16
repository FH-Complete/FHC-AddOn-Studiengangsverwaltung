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

echo '<br>Aktualisierung abgeschlossen<br><br>';
echo '<h2>Gegenprüfung</h2>';


// Liste der verwendeten Tabellen / Spalten des Addons
$tabellen = array(
    "addon.tbl_template_items" => array("template_items_kurzbz", "bezeichnung"),
    "addon.tbl_stgv_foerdervertrag" => array("foerdervertrag_id","studiengang_kz","foerdergeber","foerdersatz","foerdergruppe","gueltigvon","gueltigbis","erlaeuterungen","insertamum","insertvon","updateamum","updatevon")
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
	echo $tabs[$i] . ': OK - ';
    flush();
    $i++;
}
?>
