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

require_once(dirname(__FILE__).'/../../../include/basis_db.class.php');

class StudienordnungStatus extends basis_db {

    public $status_kurzbz;  //varchar(32)
    public $bezeichnung;    //varchar(256)
    public $reihenfolge;    //integer
    public $result = array();

    /**
     * Konstruktor
     */
    public function __construct() {
	parent::__construct();
    }

    public function getAll() {
	$qry = "SELECT * FROM lehre.tbl_studienordnungstatus ORDER BY reihenfolge;";

	if (!$this->db_query($qry)) {
	    $this->errormsg = 'Fehler bei einer Datenbankabfrage';
	    return false;
	}

	while ($row = $this->db_fetch_object()) {
	    $obj = new StudienordnungStatus();
	    $obj->status_kurzbz = $row->status_kurzbz;
	    $obj->bezeichnung = $row->bezeichnung;
	    $obj->reihenfolge = $row->reihenfolge;
	    array_push($this->result, $obj);
	}
	return true;
    }

}
