<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');

require_once('../../../../include/qualifikationsziel.class.php');
require_once('../../../../include/studienordnungAddonStgv.class.php');
require_once('../../functions.php');

$uid = get_uid();
$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($uid);
if(!$berechtigung->isBerechtigt("stgv/editQualifikationsziel",null,"suid"))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Qualifikationsziel anzulegen oder zu editieren.", "detail"=>"stgv/editQualifikationsziel");
    returnAJAX(FALSE, $error);
}

$data = filter_input_array(INPUT_POST, array("data"=> array('flags'=> FILTER_REQUIRE_ARRAY)));
$data = (Object) $data["data"];

$qualifikationsziel = mapDataToQualifikationsziel($data);

$studienordnung = new studienordnungAddonStgv();
$studienordnung->loadStudienordnung($qualifikationsziel->studienordnung_id);

if($studienordnung->status_kurzbz != "development" && !($berechtigung->isBerechtigt("stgv/changeStoAdmin")))
{
    $error = array("message"=>"Sie haben nicht die Berechtigung um Studienordnungen in diesem Status zu ändern.", "detail"=>"stgv/changeStoAdmin");
    returnAJAX(FALSE, $error);
}

if($qualifikationsziel->save())
{
    returnAJAX(true, array($qualifikationsziel->qualifikationsziel_id));
}
else
{
    $error = array("message"=>"Fehler beim Speichern der Tätigkeitsfelder.", "detail"=>$studienordnung->errormsg);
    returnAJAX(false, $error);
}

function mapDataToQualifikationsziel($data)
{
    $q = new qualifikationsziel();
    if($data->qualifikationsziel_id === "")
	$q->new = true;
    else
    {
	$q->new = false;
	$q->load($data->qualifikationsziel_id);
    }
    
    $q->studienordnung_id = $data->studienordnung_id;
    $q->data = $data->data;
    $q->insertvon = get_uid();
    return $q;
}

?>
