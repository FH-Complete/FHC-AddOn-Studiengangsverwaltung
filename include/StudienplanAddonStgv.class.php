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

require_once(dirname(__FILE__) . '/../../../include/studienplan.class.php');

class StudienplanAddonStgv extends studienplan
{

    /**
     * Konstruktor
     */
    public function __construct()
    {
	parent::__construct();
    }

    /**
     * speichert die Semesterzuordnung für die Studieordnung
     * @param int $$studienplan_id Die ID des Studienplans
     * @param string $studiensemester_kurzbz Kurzbezeichnung des Studiensemesters
     * @param int $ausbildungssemester Ausbildungssemester als Zahl
     */
    public function saveSemesterZuordnung($zuordnung = array())
    {

	if (is_array($zuordnung))
	{
	    $qry = "";
	    foreach ($zuordnung as $key)
	    {
		if (!is_numeric($key["studienplan_id"]))
		{
		    $this->errormsg = 'studienplan_id muss eine gueltige Zahl sein';
		    return false;
		}

		if (!is_string($key["studiensemester_kurzbz"]) || strlen($key["studiensemester_kurzbz"]) != 6)
		{
		    $this->errormsg = 'studiensemester_kurzbz muss ein String mit 6 Zeichen sein';
		    return false;
		}

		if (!is_numeric($key["ausbildungssemester"]))
		{
		    $this->errormsg = 'ausbildungssemester muss eine gueltige Zahl sein';
		    return false;
		}


		$qry .= "INSERT INTO addon.tbl_stgv_studienplan_semester (studienplan_id, studiensemester_kurzbz, semester) VALUES (" .
			$this->db_add_param($key["studienplan_id"]) . ', ' .
			$this->db_add_param($key["studiensemester_kurzbz"]) . ', ' .
			$this->db_add_param($key["ausbildungssemester"]) . '); ';
	    }

	    if (!$this->db_query($qry))
	    {
		$this->errormsg = 'Fehler beim Speichern des Datensatzes';
		return false;
	    }
	    return true;
	} else
	{
	    //TODO $zuordnung has to be array
	    return false;
	}
	return false;
    }

    /**
     * lädt alle zugeordneten Semester eines Studienplans
     * @param int $studienplan ID
     */
    public function loadStudiensemesterFromStudienordnung($studienplan_id)
    {
	if (!is_numeric($studienplan_id))
	{
	    $this->errormsg = 'studienplan_id muss eine gueltige Zahl sein';
	    return false;
	}

	$qry = 'SELECT DISTINCT studiensemester_kurzbz, tbl_studiensemester.start
				FROM 
					addon.tbl_stgv_studienplan_semester 
					JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
				WHERE studienplan_id=' . $this->db_add_param($studienplan_id) . '
				ORDER BY tbl_studiensemester.start, studiensemester_kurzbz';

	if (!$this->db_query($qry))
	{
	    $this->errormsg = 'Fehler bei einer Datenbankabfrage';
	    return false;
	}

	$data = array();
	while ($row = $this->db_fetch_object())
	{
	    $obj = new stdClass();
	    $data[] = $row->studiensemester_kurzbz;
	}
	return $data;
    }

    public function loadAusbildungsemesterFromStudiensemester($studienplan_id, $studiensemester_kurzbz)
    {
	$qry = 'SELECT semester 
					FROM addon.tbl_stgv_studienplan_semester
					WHERE studienplan_id=' . $this->db_add_param($studienplan_id) . ' AND 
						studiensemester_kurzbz=' . $this->db_add_param($studiensemester_kurzbz) . ' 
					ORDER BY semester;';

	if (!$this->db_query($qry))
	{
	    return false;
	}

	$data = array();
	while ($row = $this->db_fetch_object())
	{
	    $data[] = $row->semester;
	}
	return $data;
    }

    function isSemesterZugeordnet($studienplan_id, $studiensemester_kurzbz, $ausbildungssemester)
    {
	if (!is_numeric($studienplan_id))
	{
	    $this->errormsg = 'studienplan_id muss eine gueltige Zahl sein';
	    return false;
	}

	if (!is_string($studiensemester_kurzbz) || strlen($studiensemester_kurzbz) != 6)
	{
	    $this->errormsg = 'studiensemester_kurzbz muss ein String mit 6 Zeichen sein';
	    return false;
	}

	if (!is_numeric($ausbildungssemester))
	{
	    $this->errormsg = 'ausbildungssemester muss eine gueltige Zahl sein';
	    return false;
	}

	$qry = 'SELECT * FROM addon.tbl_stgv_studienplan_semester WHERE 
			studienplan_id=' . $this->db_add_param($studienplan_id) . ' AND 
			studiensemester_kurzbz=' . $this->db_add_param($studiensemester_kurzbz) . ' AND 
			semester=' . $this->db_add_param($ausbildungssemester) . ';';

	if ($this->db_query($qry))
	{
	    if ($this->db_num_rows() == 1)
	    {
		return true;
	    }
	    if ($this->db_num_rows() == 0)
	    {
		return false;
	    }
	    return false;
	}
	return false;
    }

    public function deleteSemesterZuordnung($studienplan_id, $studiensemester_kurzbz, $ausbildungssemester = NULL)
    {
	if (!is_numeric($studienplan_id))
	{
	    $this->errormsg = 'studienplan_id muss eine gueltige Zahl sein';
	    return false;
	}

	if (!is_string($studiensemester_kurzbz) || strlen($studiensemester_kurzbz) != 6)
	{
	    $this->errormsg = 'studiensemester_kurzbz muss ein String mit 6 Zeichen sein';
	    return false;
	}

	$qry = 'DELETE FROM addon.tbl_stgv_studienplan_semester 
					WHERE studienplan_id=' . $this->db_add_param($studienplan_id) . ' AND 
						studiensemester_kurzbz=' . $this->db_add_param($studiensemester_kurzbz) . '';
	if ($ausbildungssemester !== null)
	    $qry.=' AND semester=' . $this->db_add_param($ausbildungssemester) . '';

	$qry.=';';

	if ($this->db_query($qry))
	{
	    return true;
	} else
	{
	    $this->errormsg = 'Fehler beim Löschen der Zuordnung' . "\n";
	    return false;
	}
    }

}
