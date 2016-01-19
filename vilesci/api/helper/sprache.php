<?php

require_once('../../../../../config/vilesci.config.inc.php');
require_once('../../../../../include/functions.inc.php');
require_once('../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../include/sprache.class.php');
require_once('../functions.php');

$sprache = new sprache();
if($sprache->getAll(true, true))
{
    $data =  $sprache->result;
}
else
{
    returnAJAX(false, $sprache->errormsg);
}

returnAJAX(true, $data);
?>