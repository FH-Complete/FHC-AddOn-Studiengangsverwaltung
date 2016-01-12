<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/organisationseinheit.class.php');

require_once('../functions.php');

$oe = new organisationseinheit();
if($oe->getAll(true, true))
{
    $data =  $oe->result;
}
else
{
    returnAJAX(false, $oe->errormsg);
}


returnAJAX(true, $data);
?>