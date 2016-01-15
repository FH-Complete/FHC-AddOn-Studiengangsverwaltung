<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/studienordnung.class.php');
require_once('../../../../../include/akadgrad.class.php');

require_once('../../../vendor/autoload.php');
require_once('../../../include/StudienordnungAddonStgv.class.php');
require_once('../../../include/Taetigkeitsfeld.class.php');
require_once('../functions.php');

$sto_properties = array("bezeichnung","ects","studiengangbezeichnung","studiengangbezeichnung_englisch","studiengangkurzbzlang","begruendung","studiengangsart","orgform_kurzbz","gueltigvon","gueltigbis");

$studienordnung_id_old = filter_input(INPUT_GET, "studienordnung_id_old");
$studienordnung_id_new = filter_input(INPUT_GET, "studienordnung_id_new");

if(is_null($studienordnung_id_old))
{
    returnAJAX(false, "Variable studienordnung_id_old nicht gesetzt");    
}
elseif(is_null($studienordnung_id_new))
{
    returnAJAX(false, "Variable studienordnung_id_new nicht gesetzt");  
}
elseif(($studienordnung_id_old == false) || ($studienordnung_id_new == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$sto = new StudienordnungAddonStgv();
$sto->loadStudienordnung($studienordnung_id_old);

$sto_compare = new StudienordnungAddonStgv();
$sto_compare->loadStudienordnung($studienordnung_id_new);

$diff_old = new stdClass();
$diff_new = new stdClass();

foreach($sto_properties as $property)
{
    $diff_old->$property = $sto->$property;
    $diff_new->$property = $sto_compare->$property;
}
$akadgrad = new akadgrad();
$akadgrad->load($sto->akadgrad_id);
$diff_old->akadgrad_kurzbz = $akadgrad->akadgrad_kurzbz;

$akadgrad->load($sto_compare->akadgrad_id);
$diff_new->akadgrad_kurzbz = $akadgrad->akadgrad_kurzbz;

$properties = array_keys(get_object_vars($diff_old));

$granularity = new cogpowered\FineDiff\Granularity\Word;
$diff = new cogpowered\FineDiff\Diff($granularity);

$diff_array = array();

foreach($properties as $property)
{
    $diff_array[$property]['old'] = $diff_old->$property;
    $diff_array[$property]['new'] = $diff_new->$property;
    $diff_array[$property]['diff'] = $diff->render($diff_old->$property, $diff_new->$property);
}


returnAJAX(true,$diff_array);


//$diff = Diff::compare($sto->bezeichnung, $sto_compare->bezeichnung);
//
//echo Diff::toString($diff);
//echo Diff::toTable($diff);

