<?php

/*
 * 
 * Copyright 2015 fhcomplete.org
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 *
 * Authors: Stefan Puraner <stefan.puraner@technikum-wien.at>
 */

require_once(dirname(__FILE__) . '/../../../include/studienordnung.class.php');

class StudienordnungAddonStgv extends studienordnung
{

    public $aenderungsvariante_kurzbz; //varchar(32)
    public $status_kurzbz;  //varchar(32)
    public $begruendung;  //text

    /**
     * Konstruktor
     */

    public function __construct()
    {
	parent::__construct();
    }

    public function __set($name, $value)
    {
	switch ($name)
	{
	    case 'studiengang_kz':
		if (!is_numeric($value))
		    throw new Exception('Attribute studiengang_kz must be numeric!"');
		$this->$name = $value;
		break;
	    default:
		$this->$name = $value;
	}
    }

    public function __get($name)
    {
	return $this->$name;
    }

    public function loadStudienordnungWithStatus($studiengang_kz, $status_kurzbz)
    {
	$qry = "SELECT * FROM lehre.tbl_studienordnung WHERE "
		. "status_kurzbz=" . $this->db_add_param($status_kurzbz, FHC_STRING) . ""
		. " AND studiengang_kz=" . $this->db_add_param($studiengang_kz, FHC_INTEGER) . ";";

	if (!$this->db_query($qry))
	{
	    $this->errormsg = 'Fehler bei einer Datenbankabfrage';
	    return false;
	}

	while ($row = $this->db_fetch_object())
	{
	    $obj = new studienordnung();

	    $obj->studienordnung_id = $row->studienordnung_id;
	    $obj->studiengang_kz = $row->studiengang_kz;
	    $obj->version = $row->version;
	    $obj->bezeichnung = $row->bezeichnung;
	    $obj->ects = $row->ects;
	    $obj->gueltigvon = $row->gueltigvon;
	    $obj->gueltigbis = $row->gueltigbis;
	    $obj->studiengangbezeichnung = $row->studiengangbezeichnung;
	    $obj->studiengangbezeichnung_englisch = $row->studiengangbezeichnung_englisch;
	    $obj->studiengangkurzbzlang = $row->studiengangkurzbzlang;
	    $obj->akadgrad_id = $row->akadgrad_id;
	    $obj->aenderungsvariante_kurzbz = $row->aenderungsvariante_kurzbz;
	    $obj->status_kurzbz = $row->status_kurzbz;
	    $obj->begruendung = $row->begruendung;
	    $obj->updateamum = $row->updateamum;
	    $obj->updatevon = $row->updatevon;
	    $obj->insertamum = $row->insertamum;
	    $obj->insertvon = $row->insertvon;
	    $obj->new = false;
	    $this->result[] = $obj;
	}
	return true;
    }

    public function save()
    {
//Variablen pruefen
	if (!$this->validate())
	    return false;

	if ($this->new)
	{
//Neuen Datensatz einfuegen
	    $qry = 'BEGIN;INSERT INTO lehre.tbl_studienordnung (studiengang_kz, version, bezeichnung, ects, gueltigvon, gueltigbis, studiengangbezeichnung, studiengangbezeichnung_englisch, studiengangkurzbzlang, akadgrad_id, aenderungsvariante_kurzbz, status_kurzbz, begruendung, insertamum, insertvon) VALUES (' .
		    $this->db_add_param($this->studiengang_kz, FHC_INTEGER) . ', ' .
		    $this->db_add_param($this->version) . ', ' .
		    $this->db_add_param($this->bezeichnung) . ', ' .
		    $this->db_add_param($this->ects) . ', ' .
		    $this->db_add_param($this->gueltigvon) . ', ' .
		    $this->db_add_param($this->gueltigbis) . ', ' .
		    $this->db_add_param($this->studiengangbezeichnung) . ', ' .
		    $this->db_add_param($this->studiengangbezeichnung_englisch) . ', ' .
		    $this->db_add_param($this->studiengangkurzbzlang) . ', ' .
		    $this->db_add_param($this->akadgrad_id, FHC_INTEGER) . ', ' .
		    $this->db_add_param($this->aenderungsvariante_kurzbz) . ', ' .
		    $this->db_add_param($this->status_kurzbz) . ', ' .
		    $this->db_add_param($this->begruendung) . ', ' .
		    ' now(), ' .
		    $this->db_add_param($this->insertvon) . ');';
	} else
	{
//Pruefen ob studienordnung_id eine gueltige Zahl ist
	    if (!is_numeric($this->studienordnung_id))
	    {
		$this->errormsg = 'studienordnung_id muss eine gueltige Zahl sein';
		return false;
	    }
	    $qry = 'UPDATE lehre.tbl_studienordnung SET' .
		    ' studiengang_kz=' . $this->db_add_param($this->studiengang_kz, FHC_INTEGER) . ', ' .
		    ' version=' . $this->db_add_param($this->version) . ', ' .
		    ' bezeichnung=' . $this->db_add_param($this->bezeichnung) . ', ' .
		    ' ects=' . $this->db_add_param($this->ects) . ', ' .
		    ' gueltigvon=' . $this->db_add_param($this->gueltigvon) . ', ' .
		    ' gueltigbis=' . $this->db_add_param($this->gueltigbis) . ', ' .
		    ' studiengangbezeichnung=' . $this->db_add_param($this->studiengangbezeichnung) . ', ' .
		    ' studiengangbezeichnung_englisch=' . $this->db_add_param($this->studiengangbezeichnung_englisch) . ', ' .
		    ' studiengangkurzbzlang=' . $this->db_add_param($this->studiengangkurzbzlang) . ',' .
		    ' akadgrad_id=' . $this->db_add_param($this->akadgrad_id, FHC_INTEGER) . ', ' .
		    ' aenderungsvariante_kurzbz=' . $this->db_add_param($this->aenderungsvariante_kurzbz) . ', ' .
		    ' status_kurzbz=' . $this->db_add_param($this->status_kurzbz) . ', ' .
		    ' begruendung=' . $this->db_add_param($this->begruendung) . ', ' .
		    ' updateamum= now(), ' .
		    ' updatevon=' . $this->db_add_param($this->updatevon) . ' ' .
		    ' WHERE studienordnung_id=' . $this->db_add_param($this->studienordnung_id, FHC_INTEGER, false) . ';';
	}

	if ($this->db_query($qry))
	{
	    if ($this->new)
	    {
//naechste ID aus der Sequence holen
		$qry = "SELECT currval('lehre.seq_studienordnung_studienordnung_id') as id;";
		if ($this->db_query($qry))
		{
		    if ($row = $this->db_fetch_object())
		    {
			$this->studienordnung_id = $row->id;
			$this->db_query('COMMIT');
		    } else
		    {
			$this->db_query('ROLLBACK');
			$this->errormsg = "Fehler beim Auslesen der Sequence";
			return false;
		    }
		} else
		{
		    $this->db_query('ROLLBACK');
		    $this->errormsg = 'Fehler beim Auslesen der Sequence';
		    return false;
		}
	    }
	} else
	{
	    $this->errormsg = 'Fehler beim Speichern des Datensatzes';
	    return false;
	}
	return $this->studienordnung_id;
    }

    /**
     * Laedt die Studienordnung mit der ID $studienordnung_id
     * @param  $studienordnung_id ID der zu ladenden Studienordnung
     * @return true wenn ok, false im Fehlerfall
     */
    public function loadStudienordnung($studienordnung_id)
    {
	//Pruefen ob studienordnung_id eine gueltige Zahl ist
	if (!is_numeric($studienordnung_id) || $studienordnung_id == '')
	{
	    $this->errormsg = 'Studienordnung_id muss eine Zahl sein';
	    return false;
	}

	//Daten aus der Datenbank lesen
	$qry = "SELECT * FROM lehre.tbl_studienordnung WHERE studienordnung_id=" . $this->db_add_param($studienordnung_id, FHC_INTEGER, false);

	if (!$this->db_query($qry))
	{
	    $this->errormsg = 'Fehler bei einer Datenbankabfrage';
	    return false;
	}

	if ($row = $this->db_fetch_object())
	{
	    $this->studienordnung_id = $row->studienordnung_id;
	    $this->studiengang_kz = $row->studiengang_kz;
	    $this->version = $row->version;
	    $this->bezeichnung = $row->bezeichnung;
	    $this->ects = $row->ects;
	    $this->gueltigvon = $row->gueltigvon;
	    $this->gueltigbis = $row->gueltigbis;
	    $this->studiengangbezeichnung = $row->studiengangbezeichnung;
	    $this->studiengangbezeichnung_englisch = $row->studiengangbezeichnung_englisch;
	    $this->studiengangkurzbzlang = $row->studiengangkurzbzlang;
	    $this->akadgrad_id = $row->akadgrad_id;
	    $this->aenderungsvariante_kurzbz = $row->aenderungsvariante_kurzbz;
	    $this->status_kurzbz = $row->status_kurzbz;
	    $this->begruendung = $row->begruendung;
	    $this->updateamum = $row->updateamum;
	    $this->updatevon = $row->updatevon;
	    $this->insertamum = $row->insertamum;
	    $this->insertvon = $row->insertvon;
	} else
	{
	    $this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
	    return false;
	}
	$this->new = false;
	return true;
    }

    /**
	 * Laedt alle Studienordnungen zu einem Studiengang der uebergeben wird
	 * @param $studiengang_kz Kennzahl des Studiengangs
	 * @param $studiensemester_kurzbz
	 * @param $semester
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadStudienordnungSTG($studiengang_kz,$studiensemester_kurzbz=null, $semester=null)
	{
		//Pruefen ob studiengang_kz eine gueltige Zahl ist
		if(!is_numeric($studiengang_kz) || $studiengang_kz === '')
		{
			$this->errormsg = 'studiengang_kz muss eine gültige Zahl sein';
			return false;
		}

		if(is_null($studiensemester_kurzbz))
		{
			$qry = 'SELECT 
						* 
					FROM 
						lehre.tbl_studienordnung 
					WHERE 
						studiengang_kz='.$this->db_add_param($studiengang_kz, FHC_INTEGER, false);
		}
		else
		{
			$qry = 'SELECT 
						* 
					FROM 
						lehre.tbl_studienordnung 
						LEFT JOIN lehre.tbl_studienordnung_semester USING (studienordnung_id) 
					WHERE 
						studiengang_kz='.$this->db_add_param($studiengang_kz, FHC_INTEGER, false);

			if (!is_null($studiensemester_kurzbz))
				$qry.=" AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz, FHC_STRING,false);
			if (!is_null($semester))
				$qry.=" AND semester=".$this->db_add_param($semester, FHC_INTEGER,false);
		}

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$obj = new studienordnung();

			$obj->studienordnung_id	= $row->studienordnung_id;
			$obj->studiengang_kz	= $row->studiengang_kz;
			$obj->version			= $row->version;
			$obj->bezeichnung		= $row->bezeichnung;
			$obj->ects				= $row->ects;
			$obj->gueltigvon		= $row->gueltigvon;
			$obj->gueltigbis		= $row->gueltigbis;
			$obj->studiengangbezeichnung	= $row->studiengangbezeichnung;
			$obj->studiengangbezeichnung_englisch	= $row->studiengangbezeichnung_englisch;
			$obj->studiengangkurzbzlang	= $row->studiengangkurzbzlang;
			$obj->akadgrad_id		= $row->akadgrad_id;
			$obj->aenderungsvariante_kurzbz = $row->aenderungsvariante_kurzbz;
			$obj->status_kurzbz		= $row->status_kurzbz;
			$obj->updateamum		= $row->updateamum;
			$obj->updatevon			= $row->updatevon;
			$obj->insertamum		= $row->insertamum;
			$obj->insertvon			= $row->insertvon;
			$obj->new				= false;

			if(!is_null($studiensemester_kurzbz))
			{
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->semester = $row->semester;
				$obj->studienordnung_semester_id = $row->studienordnung_semester_id;
			}
			$this->result[] = $obj;
		}
		return true;
	}
}
