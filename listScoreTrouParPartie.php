<?php

require 'config.php';
dol_include_once('/minigolf/class/minigolf.class.php');
dol_include_once('/minigolf/lib/minigolf.lib.php');

if(empty($user->rights->minigolf->read)) accessforbidden();

$langs->load('abricot@abricot');
$langs->load('minigolf@minigolf');

$PDOdb = new TPDOdb;

$object = new TFicheScore();

//$parcoursId = GETPOST('parcoursId');

$partieId = GETPOST('partieId');

$action = GETPOST('action');

if (empty($action) ) $action = $mode = 'view';

$hookmanager->initHooks(array('minigolfHook'));

//var_dump($_REQUEST);exit;
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

            $object->set_values($_POST); // Set standard attributes

            //var_dump($object);exit;

            $object->save($PDOdb, empty($object->ref)); // ref ?

            header('Location: '.dol_buildpath('/minigolf/listScoreTrouParPartie.php', 1) ) ;
            exit;

            break;

        case 'delete' :

            /*
            $rowid = GETPOST('rowid');

            $object->load($PDOdb, $rowid);

            $object->to_delete = true;

            $object->save($PDOdb);

            header('Location: '.dol_buildpath('/minigolf/listParcoursTrou.php', 1)."?action=edit&parcoursId=$object->fk_parcours"  ); //.'?id= .$object->getId());
            exit;

            */

            break;


        case 'create' :
            // aucunes info dispo, on demande pour quel joueur et quel parcours

            //dol_fiche_head();

            //var_dump($_REQUEST);exit;

            llxHeader('',$langs->trans('Choisir Parcour et Joueur') );

            $sql = "SELECT rowid, name, difficulty FROM ".MAIN_DB_PREFIX."minigolf_parcours ;";

            $resql = $db->query($sql);

            //quel parcours ?
            echo $langs->trans("Veuillez choisir un parcours");

            $html ="<form name='SelectAParcours' method='get' action=''>";
            $html.='<select name="fk_parcours">';
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


            // quel joueur ?
            echo $langs->trans("Veuillez choisir un joueur");

            $sql = "SELECT rowid, firstname, lastname FROM ".MAIN_DB_PREFIX."user ;";

            $resql = $db->query($sql);


            $html.='<select name="userId">';
            if ($resql)   {
                $res = $db->num_rows($resql);
                $i = 0;
                if ($res) {
                    while ($i < $res) {
                        $obj = $db->fetch_object($resql);
                        if ($obj) {
                            $html .= '<option value="'.$obj->rowid.'">'.$obj->lastname.' '. $obj->firstname .'</option>';
                        }
                        $i++;
                    }
                }
            }
            $html .= "</select>";

            $html .= "<input type='hidden' name='action' value='createFicheScore'/>";

            $html .= "<input type='submit' value='".$langs->trans('CommencerLaSaisie')."'/>";

            $html .= "</form>";

            echo $html;

            llxFooter('');

            exit;
            break;

        case 'createFicheScore' :

            //var_dump($_REQUEST);



            $parcoursId = GETPOST('fk_parcours') ;

            $userId = GETPOST('userId');

            // recuperation de la liste des trous du parcours

            //-----
            global $db;

            $sql  = "SELECT fk_trou , ordre";
            $sql .= " FROM ".MAIN_DB_PREFIX."minigolf_parcours_trou ";
            $sql .= " WHERE fk_parcours = " . $parcoursId;
            $sql .= " ORDER BY ordre;";

            $resql = $db->query($sql);

            //var_dump($_REQUEST);
            //var_dump($resql);exit;

            // creation d'un formulaire en fonction du parcours choisi
            $html ="<form name='saisirScoreParTrou' method='get' action=''>";
            $html .= "<table>";
            if ($resql)   {
                $res = $db->num_rows($resql);
                $i = 0;
                if ($res) {
                    while ($i < $res) {
                        $obj = $db->fetch_object($resql);
                        if ($obj) {

                            $html .= '<tr><td>'.$langs->trans("Score pour le trou n° :").$obj->fk_trou.'</td>';
                            $html .= '<td><input type="text" name="'.$obj->fk_trou.'"></td></tr>';
                        }
                        $i++;
                    }
                }
            }
            $html .= "</table>";

            $html .= "<input type='hidden' name='action' value='saveFicheScore'/>";
            $html .= "<input type='hidden' name='fk_parcours' value='".$parcoursId."'/>";
            $html .= "<input type='hidden' name='userId' value='".$userId."'/>";
            $html .= "<input type='submit' value='".$langs->trans('Enregistrer la Fiche de score')."'/>";
            $html .= "</form>";

            llxHeader();
            echo $html;
            llxFooter();


            //-----

            // generation de la liste



            exit;
            break;

        case 'saveFicheScore' :

            $userId = GETPOST('userId');

            $fk_parcours = GETPOST('fk_parcours');

            //var_dump($_REQUEST);exit;

            //entrée : on récupere un listing de score pour chaque trou d'un parcours

            // on doit inserer dans la table minigolf_score (champs : fk_partie, fk_trou , score)

            // il faudrait le faire avec un object abricot, mais je ne suis pas encore assez a l'aise avec pour le produire en 10min

            // sql donc :

            //on se renseigne sur le nouveau numéro de partie
            $newIdPartie = getNewRowIdFrom('minigolf_partie');
            //data ok, insertion bdd
            //insertion nouvelle partie ( parcours , user )

            $sql = "INSERT INTO " .MAIN_DB_PREFIX. "minigolf_partie (rowid, parcoursId, userId)";
            $sql .= " VALUES ('$newIdPartie','$fk_parcours','$userId') ;" ;

            $db->query($sql);
            $sql="";

            //insertion score

            $sql .= "INSERT INTO " .MAIN_DB_PREFIX. "minigolf_score (rowid, fk_partie , fk_trou , score)";
            $sql .= " VALUES ";
            // pour chaque 'score' (identifié par l'id du trou) on insert fk_partie, fk_trou, score
            $newIdScore = (int) getNewRowIdFrom('minigolf_score');

            //var_dump($_GET);exit;
            $i=0;
            foreach ($_GET as $trouId => $score){
                if ( empty( (int) $trouId) ) break;
                if ($i>0) $sql .= ",";
                //echo "score for trou $trouId : $score <br/>";

                $sql .= "('$newIdScore','$newIdPartie','$trouId','$score')" ;

                $newIdScore++;
                $i++;
            }
            $sql .= ' ;';

            //echo $sql;exit;

            $resql = $db->query($sql);


            header('Location: '.dol_buildpath('/minigolf/listPartie.php', 1) ) ;

            exit;

            break;


    }





}


/*
 * View
 */




//$type = GETPOST('type');
//if (empty($user->rights->mymodule->all->read)) $type = 'mine';

// TODO ajouter les champs de son objet que l'on souhaite afficher





$find = false;
if(!empty($partieId) ) {

    $find= true;

    $sql .= 'SELECT p.rowid , t.fk_partie , t.fk_trou, t.score';
    $sql .= ' FROM llx_minigolf_partie p';
    $sql .= ' LEFT JOIN llx_minigolf_score t ON p.rowid = t.fk_partie';
    $sql .= " WHERE p.rowid = $partieId ";

}
else  {


}

//$head = '';//minigolf_prepare_head();

llxHeader('',$langs->trans('Saisir Nouvelle partie') );

//dol_fiche_head($head);

$formCore = new TFormCore($_SERVER['PHP_SELF'], 'form_saisie_nouvelle_partie', 'POST');

$formCore->Set_typeaff($mode);

//$form = new Form($PDOdb);

$nbLine = !empty($user->conf->MAIN_SIZE_LISTE_LIMIT) ? $user->conf->MAIN_SIZE_LISTE_LIMIT : $conf->global->MAIN_SIZE_LISTE_LIMIT;

$r = new TListviewTBS('minigolf');

echo $r->render($PDOdb, $sql, array(
	'view_type' => 'list' // default = [list], [raw], [chart]
	,'limit'=>array(
		'nbLine' => $nbLine
	)
	,'subQuery' => array()
,'link' => array()
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
		'titre' => $langs->trans('SaisieNouvellePartieSur') . _getParcoursNameFromId($fk_parcours)
		,'image' => img_picto('','title_generic.png', '', 0)
		,'picto_precedent' => '<'
		,'picto_suivant' => '>'
		,'noheader' => 0
		,'messageNothing' => $langs->trans('NoMyModule')
		,'picto_search' => img_picto('','search.png', '', 0)
	)
	,'title'=>array(
		'date_cre' => $langs->trans('DateCre')
		,'date_maj' => $langs->trans('DateMaj')
	)
	,'eval'=>array(

	)
));

$parameters=array('sql'=>$sql);
$reshook=$hookmanager->executeHooks('printFieldListFooter', $parameters, $object);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;


echo "<input type=hidden name='action' value='save' />";

if ($action == 'edit') echo $formCore->btsubmit( $langs->trans('Save'), 'bt_save' );

echo '<a class="button"  href="' .  dol_buildpath('/minigolf/listParcours.php?',1) .'">' . $langs->trans("backToParcours") . '</a>';

$formCore->end_form();

/*
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
*/


llxFooter('');


