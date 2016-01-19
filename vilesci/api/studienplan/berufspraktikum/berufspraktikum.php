<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/akadgrad.class.php');
require_once('../../../../../../include/studiensemester.class.php');
require_once('../../../../include/Berufspraktikum.class.php');
require_once('../../functions.php');

$splid = filter_input(INPUT_GET, "stplid");

if(is_null($splid))
{
    returnAJAX(false, "Variable stplid nicht gesetzt");    
}
elseif(($splid == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$berufspraktikum = new berufspraktikum();
$berufspraktikum->getAll($splid);

$data = $berufspraktikum->result;


returnAJAX(true, $data);
?>