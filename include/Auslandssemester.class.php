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
 * Klasse Auslandsemester
 * @create 10-01-2007
 */
//require_once('../../../inlcude/basis_db.class.php');
require_once (dirname(__FILE__).'/../../../include/basis_db.class.php');

class auslandssemester extends basis_db
{
	public $new;			//  boolean
	public $result = array();

	//Tabellenspalten
	public $auslandssemester_id;//  integer
	public $studienplan_id;	//  integer
	public $erlaeuterungen;	//string
	public $data;	//jsonb
	public $insertamum;		//  timestamp
	public $insertvon;		//  string
	public $updateamum;		//  timestamp
	public $updatevon;		//  string

	/**
	 * Konstruktor
	 * @param $auslandssemester_id ID der Adresse die geladen werden soll (Default=null)
	 */
	public function __construct($auslandssemester_id=null)
	{
		parent::__construct();

		if(!is_null($auslandssemester_id))
			$this->load($auslandssemester_id);
	}

	/**
	 * Laedt die Auslandssemester mit der ID $auslandssemester_id
	 * @param  $auslandssemester_id ID der zu ladenden Auslandssemester
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($auslandssemester_id)
	{
		if(!is_numeric($auslandssemester_id))
		{
			$this->errormsg = 'auslandssemester_id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM addon.tbl_stgv_auslandssemester WHERE auslandssemester_id=".$this->db_add_param($auslandssemester_id, FHC_INTEGER, false);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->auslandssemester_id = $row->auslandssemester_id;
				$this->studienplan_id = $row->studienplan_id;
				$this->erlaeuterungen = $row->erlaeuterungen;
				$this->data = json_decode($row->data);
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				return true;
			}
			else
			{
				$this->errormsg = 'Auslandssemester existiert nicht';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Auslandssemester';
			return false;
		}
	}

	/**
	 * Liefert alle Tätigkeitsfelder
	 */
	public function getAll($studienplan_id=null)
	{
		$qry = "SELECT * FROM addon.tbl_stgv_auslandssemester ";
		if($studienplan_id!=null)
			$qry.=" WHERE studienplan_id=".$this->db_add_param($studienplan_id);
		$qry.=";";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new auslandssemester();

				$obj->auslandssemester_id = $row->auslandssemester_id;
				$obj->studienplan_id = $row->studienplan_id;
				$obj->erlaeuterungen = $row->erlaeuterungen;
				$obj->data = json_decode($row->data);
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Auslandssemester.';
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
		if(!is_numeric($this->studienplan_id))
		{
			$this->errormsg='studienplan_id enthaelt ungueltige Zeichen';
			return false;
		}

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
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen

			$qry='BEGIN; INSERT INTO addon.tbl_stgv_auslandssemester (studienplan_id, erlaeuterungen, data, insertamum, insertvon) VALUES('.
			     $this->db_add_param($this->studienplan_id, FHC_INTEGER).', '.
			     $this->db_add_param($this->erlaeuterungen).', '.
			     $this->db_add_param($this->data).', now(),'.
			     $this->db_add_param($this->insertvon).');';
		}
		else
		{
			$qry='UPDATE addon.tbl_stgv_auslandssemester SET '.
				'studienplan_id='.$this->db_add_param($this->studienplan_id, FHC_INTEGER).', '.
				'erlaeuterungen='.$this->db_add_param($this->erlaeuterungen).', '.
				'data='.$this->db_add_param($this->data).', '.
				'updateamum= now(), '.
				'updatevon='.$this->db_add_param($this->updatevon).' '.
				'WHERE auslandssemester_id='.$this->db_add_param($this->auslandssemester_id, FHC_INTEGER, false).';';
		}
		
		if($this->db_query($qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('addon.tbl_stgv_auslandssemester_auslandssemester_id_seq') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->auslandssemester_id = $row->id;
						$this->db_query('COMMIT');
						return true;
					}
					else
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK');
						return false;
					}
				}
				else
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK');
					return false;
				}
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}
	
	public function delete($auslandssemester_id)
	{
	    $qry = "DELETE from addon.tbl_stgv_auslandssemester WHERE auslandssemester_id=".$this->db_add_param($auslandssemester_id);
	    
	    if(!$this->db_query($qry))
	    {
		$this->errormsg = 'Fehler beim Löschen der Daten';
		return false;
	    }
	    
	    return true;
	}
}
