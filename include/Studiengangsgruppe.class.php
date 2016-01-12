<?php

/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Stefan Puraner <stefan.puraner@technikum-wien.at>
 */
/**
 * Klasse Studiengangsgruppe
 * @create 10-01-2007
 */
//require_once('../../../inlcude/basis_db.class.php');
require_once (dirname(__FILE__) . '/../../../include/basis_db.class.php');

class studiengangsgruppe extends basis_db
{

    public $new;   //  boolean
    public $result = array();
    //Tabellenspalten
    public $studiengangsgruppe_id; //  integer
    public $data; //string
    public $insertamum;  //  timestamp
    public $insertvon;  //  string
    public $updateamum;  //  timestamp
    public $updatevon;  //  string
    
    public $studiengangsgruppe_studiengang_id;
    public $studiengang_kz;

    /**
     * Konstruktor
     * @param $studiengangsgruppe_id ID der Adresse die geladen werden soll (Default=null)
     */
    public function __construct($studiengangsgruppe_id = null)
    {
	parent::__construct();

	if (!is_null($studiengangsgruppe_id))
	    $this->load($studiengangsgruppe_id);
    }

    /**
     * Laedt die Studiengangsgruppen mit der ID $studiengangsgruppe_id
     * @param  $studiengangsgruppe_id ID der zu ladenden Studiengangsgruppen
     * @return true wenn ok, false im Fehlerfall
     */
    public function load($studiengangsgruppe_id)
    {
	if (!is_numeric($studiengangsgruppe_id))
	{
	    $this->errormsg = 'studiengangsgruppe_id ist ungueltig';
	    return false;
	}

	$qry = "SELECT * FROM addon.tbl_stgv_studiengangsgruppen WHERE studiengangsgruppe_id=" . $this->db_add_param($studiengangsgruppe_id, FHC_INTEGER, false);

	if ($this->db_query($qry))
	{
	    if ($row = $this->db_fetch_object())
	    {
		$this->studiengangsgruppe_id = $row->studiengangsgruppe_id;
		$this->data = json_decode($row->data);
		$this->insertamum = $row->insertamum;
		$this->insertvon = $row->insertvon;
		$this->updateamum = $row->updateamum;
		$this->updatevon = $row->updatevon;
		return true;
	    } else
	    {
		$this->errormsg = 'Studiengangsgruppe existiert nicht';
		return false;
	    }
	} else
	{
	    $this->errormsg = 'Fehler beim Laden der Studiengangsgruppen';
	    return false;
	}
    }

    /**
     * Liefert alle Studiengangsgruppen
     */
    public function getAll()
    {
	$qry = "SELECT * FROM addon.tbl_stgv_studiengangsgruppen;";

	if ($this->db_query($qry))
	{
	    while ($row = $this->db_fetch_object())
	    {
		$obj = new studiengangsgruppe();

		$obj->studiengangsgruppe_id = $row->studiengangsgruppe_id;
		$obj->data = json_decode($row->data);
		$obj->insertamum = $row->insertamum;
		$obj->insertvon = $row->insertvon;
		$obj->updateamum = $row->updateamum;
		$obj->updatevon = $row->updatevon;

		$this->result[] = $obj;
	    }
	    return true;
	} else
	{
	    $this->errormsg = 'Fehler beim Laden der Studiengangsgruppen.';
	    return false;
	}
    }

    /**
     * Prueft die Variablen auf Gueltigkeit
     * @return true wenn ok, false im Fehlerfall
     */
    private function validate()
    {
	//Zahlenfelder pruefen

	$this->errormsg = '';
	return true;
    }

    /**
     * Speichert den aktuellen Datensatz in die Datenbank
     * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
     * andernfalls wird der Datensatz mit der ID in $reihungstest_id aktualisiert
     * @return true wenn ok, false im Fehlerfall
     */
    public function save()
    {
	if (!$this->validate())
	    return false;

	if ($this->new)
	{
	    //Neuen Datensatz einfuegen

	    $qry = 'BEGIN; INSERT INTO addon.tbl_stgv_studiengangsgruppen (code, text, studiengangsgruppe_parent_id, insertamum, insertvon) VALUES(' .
		    $this->db_add_param($this->code) . ', ' .
		    $this->db_add_param($this->text) . ', ' .
		    $this->db_add_param($this->studiengangsgruppe_parent_id) . ', now(),' .
		    $this->db_add_param($this->insertvon) . ');';
	} else
	{
	    $qry = 'UPDATE addon.tbl_stgv_studiengangsgruppen SET ' .
		    'code=' . $this->db_add_param($this->code) . ', ' .
		    'text=' . $this->db_add_param($this->text) . ', ' .
		    'studiengangsgruppe_parent_id=' . $this->db_add_param($this->studiengangsgruppe_parent_id) . ', ' .
		    'updateamum= now(), ' .
		    'updatevon=' . $this->db_add_param($this->updatevon) . ' ' .
		    'WHERE studiengangsgruppe_id=' . $this->db_add_param($this->studiengangsgruppe_id, FHC_INTEGER, false) . ';';
	}

	if ($this->db_query($qry))
	{
	    if ($this->new)
	    {
		$qry = "SELECT currval('addon.tbl_stgv_studiengangsgruppen_studiengangsgruppe_id_seq') as id";
		if ($this->db_query($qry))
		{
		    if ($row = $this->db_fetch_object())
		    {
			$this->studiengangsgruppe_id = $row->id;
			$this->db_query('COMMIT');
			return true;
		    } else
		    {
			$this->errormsg = 'Fehler beim Auslesen der Sequence';
			$this->db_query('ROLLBACK');
			return false;
		    }
		} else
		{
		    $this->errormsg = 'Fehler beim Auslesen der Sequence';
		    $this->db_query('ROLLBACK');
		    return false;
		}
	    }
	    return true;
	} else
	{
	    $this->errormsg = 'Fehler beim Speichern der Daten';
	    return false;
	}
    }

    public function delete($studiengangsgruppe_id)
    {
	$qry = "DELETE from addon.tbl_stgv_studiengangsgruppen WHERE studiengangsgruppe_id=" . $this->db_add_param($studiengangsgruppe_id);

	if (!$this->db_query($qry))
	{
	    $this->errormsg = 'Fehler beim Löschen der Daten';
	    return false;
	}

	return true;
    }
    
    public function saveZuordnung()
    {
	if (!$this->validate())
	    return false;

	if ($this->new)
	{
	    //Neuen Datensatz einfuegen

	    $qry = 'BEGIN; INSERT INTO addon.tbl_stgv_studiengangsgruppe_studiengang (studiengang_kz, data, insertamum, insertvon) VALUES(' .
		    $this->db_add_param($this->studiengang_kz) . ', ' .
		    $this->db_add_param($this->data) . ', now(),' .
		    $this->db_add_param($this->insertvon) . ');';
	} else
	{
	    $qry = 'UPDATE addon.tbl_stgv_studiengangsgruppen SET ' .
		    'studiengang_kz=' . $this->db_add_param($this->studiengang_kz) . ', ' .
		    'data=' . $this->db_add_param($this->data) . ', ' .
		    'updateamum= now(), ' .
		    'updatevon=' . $this->db_add_param($this->updatevon) . ' ' .
		    'WHERE studiengangsgruppe_studiengang_id=' . $this->db_add_param($this->studiengangsgruppe_studiengang_id, FHC_INTEGER, false) . ';';
	}

	if ($this->db_query($qry))
	{
	    if ($this->new)
	    {
		$qry = "SELECT currval('addon.tbl_stgv_studiengangsgruppe_studiengang_studiengangsgruppe_studiengang_id_seq') as id";
		if ($this->db_query($qry))
		{
		    if ($row = $this->db_fetch_object())
		    {
			$this->studiengangsgruppe_studiengang_id = $row->id;
			$this->db_query('COMMIT');
			return true;
		    } else
		    {
			$this->errormsg = 'Fehler beim Auslesen der Sequence';
			$this->db_query('ROLLBACK');
			return false;
		    }
		} else
		{
		    $this->errormsg = 'Fehler beim Auslesen der Sequence';
		    $this->db_query('ROLLBACK');
		    return false;
		}
	    }
	    return true;
	} else
	{
	    $this->errormsg = 'Fehler beim Speichern der Daten';
	    return false;
	}
    }
    
    public function loadZuordnung($studiengang_kz)
    {
	if (!is_numeric($studiengang_kz))
	{
	    $this->errormsg = 'studiengang_kz ist ungueltig';
	    return false;
	}

	$qry = "SELECT * FROM addon.tbl_stgv_studiengangsgruppe_studiengang WHERE studiengang_kz=" . $this->db_add_param($studiengang_kz, FHC_INTEGER, false);

	if ($this->db_query($qry))
	{
	    if ($row = $this->db_fetch_object())
	    {
		$this->studiengangsgruppe_studiengang_id = $row->studiengangsgruppe_studiengang_id;
		$this->studiengang_kz = $row->studiengang_kz;
		$this->data = json_decode($row->data);
		$this->insertamum = $row->insertamum;
		$this->insertvon = $row->insertvon;
		$this->updateamum = $row->updateamum;
		$this->updatevon = $row->updatevon;
		return true;
	    } else
	    {
		$this->errormsg = 'Studiengangsgruppe existiert nicht';
		return false;
	    }
	} else
	{
	    $this->errormsg = 'Fehler beim Laden der Studiengangsgruppen';
	    return false;
	}
    }
    
    public function deleteZuordnung($studiengang_kz)
    {
	$qry = "DELETE from addon.tbl_stgv_studiengangsgruppe_studiengang WHERE studiengang_kz=" . $this->db_add_param($studiengang_kz);

	if (!$this->db_query($qry))
	{
	    $this->errormsg = 'Fehler beim Löschen der Daten';
	    return false;
	}

	return true;
    }

}
