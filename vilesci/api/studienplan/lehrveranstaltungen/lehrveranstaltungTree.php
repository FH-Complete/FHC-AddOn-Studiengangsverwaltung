<?php

require_once('../../../../../../config/vilesci.config.inc.php');
require_once('../../../../../../include/functions.inc.php');
require_once('../../../../../../include/benutzerberechtigung.class.php');
require_once('../../../../../../include/lehrveranstaltung.class.php');

require_once('../../../../include/studienplanAddonStgv.class.php');
require_once('../../functions.php');

$studienplan_id = filter_input(INPUT_GET, "studienplan_id");
$parent_id = filter_input(INPUT_POST, "id");

if(is_null($studienplan_id))
{
    returnAJAX(false, "Variable studienplan_id nicht gesetzt");    
}
elseif(($studienplan_id == false))
{
    returnAJAX(false, "Fehler beim lesen der GET Variablen");    
}

$data = array();

$studienplan = new studienplanAddonStgv();
$studienplan->loadStudienplan($studienplan_id);

$lehrveranstaltung = new lehrveranstaltung();
$lehrveranstaltung->loadLehrveranstaltungStudienplan($studienplan_id);

if($parent_id == null)
{
    for($i=1; $i <= $studienplan->regelstudiendauer; $i++)
    {
        $node = new stdClass();
        $node->id = $i.'_sem';
        $node->bezeichnung = $i.'. Semester';
        $node->type = "sem";
        $node->sem = $i;
        $node->iconCls = "tree-folder";
        $node->state = "open";
        $node->ects = 0;
        foreach($lehrveranstaltung->lehrveranstaltungen as $lv)
        {
            if(($lv->stpllv_semester == $i) && ($lv->studienplan_lehrveranstaltung_id_parent==""))
            {
                $node->state = "closed";
                $node->ects += $lv->ects;
            }
        }
        array_push($data, $node);
    }
}
else
{
    $idParams = explode("_",$parent_id);
    if(isset($idParams[1]) && ($idParams[1] == "sem"))
    {
        foreach($lehrveranstaltung->lehrveranstaltungen as $lv)
        {
            if(($lv->stpllv_semester == $idParams[0]) && ($lv->studienplan_lehrveranstaltung_id_parent==""))
            {
                $lv->parentId = $parent_id;
                $lv->id = $lv->studienplan_lehrveranstaltung_id;
                $lv->type = $lv->lehrtyp_kurzbz;
                if($lehrveranstaltung->hasChildren($lv->studienplan_lehrveranstaltung_id))
                {
                    $lv->state = "closed";
                }
                switch($lv->lehrtyp_kurzbz)
                {
                    case "lv":
                        $lv->iconCls = "icon-lv";
                        break;
                    case "modul":
                        $lv->iconCls = "icon-module";
                        break;
                    case "lf":
                        $lv->iconCls = "icon-lv";
                        break;
                    default:
                        $lv->iconCls = "icon-lv";
                        break;
                }
                array_push($data, $lv);
            }
        }
        usort($data, "cmp_type");
        usort($data, "cmp_name");
    }
    else
    {
        foreach($lehrveranstaltung->lehrveranstaltungen as $lv)
        {
            if($lv->studienplan_lehrveranstaltung_id_parent == $idParams[0])
            {
                $lv->parentId = $lv->studienplan_lehrveranstaltung_id_parent;
                $lv->id = $lv->studienplan_lehrveranstaltung_id;
                $lv->type = $lv->lehrtyp_kurzbz;
                if($lehrveranstaltung->hasChildren($lv->studienplan_lehrveranstaltung_id))
                {
                    $lv->state = "closed";
                }
                switch($lv->lehrtyp_kurzbz)
                {
                    case "lv":
                        $lv->iconCls = "icon-lv";
                        break;
                    case "modul":
                        $lv->iconCls = "icon-module";
                        break;
                    case "lf":
                        $lv->iconCls = "icon-lv";
                        break;
                    default:
                        $lv->iconCls = "icon-lv";
                        break;
                }
                array_push($data, $lv);
            }
        }
        usort($data, "cmp_type");
        usort($data, "cmp_name");
    }
}

returnAJAX(true, $data);

function cmp_name($a, $b)
{
    if ($a->bezeichnung == $b->bezeichnung) {
        return 0;
    }
    return ($a->bezeichnung < $b->bezeichnung) ? -1 : 1;
}

function cmp_type($a, $b)
{
    if ($a->type == $b->type) {
        return 0;
    }
    return ($a->type < $b->type) ? -1 : 1;
}

?>