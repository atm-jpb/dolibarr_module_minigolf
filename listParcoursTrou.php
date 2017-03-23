<?php

require 'config.php';
dol_include_once('/minigolf/class/minigolf.class.php');
dol_include_once('/minigolf/lib/minigolf.lib.php');

if(empty($user->rights->minigolf->read)) accessforbidden();

$langs->load('abricot@abricot');
$langs->load('minigolf@minigolf');

$PDOdb = new TPDOdb;

$object = new TParcoursTrou();

$parcoursId = GETPOST('parcoursId');
$action = GETPOST('action');

if (empty($action) ) $action = $mode = 'view';


$hookmanager->initHooks(array('mymodulelist'));





/*
 * Actions
 */

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	// do action from GETPOST ...

    // Code go here


    switch($action){

        case 'save' :

            $parcoursId = GETPOST('fk_parcours');

            $object->set_values($_POST); // Set standard attributes

            var_dump($object);exit;

            $object->save($PDOdb, empty($object->ref)); // ref ?

            header('Location: '.dol_buildpath('/minigolf/listParcoursTrou.php', 1)."?action=edit&parcoursId=$parcoursId"  ); //.'?id= .$object->getId());
            exit;

            break;

        case 'delete' :

            $rowid = GETPOST('rowid');

            $object->load($PDOdb, $rowid);

            $object->to_delete = true;

            $object->save($PDOdb);
            
            header('Location: '.dol_buildpath('/minigolf/listParcoursTrou.php', 1)."?action=edit&parcoursId=$object->fk_parcours"  ); //.'?id= .$object->getId());
            exit;

            break;

    }



}


/*
 * View
 */



llxHeader('',$langs->trans('Trou du parcours') . _getParcoursNameFromId($parcoursId)  ,'','');

//$type = GETPOST('type');
//if (empty($user->rights->mymodule->all->read)) $type = 'mine';

// TODO ajouter les champs de son objet que l'on souhaite afficher
$sql = 'SELECT fk_trou, ordre, rowid as dellink' ; //, t.date_cre, t.date_maj, \'\' AS action';

$sql.= ' FROM '.MAIN_DB_PREFIX.'minigolf_parcours_trou ';

$sql.= ' WHERE fk_parcours = ' . $parcoursId . ';';


//$sql.= ' WHERE 1=1';
//$sql.= ' AND t.entity IN ('.getEntity('MyModule', 1).')';
//if ($type == 'mine') $sql.= ' AND t.fk_user = '.$user->id;

$head = minigolfPrepareHeadForParcoursCard($parcoursId);

dol_fiche_head($head, 'tabTrous', $langs->trans("ModuleMinigolf"), 0, $picto);

$formCore = new TFormCore($_SERVER['PHP_SELF'], 'form_list_minigolfParcours', 'POST');

$formCore->Set_typeaff($mode);

$form = new Form($PDOdb);
$nbLine = !empty($user->conf->MAIN_SIZE_LISTE_LIMIT) ? $user->conf->MAIN_SIZE_LISTE_LIMIT : $conf->global->MAIN_SIZE_LISTE_LIMIT;

$r = new TListviewTBS('minigolf');
echo $r->render($PDOdb, $sql, array(
	'view_type' => 'list' // default = [list], [raw], [chart]
	,'limit'=>array(
		'nbLine' => $nbLine
	)
	,'subQuery' => array()
,'link' => array('name' => '<a href="cardParcoursTrou.php?id=@rowid@&action=edit">@val@</a>'
    , 'ordre' => '<input name="ordre" type="text" value="@val@"/>'
    , 'dellink' => '<a href="listParcoursTrou.php?rowid=@dellink@&action=delete">X</a>'
    )
	,'type' => array(
		'date_cre' => 'date' // [datetime], [hour], [money], [number], [integer]
		,'date_maj' => 'date'
	)
	/*,'search' => array(
		'date_cre' => array('recherche' => 'calendars', 'allow_is_null' => true)
		,'date_maj' => array('recherche' => 'calendars', 'allow_is_null' => false)
		,'ref' => array('recherche' => true, 'table' => 't', 'field' => 'ref')
		,'label' => array('recherche' => true, 'table' => array('t', 't'), 'field' => array('label', 'description')) // input text de recherche sur plusieurs champs
		,'status' => array('recherche' => TMymodule::$TStatus, 'to_translate' => true) // select html, la clé = le status de l'objet, 'to_translate' à true si nécessaire
	)*/
	,'translate' => array()
	,'hide' => array(
		'rowid' , 'date_cre' , 'date_maj'
	)
	,'liste' => array(
		'titre' => $langs->trans('editionTroudunparcours') . _getParcoursNameFromId($fk_parcours)
		,'image' => img_picto('','title_generic.png', '', 0)
		,'picto_precedent' => '<'
		,'picto_suivant' => '>'
		,'noheader' => 0
		,'messageNothing' => $langs->trans('NoMyModule')
		,'picto_search' => img_picto('','search.png', '', 0)
	)
	,'title'=>array(
		'fk_trou' => $langs->trans('trou')
		,'ordre' => $langs->trans('ordre')
		,'date_cre' => $langs->trans('DateCre')
		,'date_maj' => $langs->trans('DateMaj')
	)
	,'eval'=>array(
		'fk_trou' => '_getTrouNameFromId(@val@)' // Si on a un fk_user dans notre requête
	)
));

$parameters=array('sql'=>$sql);
$reshook=$hookmanager->executeHooks('printFieldListFooter', $parameters, $object);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;


echo "<input type=hidden name='action' value='save' />";

if ($action == 'edit') echo $formCore->btsubmit( $langs->trans('Save'), 'bt_save' );

echo '<a class="button"  href="' .  dol_buildpath('/minigolf/listParcours.php?',1) .'">' . $langs->trans("backToParcours") . '</a>';

$formCore->end_form();


//ajouter un trou a un parcours
// on affiche la liste des trous associer au parcours  $parcoursId
//todo a remplacer un jour par appel obj abricot
global $db;
$sql = "SELECT rowid, name, difficulty FROM ".MAIN_DB_PREFIX."minigolf_trou ;";

$resql = $db->query($sql);

$html ="<form name='addTrouToParcours' method='post' action=''>";
$html.='<select name="fk_trou">';
if ($resql)   {
    $res = $db->num_rows($resql);
    $i = 0;
    if ($res) {
        while ($i < $res) {
            $obj = $db->fetch_object($resql);
            if ($obj) {
                $html .= '<option value="'.$obj->rowid.'">'.$obj->name .'</option>';
            }
            $i++;
        }
    }
}
$html .= "</select>";
$html .= "<input type='hidden' name='action' value='save'/>";
$html .= "<input type='hidden' name='fk_parcours' value='".$parcoursId."'/>";
$html .= "<input type='hidden' name='ordre' value='999'/>";

$html .= "<input type='submit' value='".$langs->trans('Ajouter un Trou')."'/>";
$html .= "</form>";

echo $html;



llxFooter('');


