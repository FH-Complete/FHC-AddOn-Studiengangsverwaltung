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
require_once(dirname(__FILE__) . '/../../../include/lehrform.class.php');

class lehrformAddonStgv extends lehrform
{

    /**
     * Konstruktor
     */
    public function __construct()
    {
	parent::__construct();
    }

    public function getByLehrtyp($lehrtyp_kurzbz)
    {
	$sprache = new sprache();
	$qry = 'SELECT l2.* FROM addon.tbl_stgv_lehrtyp_lehrform l1'
		. ' JOIN lehre.tbl_lehrform l2 USING(lehrform_kurzbz)'
		. ' WHERE l1.lehrtyp_kurzbz=' . $this->db_add_param($lehrtyp_kurzbz);

	if (!$this->db_query($qry))
	{
	    $this->errormsg = 'Fehler beim Lesen der Lehrform';
	    return false;
	}

	while ($row = $this->db_fetch_object())
	{
	    $lf = new lehrform();

	    $lf->lehrform_kurzbz = $row->lehrform_kurzbz;
	    $lf->bezeichnung = $row->bezeichnung;
	    $lf->verplanen = $this->db_parse_bool($row->verplanen);
	    $lf->bezeichnung_kurz = $sprache->parseSprachResult('bezeichnung_kurz', $row);
	    $lf->bezeichnung_lang = $sprache->parseSprachResult('bezeichnung_lang', $row);

	    $this->lehrform[] = $lf;
	}

	return true;
    }

}
