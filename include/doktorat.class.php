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
 * Klasse Doktorat
 * @create 10-01-2007
 */
//require_once('../../../inlcude/basis_db.class.php');
require_once (dirname(__FILE__).'/../../../include/basis_db.class.php');

class doktorat extends basis_db
{
	public $new;			//  boolean
	public $result = array();

	//Tabellenspalten
	public $doktorat_id;//  integer
	public $studiengang_kz;	//  integer
	public $bezeichnung;	//string
	public $datum_erlass;	//timestamp
	public $gueltigvon;		//  string
	public $gueltigbis;		//  string
	public $erlaeuterungen;		//  string
	public $insertamum;		//  timestamp
	public $insertvon;		//  bigint
	public $updateamum;		//  timestamp
	public $updatevon;		//  bigint
	public $dokumente = array();

	/**
	 * Konstruktor
	 * @param $doktorat_id ID der Adresse die geladen werden soll (Default=null)
	 */
	public function __construct($doktorat_id=null)
	{
		parent::__construct();

		if(!is_null($doktorat_id))
			$this->load($doktorat_id);
	}

	/**
	 * Laedt die Doktoratsstudienverordnung mit der ID $doktorat_id
	 * @param  $doktorat_id ID der zu ladenden Doktoratsstudienverordnung
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($doktorat_id)
	{
		if(!is_numeric($doktorat_id))
		{
			$this->errormsg = 'Doktorat_id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM addon.tbl_stgv_doktorat WHERE doktorat_id=".$this->db_add_param($doktorat_id, FHC_INTEGER, false);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->doktorat_id = $row->doktorat_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->bezeichnung = $row->bezeichnung;
				$this->datum_erlass = $row->datum_erlass;
				$this->gueltigvon = $row->gueltigvon;
				$this->gueltigbis = $row->gueltigbis;
				$this->erlaeuterungen = $row->erlaeuterungen;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				return true;
			}
			else
			{
				$this->errormsg = 'Doktorat existiert nicht';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Doktorats';
			return false;
		}
	}

	/**
	 * Liefert alle Doktoratsstudienverordnungen
	 */
	public function getAll($stg_kz=null)
	{
		$qry = "SELECT * FROM addon.tbl_stgv_doktorat ";
		if($stg_kz!=null)
			$qry.=" WHERE studiengang_kz=".$this->db_add_param($stg_kz);
		$qry.=";";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new doktorat();

				$obj->doktorat_id = $row->doktorat_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->datum_erlass = $row->datum_erlass;
				$obj->gueltigvon = $row->gueltigvon;
				$obj->gueltigbis = $row->gueltigbis;
				$obj->erlaeuterungen = $row->erlaeuterungen;
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
			$this->errormsg = 'Fehler beim Laden der Doktoratsverordnungen.';
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
		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg='studiengang_kz enthaelt ungueltige Zeichen';
			return false;
		}

		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $doktorat_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen

			$qry='BEGIN; INSERT INTO addon.tbl_stgv_doktorat (studiengang_kz, bezeichnung, datum_erlass, gueltigvon, gueltigbis, erlaeuterungen,
				 insertamum, insertvon) VALUES('.
			     $this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
			     $this->db_add_param($this->bezeichnung).', '.
			     $this->db_add_param($this->datum_erlass).', '.
			     $this->db_add_param($this->gueltigvon).', '.
			     $this->db_add_param($this->gueltigbis).', '.
			     $this->db_add_param($this->erlaeuterungen).', now(),'.
			     $this->db_add_param($this->insertvon).');';
		}
		else
		{
			$qry='UPDATE addon.tbl_stgv_doktorat SET '.
				'studiengang_kz='.$this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
				'bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
				'datum_erlass='.$this->db_add_param($this->datum_erlass).', '.
				'gueltigvon='.$this->db_add_param($this->gueltigvon).', '.
				'gueltigbis='.$this->db_add_param($this->gueltigbis).', '.
				'erlaeuterungen='.$this->db_add_param($this->erlaeuterungen).', '.
				'updateamum= now(), '.
				'updatevon='.$this->db_add_param($this->updatevon).' '.
				'WHERE doktorat_id='.$this->db_add_param($this->doktorat_id, FHC_INTEGER, false).';';
		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('addon.tbl_stgv_doktorat_doktorat_id_seq') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->doktorat_id = $row->id;
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

	public function delete($doktorat_id)
	{
	    $qry = "DELETE from addon.tbl_stgv_doktorat WHERE doktorat_id=".$this->db_add_param($doktorat_id);

	    if(!$this->db_query($qry))
	    {
		$this->errormsg = 'Fehler beim Löschen der Daten';
		return false;
	    }

	    return true;
	}

	/**
     * Speichert ein Dokument zum Doktorat
     * @param int $dms_id
     * @return boolean
     */
    public function saveDokument($dms_id)
    {
	$qry = "INSERT INTO addon.tbl_stgv_doktorat_dokument(doktorat_id, dms_id) VALUES(" .
		$this->db_add_param($this->doktorat_id, FHC_INTEGER) . ',' .
		$this->db_add_param($dms_id, FHC_INTEGER) . ');';

	if ($this->db_query($qry))
	{
	    return true;
	} else
	{
	    $this->errormsg = 'Fehler beim Speichern der Daten';
	    return false;
	}
    }

    /**
     * Laedt die Dokumente des Doktorats
     * @return boolean
     */
    public function getDokumente($doktorat_id)
    {
	$qry = "SELECT dms_id FROM addon.tbl_stgv_doktorat_dokument WHERE doktorat_id=" . $this->db_add_param($doktorat_id, FHC_INTEGER);

	if ($this->db_query($qry))
	{
	    while ($row = $this->db_fetch_object())
	    {
		$this->dokumente[] = $row->dms_id;
	    }

	    return true;
	} else
	{
	    $this->errormsg = 'Fehler beim Laden der Daten';
	    return false;
	}
    }

    /**
     * Löscht ein Dokument
     * @param  $doktorat_id
     * @param  $dms_id
     * @return true wenn ok, false im Fehlerfall
     */
    public function deleteDokument($doktorat_id, $dms_id)
    {
	if (!is_numeric($doktorat_id))
	{
	    $this->errormsg = 'doktorat_id ist ungueltig';
	    return false;
	}

	if (!is_numeric($dms_id))
	{
	    $this->errormsg = 'dms_id ist ungueltig';
	    return false;
	}

	// Dokument löschen
	$dms = new dms();
	if($dms->deleteDms($dms_id))
	{
	    $qry = "Delete FROM addon.tbl_stgv_doktorat_dokument "
		    . "WHERE doktorat_id=" . $this->db_add_param($doktorat_id, FHC_INTEGER)
		    . " AND dms_id=".$this->db_add_param($dms_id, FHC_INTEGER);

	    if (!$this->db_query($qry))
	    {
		$this->errormsg = 'Fehler beim Loeschen der Daten';
		return false;
	    }
	    return true;
	}
	else
	{
	    $this->errormsg = 'Fehler beim Loeschen des Dokuments.';
	    return false;
	}


    }

	/**
	 * Prueft ob eine Zuordnung des Dokuments zu einem Doktoratseintrag existiert
	 * @param $doktorat_id integer ID des Doktoratseintrags
	 * @param $dms_id integer ID des Dokuments
	 * @return boolean true wenn vorhanden, false wenn nicht oder Fehler
	 */
	public function DokumentExists($doktorat_id, $dms_id)
	{
		$qry = "SELECT * FROM addon.tbl_stgv_doktorat_dokument
				WHERE
					doktorat_id=".$this->db_add_param($doktorat_id, FHC_INTEGER)."
					AND dms_id=".$this->db_add_param($dms_id, FHC_INTEGER);

		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result) > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
