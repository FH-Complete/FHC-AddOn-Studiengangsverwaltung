<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/studienordnung.class.php');
require_once('../../../../../include/akadgrad.class.php');

require_once('../../../vendor/autoload.php');
require_once('../../../include/studienordnungAddonStgv.class.php');
require_once('../../../include/studienplanAddonStgv.class.php');
require_once('../../../include/taetigkeitsfeld.class.php');
require_once('../../../include/qualifikationsziel.class.php');
require_once('../../../include/zugangsvoraussetzung.class.php');
require_once('../../../include/aufnahmeverfahren.class.php');
require_once('../../../include/auslandssemester.class.php');

require_once('../functions.php');

$sto_properties = array("bezeichnung", "ects", "studiengangbezeichnung", "studiengangbezeichnung_englisch", "studiengangkurzbzlang", "begruendung", "orgform_kurzbz", "gueltigvon", "gueltigbis");

$studienordnung_id_old = filter_input(INPUT_GET, "studienordnung_id_old");
$studienordnung_id_new = filter_input(INPUT_GET, "studienordnung_id_new");
$studienplan_id_old = filter_input(INPUT_GET, "studienplan_id_old");
$studienplan_id_new = filter_input(INPUT_GET, "studienplan_id_new");

if (is_null($studienordnung_id_old))
{
    returnAJAX(false, "Variable studienordnung_id_old nicht gesetzt");
} elseif (is_null($studienordnung_id_new))
{
    returnAJAX(false, "Variable studienordnung_id_new nicht gesetzt");
} elseif (($studienordnung_id_old == false) || ($studienordnung_id_new == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");
}

$sto_old = new StudienordnungAddonStgv();
$sto_old->loadStudienordnung($studienordnung_id_old);

$sto_new = new StudienordnungAddonStgv();
$sto_new->loadStudienordnung($studienordnung_id_new);

$akadgrad = new akadgrad();
$akadgrad->load($sto_old->akadgrad_id);
$sto_old->akadgrad_kurzbz = $akadgrad->akadgrad_kurzbz;

$akadgrad->load($sto_new->akadgrad_id);
$sto_new->akadgrad_kurzbz = $akadgrad->akadgrad_kurzbz;


$granularity = new cogpowered\FineDiff\Granularity\Word;
$diff = new cogpowered\FineDiff\Diff($granularity);

$diff_array = array();

$diff_array["Metadaten/Eckdaten"]["Bezeichnung"]['old'] = $sto_old->bezeichnung;
$diff_array["Metadaten/Eckdaten"]["Bezeichnung"]['new'] = $sto_new->bezeichnung;
$diff_array["Metadaten/Eckdaten"]["Bezeichnung"]['diff'] = $diff->render($sto_old->bezeichnung, $sto_new->bezeichnung);

$diff_array["Metadaten/Eckdaten"]["ECTS"]['old'] = $sto_old->ects;
$diff_array["Metadaten/Eckdaten"]["ECTS"]['new'] = $sto_new->ects;
$diff_array["Metadaten/Eckdaten"]["ECTS"]['diff'] = $diff->render($sto_old->ects, $sto_new->ects);

$diff_array["Metadaten/Eckdaten"]["Studiengangbezeichnung"]['old'] = $sto_old->studiengangbezeichnung;
$diff_array["Metadaten/Eckdaten"]["Studiengangbezeichnung"]['new'] = $sto_new->studiengangbezeichnung;
$diff_array["Metadaten/Eckdaten"]["Studiengangbezeichnung"]['diff'] = $diff->render($sto_old->studiengangbezeichnung, $sto_new->studiengangbezeichnung);

$diff_array["Metadaten/Eckdaten"]["Studiengangbezeichnung Englisch"]['old'] = $sto_old->studiengangbezeichnung_englisch;
$diff_array["Metadaten/Eckdaten"]["Studiengangbezeichnung Englisch"]['new'] = $sto_new->studiengangbezeichnung_englisch;
$diff_array["Metadaten/Eckdaten"]["Studiengangbezeichnung Englisch"]['diff'] = $diff->render($sto_old->studiengangbezeichnung_englisch, $sto_new->studiengangbezeichnung_englisch);

$diff_array["Metadaten/Eckdaten"]["Studiengang Kurzbezeichnung"]['old'] = $sto_old->studiengangkurzbzlang;
$diff_array["Metadaten/Eckdaten"]["Studiengang Kurzbezeichnung"]['new'] = $sto_new->studiengangkurzbzlang;
$diff_array["Metadaten/Eckdaten"]["Studiengang Kurzbezeichnung"]['diff'] = $diff->render($sto_old->studiengangkurzbzlang, $sto_new->studiengangkurzbzlang);

$diff_array["Metadaten/Eckdaten"]["Begründung"]['old'] = $sto_old->begruendung;
$diff_array["Metadaten/Eckdaten"]["Begründung"]['new'] = $sto_new->begruendung;
$diff_array["Metadaten/Eckdaten"]["Begründung"]['diff'] = $diff->render($sto_old->begruendung, $sto_new->begruendung);

$diff_array["Metadaten/Eckdaten"]["Orgform"]['old'] = $sto_old->orgform_kurzbz;
$diff_array["Metadaten/Eckdaten"]["Orgform"]['new'] = $sto_new->orgform_kurzbz;
$diff_array["Metadaten/Eckdaten"]["Orgform"]['diff'] = $diff->render($sto_old->orgform_kurzbz, $sto_new->orgform_kurzbz);

$diff_array["Metadaten/Eckdaten"]["Gültig von"]['old'] = $sto_old->gueltigvon;
$diff_array["Metadaten/Eckdaten"]["Gültig von"]['new'] = $sto_new->gueltigvon;
$diff_array["Metadaten/Eckdaten"]["Gültig von"]['diff'] = $diff->render($sto_old->gueltigvon, $sto_new->gueltigvon);

$diff_array["Metadaten/Eckdaten"]["Gültig bis"]['old'] = $sto_old->gueltigbis;
$diff_array["Metadaten/Eckdaten"]["Gültig bis"]['new'] = $sto_new->gueltigbis;
$diff_array["Metadaten/Eckdaten"]["Gültig bis"]['diff'] = $diff->render($sto_old->gueltigbis, $sto_new->gueltigbis);

$diff_array["Metadaten/Eckdaten"]["Akademischer Grad"]['old'] = $sto_old->akadgrad_kurzbz;
$diff_array["Metadaten/Eckdaten"]["Akademischer Grad"]['new'] = $sto_new->akadgrad_kurzbz;
$diff_array["Metadaten/Eckdaten"]["Akademischer Grad"]['diff'] = $diff->render($sto_old->akadgrad_kurzbz, $sto_new->akadgrad_kurzbz);

//Abschnitt für Tätigkeitsfelder
$taetigkeitsfeld_old = new taetigkeitsfeld();
$taetigkeitsfeld_old->getAll($studienordnung_id_old);

$taetigkeitsfeld_new = new taetigkeitsfeld();
$taetigkeitsfeld_new->getAll($studienordnung_id_new);

if((!empty($taetigkeitsfeld_old->result)) &&(!empty($taetigkeitsfeld_new->result)))
{
    $diff_array["Tätigkeitsfelder"]["Überblick"]["old"] = $taetigkeitsfeld_old->result[0]->ueberblick;
    $diff_array["Tätigkeitsfelder"]["Überblick"]["new"] = $taetigkeitsfeld_new->result[0]->ueberblick;
    $diff_array["Tätigkeitsfelder"]["Überblick"]["diff"] = $diff->render($taetigkeitsfeld_old->result[0]->ueberblick, $taetigkeitsfeld_new->result[0]->ueberblick);

    $diff_array["Tätigkeitsfelder"]["Aufgaben"]["old"] = $taetigkeitsfeld_old->result[0]->data->aufgaben->fixed;
    $diff_array["Tätigkeitsfelder"]["Aufgaben"]["new"] = $taetigkeitsfeld_new->result[0]->data->aufgaben->fixed;
    $diff_array["Tätigkeitsfelder"]["Aufgaben"]["diff"] = $diff->render($taetigkeitsfeld_old->result[0]->data->aufgaben->fixed, $taetigkeitsfeld_new->result[0]->data->aufgaben->fixed);

    $diff_array["Tätigkeitsfelder"]["Aufgaben Text"]["old"] = "";
    $diff_array["Tätigkeitsfelder"]["Aufgaben Text"]["new"] = "";
    $diff_array["Tätigkeitsfelder"]["Aufgaben Text"]["diff"] = "";
    

    if (count($taetigkeitsfeld_old->result[0]->data->aufgaben->elements) < count($taetigkeitsfeld_new->result[0]->data->aufgaben->elements))
    {
//	foreach ($taetigkeitsfeld_new->result[0]->data->aufgaben->elements as $key => $ele_new)
//	{
//	    $ele_old = "";
//	    $diff_array["Tätigkeitsfelder"]["Aufgaben Text"]["new"] .= "<br>" . $ele_new;
//	    if (array_key_exists($key, $taetigkeitsfeld_old->result[0]->data->aufgaben->elements))
//	    {
//		$ele_old = $taetigkeitsfeld_old->result[0]->data->aufgaben->elements[$key];
//	    }
//	    $diff_array["Tätigkeitsfelder"]["Aufgaben Text"]["old"] .= "<br>" . $ele_old;
//	    $diff_array["Tätigkeitsfelder"]["Aufgaben Text"]["diff"] .= "<br>" . $diff->render($ele_old, $ele_new);
//	}
    } else
    {
//	foreach ($taetigkeitsfeld_old->result[0]->data->aufgaben->elements as $key => $ele_old)
//	{
//	    $ele_new = "";
//	    $diff_array["Tätigkeitsfelder"]["Aufgaben Text"]["old"] .= "<br>" . $ele_old;
//	    if (array_key_exists($key, $taetigkeitsfeld_new->result[0]->data->aufgaben->elements))
//	    {
//		$ele_new = $taetigkeitsfeld_new->result[0]->data->aufgaben->elements[$key];
//	    }
//	    $diff_array["Tätigkeitsfelder"]["Aufgaben Text"]["new"] .= "<br>" . $ele_new;
//	    $diff_array["Tätigkeitsfelder"]["Aufgaben Text"]["diff"] .= "<br>" . $diff->render($ele_old, $ele_new);
//	}
    }

    $diff_array["Tätigkeitsfelder"]["Branchen"]["old"] = $taetigkeitsfeld_old->result[0]->data->branchen->fixed;
    $diff_array["Tätigkeitsfelder"]["Branchen"]["new"] = $taetigkeitsfeld_new->result[0]->data->branchen->fixed;
    $diff_array["Tätigkeitsfelder"]["Branchen"]["diff"] = $diff->render($taetigkeitsfeld_old->result[0]->data->branchen->fixed, $taetigkeitsfeld_new->result[0]->data->branchen->fixed);

    $diff_array["Tätigkeitsfelder"]["Branchen Text"]["old"] = "";
    $diff_array["Tätigkeitsfelder"]["Branchen Text"]["new"] = "";
    $diff_array["Tätigkeitsfelder"]["Branchen Text"]["diff"] = "";

    if (count($taetigkeitsfeld_old->result[0]->data->branchen->elements) < count($taetigkeitsfeld_new->result[0]->data->branchen->elements))
    {
	foreach ($taetigkeitsfeld_new->result[0]->data->branchen->elements as $key => $ele_new)
	{
//	    $ele_old = "";
//	    $diff_array["Tätigkeitsfelder"]["Branchen Text"]["new"] .= "<br>" . $ele_new;
//	    if (array_key_exists($key, $taetigkeitsfeld_old->result[0]->data->branchen->elements))
//	    {
//		$ele_old = $taetigkeitsfeld_old->result[0]->data->branchen->elements[$key];
//	    }
//	    $diff_array["Tätigkeitsfelder"]["Branchen Text"]["old"] .= "<br>" . $ele_old;
//	    $diff_array["Tätigkeitsfelder"]["Branchen Text"]["diff"] .= "<br>" . $diff->render($ele_old, $ele_new);
	}
    } else
    {
	foreach ($taetigkeitsfeld_old->result[0]->data->branchen->elements as $key => $ele_old)
	{
//	    $ele_new = "";
//	    $diff_array["Tätigkeitsfelder"]["Branchen Text"]["old"] .= "<br>" . $ele_old;
//	    if (array_key_exists($key, $taetigkeitsfeld_new->result[0]->data->branchen->elements))
//	    {
//		$ele_new = $taetigkeitsfeld_new->result[0]->data->branchen->elements[$key];
//	    }
//	    $diff_array["Tätigkeitsfelder"]["Branchen Text"]["new"] .= "<br>" . $ele_new;
//	    $diff_array["Tätigkeitsfelder"]["Branchen Text"]["diff"] .= "<br>" . $diff->render($ele_old, $ele_new);
	}
    }

    $diff_array["Tätigkeitsfelder"]["Positionen"]["old"] = $taetigkeitsfeld_old->result[0]->data->positionen->fixed;
    $diff_array["Tätigkeitsfelder"]["Positionen"]["new"] = $taetigkeitsfeld_new->result[0]->data->positionen->fixed;
    $diff_array["Tätigkeitsfelder"]["Positionen"]["diff"] = $diff->render($taetigkeitsfeld_old->result[0]->data->positionen->fixed, $taetigkeitsfeld_new->result[0]->data->positionen->fixed);

    $diff_array["Tätigkeitsfelder"]["Positionen Text"]["old"] = "";
    $diff_array["Tätigkeitsfelder"]["Positionen Text"]["new"] = "";
    $diff_array["Tätigkeitsfelder"]["Positionen Text"]["diff"] = "";

    if (count($taetigkeitsfeld_old->result[0]->data->positionen->elements) < count($taetigkeitsfeld_new->result[0]->data->positionen->elements))
    {
	foreach ($taetigkeitsfeld_new->result[0]->data->positionen->elements as $key => $ele_new)
	{
//	    $ele_old = "";
//	    $diff_array["Tätigkeitsfelder"]["Positionen Text"]["new"] .= "<br>" . $ele_new;
//	    if (array_key_exists($key, $taetigkeitsfeld_old->result[0]->data->positionen->elements))
//	    {
//		$ele_old = $taetigkeitsfeld_old->result[0]->data->positionen->elements[$key];
//	    }
//	    $diff_array["Tätigkeitsfelder"]["Positionen Text"]["old"] .= "<br>" . $ele_old;
//	    $diff_array["Tätigkeitsfelder"]["Positionen Text"]["diff"] .= "<br>" . $diff->render($ele_old, $ele_new);
	}
    } else
    {
	foreach ($taetigkeitsfeld_old->result[0]->data->positionen->elements as $key => $ele_old)
	{
//	    $ele_new = "";
//	    $diff_array["Tätigkeitsfelder"]["Positionen Text"]["old"] .= "<br>" . $ele_old;
//	    if (array_key_exists($key, $taetigkeitsfeld_new->result[0]->data->positionen->elements))
//	    {
//		$ele_new = $taetigkeitsfeld_new->result[0]->data->positionen->elements[$key];
//	    }
//	    $diff_array["Tätigkeitsfelder"]["Positionen Text"]["new"] .= "<br>" . $ele_new;
//	    $diff_array["Tätigkeitsfelder"]["Positionen Text"]["diff"] .= "<br>" . $diff->render($ele_old, $ele_new);
	}
    }
}
//Abschnitt für Qualifikationsziele
$qualifikationsziel_old = new qualifikationsziel();
$qualifikationsziel_old->getAll($studienordnung_id_old);

$qualifikationsziel_new = new qualifikationsziel();
$qualifikationsziel_new->getAll($studienordnung_id_new);

if((!empty($qualifikationsziel_old->result)) &&(!empty($qualifikationsziel_new->result)))
{
    foreach ($qualifikationsziel_old->result[0]->data as $key => $ele)
    {

	if (!isset($diff_array["Qualifikationsziele"][$ele->header]))
	{
	    $diff_array["Qualifikationsziele"][$ele->header]["old"] = "";
	    $diff_array["Qualifikationsziele"][$ele->header]["new"] = "";
	    $diff_array["Qualifikationsziele"][$ele->header]["diff"] = "";
	}
	foreach ($ele->fixed as $k => $fixed)
	{
	    $old = $fixed;
	    $new = $qualifikationsziel_new->result[0]->data[$key]->fixed[$k];
	    $diff_array["Qualifikationsziele"][$ele->header]["old"] .= $old . "<br>" . "<br>";
	    $diff_array["Qualifikationsziele"][$ele->header]["new"] .= $new . "<br>" . "<br>";
	    $diff_array["Qualifikationsziele"][$ele->header]["diff"] .= $diff->render($old, $new) . "<br>" . "<br>";

	    if (isset($ele->elements))
	    {
    //	    if (count($taetigkeitsfeld_old->result[0]->data->positionen->elements) < count($taetigkeitsfeld_new->result[0]->data->positionen->elements))
		$old = $ele->elements[$k];
		$new = $qualifikationsziel_new->result[0]->data[$key]->elements[$k];

		if ($new !== null && $old != null)
		{
		    if (count($old) > count($new))
		    {
			foreach ($old as $i => $o)
			{
			    $diff_array["Qualifikationsziele"][$ele->header]["old"] .= $o . "<br>" . "<br>";
			    $n = "";
			    if (array_key_exists($i, $new))
			    {
				$n = $diff_array["Qualifikationsziele"][$ele->header]["new"] .= $new[$i];
			    }
			    $diff_array["Qualifikationsziele"][$ele->header]["new"] .= $n . "<br>" . "<br>";
			    $diff_array["Qualifikationsziele"][$ele->header]["diff"] .= $diff->render($o, $new[$i]) . "<br>" . "<br>";
			}
		    } else
		    {
			foreach ($new as $i => $n)
			{
			    $o = "";
			    if (array_key_exists($i, $old))
			    {
				$o = $old[$i];
			    }
			    $diff_array["Qualifikationsziele"][$ele->header]["old"] .= $o . "<br>" . "<br>";
			    $diff_array["Qualifikationsziele"][$ele->header]["new"] .= $n . "<br>" . "<br>";
			    $diff_array["Qualifikationsziele"][$ele->header]["diff"] .= $diff->render($o, $new[$i]) . "<br>" . "<br>";
			}
		    }
		}
	    }
	}
    }
}

//Abschnitt für Zugangsvoraussetzungen
$zugangsvoraussetzung_old = new zugangsvoraussetzung();
$zugangsvoraussetzung_old->getAll($studienordnung_id_old);

$zugangsvoraussetzung_new = new zugangsvoraussetzung();
$zugangsvoraussetzung_new->getAll($studienordnung_id_new);

if((!empty($zugangsvoraussetzung_old->result)) &&(!empty($zugangsvoraussetzung_new->result)))
{
    $diff_array["Zugangsvoraussetzungen"]["Text"]["old"] = $zugangsvoraussetzung_old->result[0]->data;
    $diff_array["Zugangsvoraussetzungen"]["Text"]["new"] = $zugangsvoraussetzung_new->result[0]->data;
    $diff_array["Zugangsvoraussetzungen"]["Text"]["diff"] = $diff->render($zugangsvoraussetzung_old->result[0]->data, $zugangsvoraussetzung_new->result[0]->data);
}

//Abschnitt für Aufnahmeverfahren
$aufnahmeverfahren_old = new aufnahmeverfahren();
$aufnahmeverfahren_old->getAll($studienordnung_id_old);

$aufnahmeverfahren_new = new aufnahmeverfahren();
$aufnahmeverfahren_new->getAll($studienordnung_id_new);

if((!empty($aufnahmeverfahren_old->result)) &&(!empty($aufnahmeverfahren_new->result)))
{
    $diff_array["Aufnahmeverfahren"]["Text"]["old"] = $aufnahmeverfahren_old->result[0]->data;
    $diff_array["Aufnahmeverfahren"]["Text"]["new"] = $aufnahmeverfahren_new->result[0]->data;
    $diff_array["Aufnahmeverfahren"]["Text"]["diff"] = $diff->render($aufnahmeverfahren_old->result[0]->data, $aufnahmeverfahren_new->result[0]->data);
}

if(($studienplan_id_old !== 'undefined') && ($studienplan_id_new !== 'undefined'))
{
    $stpl_old = new StudienplanAddonStgv();
    $stpl_old->loadStudienplan($studienplan_id_old);
    $stpl_new = new StudienplanAddonStgv();
    $stpl_new->loadStudienplan($studienplan_id_new);
    
//    var_dump($stpl_old);
//    var_dump($stpl_new);
    
    $diff_array["Studienpläne"]["Version"]["old"] = $stpl_old->version;
    $diff_array["Studienpläne"]["Version"]["new"] = $stpl_new->version;
    $diff_array["Studienpläne"]["Version"]["diff"] = $diff->render($stpl_old->version, $stpl_new->version);
    
    $diff_array["Studienpläne"]["Organisationsform"]["old"] = $stpl_old->orgform_kurzbz;
    $diff_array["Studienpläne"]["Organisationsform"]["new"] = $stpl_new->orgform_kurzbz;
    $diff_array["Studienpläne"]["Organisationsform"]["diff"] = $diff->render($stpl_old->orgform_kurzbz, $stpl_new->orgform_kurzbz);
    
    $diff_array["Studienpläne"]["Regelstudiendauer"]["old"] = $stpl_old->regelstudiendauer;
    $diff_array["Studienpläne"]["Regelstudiendauer"]["new"] = $stpl_new->regelstudiendauer;
    $diff_array["Studienpläne"]["Regelstudiendauer"]["diff"] = $diff->render($stpl_old->regelstudiendauer, $stpl_new->regelstudiendauer);
    
    $diff_array["Studienpläne"]["ECTS"]["old"] = $stpl_old->ects_stpl;
    $diff_array["Studienpläne"]["ECTS"]["new"] = $stpl_new->ects_stpl;
    $diff_array["Studienpläne"]["ECTS"]["diff"] = $diff->render($stpl_old->ects_stpl, $stpl_new->ects_stpl);
    
    $diff_array["Studienpläne"]["Pflicht SWS"]["old"] = $stpl_old->pflicht_sws;
    $diff_array["Studienpläne"]["Pflicht SWS"]["new"] = $stpl_new->pflicht_sws;
    $diff_array["Studienpläne"]["Pflicht SWS"]["diff"] = $diff->render($stpl_old->pflicht_sws, $stpl_new->pflicht_sws);
    
    $diff_array["Studienpläne"]["Pflicht LVS"]["old"] = $stpl_old->pflicht_lvs;
    $diff_array["Studienpläne"]["Pflicht LVS"]["new"] = $stpl_new->pflicht_lvs;
    $diff_array["Studienpläne"]["Pflicht LVS"]["diff"] = $diff->render($stpl_old->pflicht_lvs, $stpl_new->pflicht_lvs);
    
    $diff_array["Studienpläne"]["Sprache"]["old"] = $stpl_old->sprache;
    $diff_array["Studienpläne"]["Sprache"]["new"] = $stpl_new->sprache;
    $diff_array["Studienpläne"]["Sprache"]["diff"] = $diff->render($stpl_old->sprache, $stpl_new->sprache);
    
    $diff_array["Studienpläne"]["Erläuterungen"]["old"] = $stpl_old->erlaeuterungen;
    $diff_array["Studienpläne"]["Erläuterungen"]["new"] = $stpl_new->erlaeuterungen;
    $diff_array["Studienpläne"]["Erläuterungen"]["diff"] = $diff->render($stpl_old->erlaeuterungen, $stpl_new->erlaeuterungen);
    
    $auslandssemester_old = new auslandssemester();
    $auslandssemester_old->getAll($stpl_old->studienplan_id);
    
    $auslandssemester_new = new auslandssemester();
    $auslandssemester_new->getAll($stpl_new->studienplan_id);
    
    var_dump($auslandssemester_old->result);
    var_dump($auslandssemester_new->result);
    
}

returnAJAX(true, $diff_array);

