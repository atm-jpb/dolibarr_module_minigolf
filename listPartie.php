<?php

require 'config.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/usergroups.lib.php';
dol_include_once('/minigolf/class/minigolf.class.php');
dol_include_once('/minigolf/lib/minigolf.lib.php');

if(empty($user->rights->minigolf->read)) accessforbidden();

$langs->load('abricot@abricot');
$langs->load('minigolf@minigolf');

$PDOdb = new TPDOdb;

$object = new TPartie();

$action = GETPOST('action');

$userId = GETPOST('userId'); // en provenance d'une fiche utilisateur => on filtre la liste

$hookmanager->initHooks(array('minigolfHook'));

/*
 * Actions
 */

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	// do action from GETPOST ...

    switch ($action){

        case 'delete' :

            $rowid = GETPOST('rowid');

            $object->load($PDOdb, $rowid);

            $object->to_delete = true;

            $object->save($PDOdb);

            // cleanup

            $cleanup = new Tpartie();

            $cleanup->removeAssocFor($rowid);

            header('Location: '.dol_buildpath('/minigolf/listPartie.php', 1) );
            exit;

            break;
    }



}


/*
 * View
 */

llxHeader('',$langs->trans('Liste des parties'),'','');

if (!empty($userId)){
    global $user;

    //TODO :check si les permissions grisent les onglets correctement

    if ($userId == $user->id){

            $myuser = $user;
    }
    else $myuser = $user->fetch($userId);

    $head = user_prepare_head($myuser);

    dol_fiche_head($head,'PartieDeGolf');
}

//generation formulaire

//$type = GETPOST('type');
//if (empty($user->rights->mymodule->all->read)) $type = 'mine';

$sql = 'SELECT t.rowid, t.parcoursId, t.userId , t.rowid as dellink' ; //, t.date_cre, t.date_maj, \'\' AS action';

$sql.= ' FROM '.MAIN_DB_PREFIX.'minigolf_partie t ';

if (!empty($userId) ) $sql .= "WHERE t.userId = $userId";

//$sql.= ' WHERE 1=1';
//$sql.= ' AND t.entity IN ('.getEntity('MyModule', 1).')';
//if ($type == 'mine') $sql.= ' AND t.fk_user = '.$user->id;


$formCore = new TFormCore($_SERVER['PHP_SELF'], 'form_list_minigolfTrou', 'GET');

$nbLine = !empty($user->conf->MAIN_SIZE_LISTE_LIMIT) ? $user->conf->MAIN_SIZE_LISTE_LIMIT : $conf->global->MAIN_SIZE_LISTE_LIMIT;

$r = new TListviewTBS('minigolf');
echo $r->render($PDOdb, $sql, array(
	'view_type' => 'list' // default = [list], [raw], [chart]
	,'limit'=>array(
		'nbLine' => $nbLine
	)
	,'subQuery' => array()
    ,'link' => array('rowid' => '<a href="cardPartie.php?id=@rowid@&action=edit">@val@</a>'
    ,'parcoursId' => '<a href="cardParcours.php?id=@rowid@&action=edit">@val@</a>'
    ,'dellink' => '<a href="listPartie.php?rowid=@dellink@&action=delete">X</a>'
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
		'date_cre' , 'date_maj'
	)
	,'liste' => array(
		'titre' => empty(userId) ? $langs->trans('Liste Des Parties') : $langs->trans('Liste Des Parties') . ' :  ' . _getUserNameFromId($userId)
		,'image' => img_picto('','title_generic.png', '', 0)
		,'picto_precedent' => '<'
		,'picto_suivant' => '>'
		,'noheader' => 0
		,'messageNothing' => $langs->trans('NoMyModule')
		,'picto_search' => img_picto('','search.png', '', 0)
	)
	,'title'=>array(
		'userId' => $langs->trans('userId')
        ,'dellink' => $langs->trans('dellink')
        ,'parcoursId' => $langs->trans('parcoursId')
		,'date_cre' => $langs->trans('DateCre')
		,'date_maj' => $langs->trans('DateMaj')
        ,'rowid' => $langs->trans('identifiantPartie')
	)
	,'eval'=>array(
		'userId' => '_getUserNameFromId(@val@)' // Si on a un fk_user dans notre requête
        ,'parcoursId' => '_getParcoursNameFromId(@val@)'
	)
));

$parameters=array('sql'=>$sql);

$reshook=$hookmanager->executeHooks('printFieldListFooter', $parameters, $object);    // Note that $action and $object may have been modified by hook

print $hookmanager->resPrint;

$formCore->end_form();


echo '<a class="button"  href="' .  dol_buildpath('/minigolf/listScoreTrouParPartie.php',1) .'?action=create">' . $langs->trans("Saisir nouvelle partie") . '</a>';
llxFooter('');

/**
 * TODO remove if unused
 */
