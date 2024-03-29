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

require_once(dirname(__FILE__).'/../../../include/studienplan.class.php');

class StudienplanAddonStgv extends studienplan
{

	public $ects_stpl;
	public $pflicht_sws;
	public $pflicht_lvs;
	public $onlinebewerbung_studienplan=true;
	public $erlaeuterungen;
	public $sprache_kommentar;

	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen pruefen
		if (!$this->validate())
			return false;

		if ($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry = 'BEGIN;INSERT INTO lehre.tbl_studienplan (studienordnung_id, orgform_kurzbz,version,
					bezeichnung, regelstudiendauer, sprache, aktiv, semesterwochen, testtool_sprachwahl,
					pflicht_sws, pflicht_lvs, ects_stpl, onlinebewerbung_studienplan,
					insertamum, insertvon) VALUES ('.
				$this->db_add_param($this->studienordnung_id, FHC_INTEGER).', '.
				$this->db_add_param($this->orgform_kurzbz).', '.
				$this->db_add_param($this->version).', '.
				$this->db_add_param($this->bezeichnung).', '.
				$this->db_add_param($this->regelstudiendauer, FHC_INTEGER).', '.
				$this->db_add_param($this->sprache).', '.
				$this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
				$this->db_add_param($this->semesterwochen, FHC_INTEGER).', '.
				$this->db_add_param($this->testtool_sprachwahl, FHC_BOOLEAN).', '.
				$this->db_add_param($this->pflicht_sws).', '.
				$this->db_add_param($this->pflicht_lvs).', '.
				$this->db_add_param($this->ects_stpl).', '.
				$this->db_add_param($this->onlinebewerbung_studienplan, FHC_BOOLEAN).', '.
				'now(), '.
				$this->db_add_param($this->insertvon).');';
		}
		else
		{
			//Pruefen ob studienplan_id eine gueltige Zahl ist
			if (!is_numeric($this->studienplan_id))
			{
				$this->errormsg = 'studienplan_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry = 'UPDATE lehre.tbl_studienplan SET'.
				' studienordnung_id='.$this->db_add_param($this->studienordnung_id, FHC_INTEGER).', '.
				' orgform_kurzbz='.$this->db_add_param($this->orgform_kurzbz).', '.
				' version='.$this->db_add_param($this->version).', '.
				' bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
				' regelstudiendauer='.$this->db_add_param($this->regelstudiendauer, FHC_INTEGER).', '.
				' sprache='.$this->db_add_param($this->sprache).', '.
				' aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
				' semesterwochen='.$this->db_add_param($this->semesterwochen, FHC_INTEGER).', '.
				' testtool_sprachwahl='.$this->db_add_param($this->testtool_sprachwahl, FHC_BOOLEAN).','.
				' ects_stpl='.$this->db_add_param($this->ects_stpl).','.
				' pflicht_sws='.$this->db_add_param($this->pflicht_sws, FHC_INTEGER).','.
				' pflicht_lvs='.$this->db_add_param($this->pflicht_lvs, FHC_INTEGER).','.
				' onlinebewerbung_studienplan='.$this->db_add_param($this->onlinebewerbung_studienplan, FHC_BOOLEAN).','.
				' updateamum='.$this->db_add_param(date("Y-m-d H:i:s")).','.
				' updatevon='.$this->db_add_param($this->updatevon).' '.
				' WHERE studienplan_id='.$this->db_add_param($this->studienplan_id, FHC_INTEGER, false).';';

			//if id is not in the addon table, insert it, otherwise update
			$qrycheckid = 'SELECT studienplan_id FROM addon.tbl_stgv_studienplan WHERE studienplan_id='.$this->db_add_param($this->studienplan_id, FHC_INTEGER, false).';';
			if ($this->db_query($qrycheckid))
			{
				$numids = $this->db_num_rows($this->db_query($qrycheckid));
				if ($numids < 1)
				{
					$qryinsaddondata = 'BEGIN;INSERT INTO addon.tbl_stgv_studienplan (studienplan_id, erlaeuterungen, sprache_kommentar) VALUES ('.
						$this->db_add_param($this->studienplan_id, FHC_INTEGER).', '.
						$this->db_add_param($this->erlaeuterungen, FHC_STRING).', '.
						$this->db_add_param($this->sprache_kommentar, FHC_STRING).');';

					if ($this->db_query($qryinsaddondata))
					{
						$this->db_query('COMMIT');
					}
					else
					{
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Speichern der Daten";
						return false;
					}
				}
				else
				{
					$qry .= 'UPDATE addon.tbl_stgv_studienplan SET'.
						' erlaeuterungen='.$this->db_add_param($this->erlaeuterungen, FHC_STRING).','.
						' sprache_kommentar='.$this->db_add_param($this->sprache_kommentar, FHC_STRING).' '.
						' WHERE studienplan_id='.$this->db_add_param($this->studienplan_id, FHC_INTEGER, false).';';
				}
			}
		}

		if ($this->db_query($qry))
		{
			if ($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry = "SELECT currval('lehre.seq_studienplan_studienplan_id') as id;";
				if ($this->db_query($qry))
				{
					if ($row = $this->db_fetch_object())
					{
						$this->studienplan_id = $row->id;
						$qry = 'BEGIN;INSERT INTO addon.tbl_stgv_studienplan (studienplan_id, erlaeuterungen, sprache_kommentar) VALUES ('.
							$this->db_add_param($this->studienplan_id, FHC_INTEGER).', '.
							$this->db_add_param($this->erlaeuterungen, FHC_STRING).', '.
							$this->db_add_param($this->sprache_kommentar, FHC_STRING).');';

						if ($this->db_query($qry))
						{
							$this->db_query('COMMIT');
						}
						else
						{
							$this->db_query('ROLLBACK');
							$this->errormsg = "Fehler beim Speichern der Daten";
							return false;
						}
					}
					else
					{
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
		return $this->studienplan_id;
	}

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		//Zahlenfelder pruefen
		if (!is_numeric($this->studienordnung_id))
		{
			$this->errormsg = 'studienordnung_id enthaelt ungueltige Zeichen';
			return false;
		}

		//Gesamtlaenge pruefen
		if (mb_strlen($this->version) > 256)
		{
			$this->errormsg = 'Version darf nicht länger als 256 Zeichen sein';
			return false;
		}
		if (mb_strlen($this->bezeichnung) > 256)
		{
			$this->errormsg = 'Bezeichnung darf nicht länger als 256 Zeichen sein';
			return false;
		}
		if (mb_strlen($this->orgform_kurzbz) > 3)
		{
			$this->errormsg = 'Orgform_kurzbz darf nicht laenger als 3 Zeichen sein';
			return false;
		}
		if (mb_strlen($this->sprache) > 16)
		{
			$this->errormsg = 'sprache darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if (!is_bool($this->aktiv))
		{
			$this->errormsg = 'Aktiv ist ungueltig';
			return false;
		}
		if (!is_bool($this->testtool_sprachwahl))
		{
			$this->errormsg = 'Testtool_sprachwahl ist ungueltig';
			return false;
		}

		$this->errormsg = '';
		return true;
	}

	/**
	 * Laedt Studienplan mit der ID $studienplan_id
	 * @param  $studienplan_id ID des zu ladenden Studienplanes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadStudienplan($studienplan_id)
	{
		//Pruefen ob studienplan_id eine gueltige Zahl ist
		if (!is_numeric($studienplan_id) || $studienplan_id === '')
		{
			$this->errormsg = 'Studienplan_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM lehre.tbl_studienplan "
			."LEFT JOIN addon.tbl_stgv_studienplan USING(studienplan_id) "
			."WHERE studienplan_id=".$this->db_add_param($studienplan_id, FHC_INTEGER, false);

		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if ($row = $this->db_fetch_object())
		{
			$this->studienplan_id = $row->studienplan_id;
			$this->studienordnung_id = $row->studienordnung_id;
			$this->orgform_kurzbz = $row->orgform_kurzbz;
			$this->version = $row->version;
			$this->bezeichnung = $row->bezeichnung;
			$this->regelstudiendauer = $row->regelstudiendauer;
			$this->sprache = $row->sprache;
			$this->aktiv = $this->db_parse_bool($row->aktiv);
			$this->semesterwochen = $row->semesterwochen;
			$this->testtool_sprachwahl = $this->db_parse_bool($row->testtool_sprachwahl);
			$this->ects_stpl = $row->ects_stpl;
			$this->pflicht_lvs = $row->pflicht_lvs;
			$this->pflicht_sws = $row->pflicht_sws;
			$this->onlinebewerbung_studienplan = $this->db_parse_bool($row->onlinebewerbung_studienplan);
			$this->erlaeuterungen = $row->erlaeuterungen;
			$this->sprache_kommentar = $row->sprache_kommentar;
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
			$this->new = false;

			return true;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}
	}

	/**
	 * Laedt die Studienplaene einer Studienordnung und Optional einer Organisationsform
	 *
	 * @param $studienordnung_id ID der Studienordnung
	 * @param $orgform_kurzbz Organisationsform
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function loadStudienplanSTO($studienordnung_id, $orgform_kurzbz = null)
	{
		//Pruefen ob studienordnung_id eine gueltige Zahl ist
		if (!is_numeric($studienordnung_id) || $studienordnung_id === '')
		{
			$this->errormsg = 'Studienordnung_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM lehre.tbl_studienplan "
			."LEFT JOIN addon.tbl_stgv_studienplan USING(studienplan_id) "
			."WHERE studienordnung_id=".$this->db_add_param($studienordnung_id, FHC_INTEGER, false);

		if (!is_null($orgform_kurzbz))
			$qry .= " AND orgform_kurzbz=".$this->db_add_param($orgform_kurzbz);

		if ($this->db_query($qry))
		{
			while ($row = $this->db_fetch_object())
			{
				$obj = new StudienplanAddonStgv();

				$obj->studienplan_id = $row->studienplan_id;
				$obj->studienordnung_id = $row->studienordnung_id;
				$obj->orgform_kurzbz = $row->orgform_kurzbz;
				$obj->version = $row->version;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->regelstudiendauer = $row->regelstudiendauer;
				$obj->sprache = $row->sprache;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->semesterwochen = $row->semesterwochen;
				$obj->testtool_sprachwahl = $this->db_parse_bool($row->testtool_sprachwahl);
				$obj->ects_stpl = $row->ects_stpl;
				$obj->pflicht_lvs = $row->pflicht_lvs;
				$obj->pflicht_sws = $row->pflicht_sws;
				$obj->onlinebewerbung_studienplan = $this->db_parse_bool($row->onlinebewerbung_studienplan);
				$obj->erlaeuterungen = $row->erlaeuterungen;
				$obj->sprache_kommentar = $row->sprache_kommentar;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->new = false;

				$this->result[] = $obj;
			}

			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Laedt alle Studienplaene
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function getAll()
	{
		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM lehre.tbl_studienplan";

		if ($this->db_query($qry))
		{
			while ($row = $this->db_fetch_object())
			{
				$obj = new StudienplanAddonStgv();

				$obj->studienplan_id = $row->studienplan_id;
				$obj->studienordnung_id = $row->studienordnung_id;
				$obj->orgform_kurzbz = $row->orgform_kurzbz;
				$obj->version = $row->version;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->regelstudiendauer = $row->regelstudiendauer;
				$obj->sprache = $row->sprache;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->semesterwochen = $row->semesterwochen;
				$obj->testtool_sprachwahl = $this->db_parse_bool($row->testtool_sprachwahl);
				$obj->ects_stpl = $row->ects_stpl;
				$obj->pflicht_lvs = $row->pflicht_lvs;
				$obj->pflicht_sws = $row->pflicht_sws;
				$obj->onlinebewerbung_studienplan = $this->db_parse_bool($row->onlinebewerbung_studienplan);
				$obj->erlaeuterungen = $row->erlaeuterungen;
				$obj->sprache_kommentar = $row->sprache_kommentar;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->new = false;

				$this->result[] = $obj;
			}

			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
	}

	public function delete($studienplan_id)
	{
		//Pruefen ob studienplan_id eine gueltige Zahl ist
		if (!is_numeric($studienplan_id) || $studienplan_id === '')
		{
			$this->errormsg = 'studienplan_id muss eine gültige Zahl sein'."\n";
			return false;
		}

		//loeschen des Datensatzes
		$qry = "BEGIN;DELETE FROM addon.tbl_stgv_studienplan WHERE studienplan_id=".$this->db_add_param($studienplan_id, FHC_INTEGER, false).";";

		if ($this->db_query($qry))
		{
			if (parent::delete($studienplan_id))
			{
				$this->db_query("COMMIT");
				return true;
			}
			else
			{
				$this->db_query("ROLLBACK");
				$this->errormsg = 'Fehler beim Löschen der Daten'."\n";
				return false;
			}
		}
		else
		{
			$this->db_query("ROLLBACK");
			$this->errormsg = 'Fehler beim Löschen der Daten'."\n";
			return false;
		}
	}
}
