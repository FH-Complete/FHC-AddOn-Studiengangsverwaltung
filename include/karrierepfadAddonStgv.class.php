<?php
/*
 * Copyright 2026 fhcomplete.org
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
 */

require_once(dirname(__FILE__).'/../../../include/karrierepfad.class.php');

/**
 * Maintenance of the Karrierepfade of a Studiengang (Betriebsdaten tab)
 */
class karrierepfadAddonStgv extends karrierepfad
{
	public $new = true;
	public $studienplan_ids = array();	// Studienpläne in which the Karrierepfad is selectable
	public $anzahl_prestudenten = 0;	// prestudents with this Karrierepfad

	/**
	 * Loads all Karrierepfade of a Studiengang into $this->result, also the inactive ones
	 * @param integer $studiengang_kz
	 * @return boolean
	 */
	public function getByStudiengang($studiengang_kz)
	{
		if (!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang ist ungueltig';
			return false;
		}

		$sprache = new sprache();
		$qry = "SELECT kp.*, ".$sprache->getSprachQuery('bezeichnung_mehrsprachig').",
					(SELECT array_agg(studienplan_id ORDER BY studienplan_id) FROM public.tbl_karrierepfad_studienplan
						WHERE karrierepfad_id = kp.karrierepfad_id) AS studienplan_ids,
					(SELECT count(*) FROM public.tbl_prestudent
						WHERE karrierepfad_id = kp.karrierepfad_id) AS anzahl_prestudenten
				FROM public.tbl_karrierepfad kp
				WHERE studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER)."
				ORDER BY kp.sort NULLS LAST, kp.karrierepfad_kurzbz";

		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Karrierepfade';
			return false;
		}

		while ($row = $this->db_fetch_object())
		{
			$obj = new karrierepfadAddonStgv();
			$obj->karrierepfad_id = $row->karrierepfad_id;
			$obj->karrierepfad_kurzbz = $row->karrierepfad_kurzbz;
			$obj->karrierepfad_typ_kurzbz = $row->karrierepfad_typ_kurzbz;
			$obj->studiengang_kz = $row->studiengang_kz;
			$obj->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig', $row);
			$obj->sort = $row->sort;
			$obj->aktiv = $this->db_parse_bool($row->aktiv);
			$obj->insertamum = $row->insertamum;
			$obj->insertvon = $row->insertvon;
			$obj->updateamum = $row->updateamum;
			$obj->updatevon = $row->updatevon;
			$obj->studienplan_ids = $row->studienplan_ids == '' ? array() : $this->db_parse_array($row->studienplan_ids);
			$obj->anzahl_prestudenten = (int) $row->anzahl_prestudenten;
			$obj->new = false;
			$this->result[] = $obj;
		}
		return true;
	}

	/**
	 * Loads all Studienpläne of a Studiengang into $this->result (stdClass rows)
	 * @param integer $studiengang_kz
	 * @return boolean
	 */
	public function getStudienplaeneStudiengang($studiengang_kz)
	{
		if (!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang ist ungueltig';
			return false;
		}

		$qry = "SELECT sp.studienplan_id, sp.bezeichnung, sp.orgform_kurzbz, sp.sprache, sp.aktiv
				FROM lehre.tbl_studienplan sp
				JOIN lehre.tbl_studienordnung USING(studienordnung_id)
				WHERE studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER)."
				ORDER BY sp.aktiv DESC, sp.bezeichnung";

		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Studienpläne';
			return false;
		}

		while ($row = $this->db_fetch_object())
		{
			$row->aktiv = $this->db_parse_bool($row->aktiv);
			$this->result[] = $row;
		}
		return true;
	}

	/**
	 * Loads all Karrierepfad types into $this->result (stdClass rows)
	 * @return boolean
	 */
	public function getTypen()
	{
		$sprache = new sprache();
		$qry = "SELECT karrierepfad_typ_kurzbz, ".$sprache->getSprachQuery('bezeichnung_mehrsprachig')."
				FROM public.tbl_karrierepfad_typ
				ORDER BY karrierepfad_typ_kurzbz";

		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Karrierepfad-Typen';
			return false;
		}

		while ($row = $this->db_fetch_object())
		{
			$typ = new stdClass();
			$typ->karrierepfad_typ_kurzbz = $row->karrierepfad_typ_kurzbz;
			$typ->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig', $row);
			$this->result[] = $typ;
		}
		return true;
	}

	/**
	 * Checks the fields before save
	 * @return boolean
	 */
	public function validate()
	{
		// the kurzbz is the value of the URL parameter karrierepfad_kurzbz of the Bewerbungstool
		if (!preg_match('/^[A-Za-z0-9_-]{1,32}$/', $this->karrierepfad_kurzbz))
		{
			$this->errormsg = 'Die Kurzbezeichnung darf nur Buchstaben, Ziffern, "-" und "_" enthalten (maximal 32 Zeichen).';
			return false;
		}
		foreach (array('German', 'English') as $sprache)
		{
			$bezeichnung = isset($this->bezeichnung_mehrsprachig[$sprache]) ? trim($this->bezeichnung_mehrsprachig[$sprache]) : '';
			if ($bezeichnung == '' || mb_strlen($bezeichnung) > 256)
			{
				$this->errormsg = 'Die Bezeichnung ('.$sprache.') fehlt oder ist länger als 256 Zeichen.';
				return false;
			}
		}
		if ($this->karrierepfad_typ_kurzbz == '')
		{
			$this->errormsg = 'Der Typ fehlt.';
			return false;
		}
		if (!is_numeric($this->studiengang_kz))
		{
			$this->errormsg = 'Der Studiengang fehlt.';
			return false;
		}
		if ($this->sort !== '' && !is_null($this->sort) && !ctype_digit((string) $this->sort))
		{
			$this->errormsg = 'Die Sortierung muss eine positive ganze Zahl sein.';
			return false;
		}
		foreach ($this->studienplan_ids as $studienplan_id)
		{
			if (!ctype_digit((string) $studienplan_id))
			{
				$this->errormsg = 'Ungültige Studienplan ID.';
				return false;
			}
		}

		$qry = "SELECT 1 FROM public.tbl_karrierepfad
				WHERE karrierepfad_kurzbz=".$this->db_add_param($this->karrierepfad_kurzbz);
		if (!$this->new)
			$qry .= " AND karrierepfad_id<>".$this->db_add_param($this->karrierepfad_id, FHC_INTEGER);
		if (!$result = $this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Prüfen der Kurzbezeichnung';
			return false;
		}
		if ($this->db_num_rows($result) > 0)
		{
			$this->errormsg = 'Die Kurzbezeichnung '.$this->karrierepfad_kurzbz.' ist bereits vergeben.';
			return false;
		}

		// only Studienpläne of the own Studiengang
		if (count($this->studienplan_ids) > 0)
		{
			$qry = "SELECT count(*) AS anzahl
					FROM lehre.tbl_studienplan
					JOIN lehre.tbl_studienordnung USING(studienordnung_id)
					WHERE studiengang_kz=".$this->db_add_param($this->studiengang_kz, FHC_INTEGER)."
					AND studienplan_id IN (".implode(',', array_map('intval', $this->studienplan_ids)).")";
			if (!$this->db_query($qry) || !($row = $this->db_fetch_object()))
			{
				$this->errormsg = 'Fehler beim Prüfen der Studienpläne';
				return false;
			}
			if ($row->anzahl != count(array_unique($this->studienplan_ids)))
			{
				$this->errormsg = 'Mindestens ein Studienplan gehört nicht zu diesem Studiengang.';
				return false;
			}
		}

		return true;
	}

	/**
	 * Saves the Karrierepfad and replaces its Studienplan links
	 * @return boolean
	 */
	public function save()
	{
		$this->studienplan_ids = array_unique($this->studienplan_ids);

		if (!$this->validate())
			return false;

		if ($this->new)
		{
			$qry = "INSERT INTO public.tbl_karrierepfad (karrierepfad_kurzbz, karrierepfad_typ_kurzbz, studiengang_kz,
						bezeichnung_mehrsprachig, sort, aktiv, insertamum, insertvon) VALUES (".
					$this->db_add_param($this->karrierepfad_kurzbz).", ".
					$this->db_add_param($this->karrierepfad_typ_kurzbz).", ".
					$this->db_add_param($this->studiengang_kz, FHC_INTEGER).", ".
					$this->db_add_param($this->bezeichnung_mehrsprachig, FHC_LANG_ARRAY).", ".
					$this->db_add_param($this->sort, FHC_INTEGER).", ".
					$this->db_add_param($this->aktiv, FHC_BOOLEAN).", ".
					"now(), ".
					$this->db_add_param($this->insertvon).")
					RETURNING karrierepfad_id";
		}
		else
		{
			$qry = "UPDATE public.tbl_karrierepfad SET".
					" karrierepfad_kurzbz=".$this->db_add_param($this->karrierepfad_kurzbz).",".
					" karrierepfad_typ_kurzbz=".$this->db_add_param($this->karrierepfad_typ_kurzbz).",".
					" bezeichnung_mehrsprachig=".$this->db_add_param($this->bezeichnung_mehrsprachig, FHC_LANG_ARRAY).",".
					" sort=".$this->db_add_param($this->sort, FHC_INTEGER).",".
					" aktiv=".$this->db_add_param($this->aktiv, FHC_BOOLEAN).",".
					" updateamum=now(),".
					" updatevon=".$this->db_add_param($this->updatevon).
					" WHERE karrierepfad_id=".$this->db_add_param($this->karrierepfad_id, FHC_INTEGER, false).
					" RETURNING karrierepfad_id";
		}

		$this->db_query('BEGIN');

		if (!$this->db_query($qry) || !($row = $this->db_fetch_object()))
		{
			$this->db_query('ROLLBACK');
			$this->errormsg = 'Fehler beim Speichern des Karrierepfads';
			return false;
		}
		$this->karrierepfad_id = $row->karrierepfad_id;

		$qry = "DELETE FROM public.tbl_karrierepfad_studienplan WHERE karrierepfad_id=".
				$this->db_add_param($this->karrierepfad_id, FHC_INTEGER, false).";";
		foreach ($this->studienplan_ids as $studienplan_id)
		{
			$qry .= "INSERT INTO public.tbl_karrierepfad_studienplan (karrierepfad_id, studienplan_id) VALUES (".
				$this->db_add_param($this->karrierepfad_id, FHC_INTEGER, false).", ".
				$this->db_add_param($studienplan_id, FHC_INTEGER, false).");";
		}

		if (!$this->db_query($qry))
		{
			$this->db_query('ROLLBACK');
			$this->errormsg = 'Fehler beim Speichern der Studienpläne';
			return false;
		}

		$this->db_query('COMMIT');
		$this->new = false;
		return true;
	}

	/**
	 * Deletes a Karrierepfad and its Studienplan links. A Karrierepfad of a prestudent stays: deactivate it instead.
	 * @param integer $karrierepfad_id
	 * @return boolean
	 */
	public function delete($karrierepfad_id)
	{
		if (!is_numeric($karrierepfad_id))
		{
			$this->errormsg = 'Karrierepfad ID ist ungueltig';
			return false;
		}

		$qry = "SELECT count(*) AS anzahl FROM public.tbl_prestudent WHERE karrierepfad_id=".
				$this->db_add_param($karrierepfad_id, FHC_INTEGER);
		if (!$this->db_query($qry) || !($row = $this->db_fetch_object()))
		{
			$this->errormsg = 'Fehler beim Prüfen der Bewerbungen';
			return false;
		}
		if ($row->anzahl > 0)
		{
			$this->errormsg = $row->anzahl.' Bewerbung(en) verwenden diesen Karrierepfad. Bitte deaktivieren Sie ihn stattdessen.';
			return false;
		}

		$qry = "BEGIN;
				DELETE FROM public.tbl_karrierepfad_studienplan WHERE karrierepfad_id=".$this->db_add_param($karrierepfad_id, FHC_INTEGER).";
				DELETE FROM public.tbl_karrierepfad WHERE karrierepfad_id=".$this->db_add_param($karrierepfad_id, FHC_INTEGER).";
				COMMIT;";

		if (!$this->db_query($qry))
		{
			$this->db_query('ROLLBACK');
			$this->errormsg = 'Fehler beim Löschen des Karrierepfads';
			return false;
		}
		return true;
	}
}
