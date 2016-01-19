<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/lehrform.class.php');
require_once('../functions.php');

$lehrform = new lehrform();
if($lehrform->getAll(true, true))
{
    $data =  $lehrform->lehrform;
}
else
{
    returnAJAX(false, $lehrform->errormsg);
}

returnAJAX(true, $data);
?>