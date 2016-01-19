<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/lehrveranstaltung.class.php');
require_once('../../functions.php');

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];

$lehrveranstaltung = mapDataToLehrveranstaltung($data);


if($lehrveranstaltung->save())
{
    returnAJAX(true, array($lehrveranstaltung->lehrveranstaltung_id));
}
else
{
    $error = array("message"=>"Fehler beim Speichern der Lehrveranstaltung.", "detail"=>$lehrveranstaltung->errormsg);
    returnAJAX(false, $error);
}

function mapDataToLehrveranstaltung($data)
{
    $lv = new lehrveranstaltung();
    $lv->new = true;
    $lv->bezeichnung = $data->bezeichnung;
    $lv->studiengang_kz = $data->studiengang_kz;
    $lv->kurzbz = $data->kurzbz;
    $lv->lehrform_kurzbz = $data->lehrform_kurzbz;
    $lv->semester = $data->semester;
    $lv->ects = $data->ects;
    $lv->semesterstunden = $data->semesterstunden;
    $lv->anmerkung = $data->anmerkung;
    $lv->lehre = parseBoolean($data->lehre);
    $lv->lehreverzeichnis = $data->lehreverzeichnis;
    $lv->aktiv = parseBoolean($data->aktiv);
    $lv->insertvon = get_uid();
    $lv->planfaktor = $data->planfaktor;
    $lv->planlektoren = $data->planlektoren;
    $lv->planpersonalkosten = $data->planpersonalkosten;
    $lv->plankostenprolektor = $data->plankostenprolektor;
    $lv->sort = $data->sort;
    $lv->zeugnis = parseBoolean($data->zeugnis);
    $lv->projektarbeit = parseBoolean($data->projektarbeit);
    $lv->sprache = $data->sprache;
    $lv->koordinator = $data->koordinator;
    $lv->bezeichnung_english = $data->bezeichnung_english;
    $lv->orgform_kurzbz = $data->orgform_kurzbz;
    $lv->incoming = $data->incoming;
    $lv->lehrtyp_kurzbz = $data->lehrtyp_kurzbz;
    $lv->oe_kurzbz = $data->oe_kurzbz;
    $lv->raumtyp_kurzbz = $data->raumtyp_kurzbz;
    $lv->anzahlsemester = $data->anzahlsemester;
    $lv->semesterwochen = $data->semesterwochen;
    $lv->lvnr = $data->lvnr; 
    $lv->semester_alternativ = $data->semester_alternativ;
    $lv->farbe = $data->farbe;
    $lv->sws = $data->sws;
    $lv->lvs = $data->lvs;
    $lv->alvs = $data->alvs;
    $lv->lvps = $data->lvps;
    $lv->las = $data->las;
    $lv->benotung = parseBoolean($data->benotung);
    $lv->lvinfo = parseBoolean($data->lvinfo);

    return $lv;
}

?>