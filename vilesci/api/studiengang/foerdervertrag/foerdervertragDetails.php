<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/foerdervertrag.class.php');

require_once('../../functions.php');

$foerdervertrag_id = filter_input(INPUT_GET, "foerdervertrag_id");

if(is_null($foerdervertrag_id))
{
    returnAJAX(false, "Variable foerdervertrag_id nicht gesetzt");    
}
elseif($foerdervertrag_id == false)
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$foerdervertrag = new foerdervertrag($foerdervertrag_id);





$data = array(
    'foerdervertrag_id' => $foerdervertrag->foerdervertrag_id,
    'studiengang_kz' => $foerdervertrag->studiengang_kz,
    'foerdergeber' => $foerdervertrag->foerdergeber,
    'foerdersatz' => $foerdervertrag->foerdersatz,
    'foerdergruppe' => $foerdervertrag->foerdergruppe,
    'gueltigvon' => $foerdervertrag->gueltigvon,
    'gueltigbis' => $foerdervertrag->gueltigbis,
    'erlaeuterungen' => $foerdervertrag->erlaeuterungen,
    'insertamum' => $foerdervertrag->insertamum,
    'insertvon' => $foerdervertrag->insertvon,
    'updateamum' => $foerdervertrag->updateamum,
    'updatevon' => $foerdervertrag->updatevon
);

returnAJAX(true, $data);