<?php

require 'config.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
dol_include_once('/minigolf/class/minigolf.class.php');
dol_include_once('/minigolf/lib/minigolf.lib.php');

if(empty($user->rights->minigolf->read)) accessforbidden();

$langs->load('minigolf@minigolf');

$action = GETPOST('action');
$id = GETPOST('id', 'int');

$mode = 'view';
if (empty($user->rights->minigolf->write)) $mode = 'view'; // Force 'view' mode if can't edit object
else if ($action == 'create' || $action == 'edit') $mode = 'edit';

$PDOdb = new TPDOdb;
$object = new TPartie();

if (!empty($id)) $object->load($PDOdb, $id);
elseif (!empty($ref)) $object->loadBy($PDOdb, $ref, 'ref');

$hookmanager->initHooks(array('mymodulecard', 'globalcard'));

/*
 * Actions
 */

$parameters = array('id' => $id, 'ref' => $ref, 'mode' => $mode);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

// Si vide alors le comportement n'est pas remplacé
if (empty($reshook))
{
    $error = 0;
    switch ($action) {

        case 'new' :

            $mode = 'edit';
            $id = null; // failsafe si on essaye d'injecter id a la main

            break;


        case 'save':


//            var_dump($_POST);



            $object->set_values($_POST); // Set standard attributes

            //var_dump($object);exit;

            //$object->set_values($_POST);


//			$object->date_other = dol_mktime(GETPOST('starthour'), GETPOST('startmin'), 0, GETPOST('startmonth'), GETPOST('startday'), GETPOST('startyear'));

            // Check parameters
//			if (empty($object->date_other))
//			{
//				$error++;
//				setEventMessages($langs->trans('warning_date_must_be_fill'), array(), 'warnings');
//			}

            // ...

            if ($error > 0)
            {
                $mode = 'edit';

                break;
            }

            $object->save($PDOdb, empty($object->ref)); // ref ?

            header('Location: '.dol_buildpath('/minigolf/listPartie.php', 1) ) ; //.'?id= .$object->getId());
            exit;

            break;
        case 'confirm_clone':
            $object->cloneObject($PDOdb);

            header('Location: '.dol_buildpath('/minigolf/cardPartie.php', 1).'?id='.$object->getId());
            exit;
            break;
        case 'modif':
            if (!empty($user->rights->minigolf->write)) $object->setDraft($PDOdb);

            break;
        case 'confirm_validate':
            if (!empty($user->rights->minigolf->write)) $object->setValid($PDOdb);

            header('Location: '.dol_buildpath('/minigolf/cardPartie.php', 1).'?id='.$object->getId());
            exit;
            break;
        case 'confirm_delete':
            if (!empty($user->rights->minigolf->write)) $object->delete($PDOdb);

            header('Location: '.dol_buildpath('/minigolf/listPartie.php', 1));
            exit;
            break;
        // link from llx_element_element
        case 'dellink':
            $object->generic->deleteObjectLinked(null, '', null, '', GETPOST('dellinkid'));
            header('Location: '.dol_buildpath('/minigolf/cardPartie.php', 1).'?id='.$object->getId());
            exit;
            break;
    }
}


/**
 * View
 */

$title=$langs->trans("minigolf");
llxHeader('',$title);

if ($action == 'create' && $mode == 'edit')
{
    load_fiche_titre($langs->trans("minigolf cardPartie"));
    dol_fiche_head();
}
else
{
    $head = minigolfAdminPrepareHead();
    $picto = 'generic';
    dol_fiche_head($head, 'card', $langs->trans("cardPartieTitle"), 0, $picto);
}

$formCore = new TFormCore;
$formCore->Set_typeaff($mode);

$form = new Form($PDOdb);

$formconfirm = getFormConfirm($PDOdb, $form, $object, $action);
if (!empty($formconfirm)) echo $formconfirm;


if ($mode == 'edit') echo $formCore->begin_form($_SERVER['PHP_SELF'], 'form_minigolf_card');

$linkback = '<a href="'.dol_buildpath('custom/minigolf/listPartie.php', 1).'">' . $langs->trans("BackToList") . '</a>';



/*Formulaire perso*/



if(empty($object->rowid)){

    $rowid = null;
    $parcoursId     = $langs->trans('Choissiez un parcours'); //TODO SELECT
    $userId = $langs->trans('Choissiez un utilisateur');

}

else {

    $rowid = $object->rowid;
    $parcoursId     = $object->parcoursId;
    $userId = $object->userId;

}


echo "<input type=hidden name='action' value='save' />";


echo "<div name='newPartie' style='padding:20px;'>";

echo "<table  >";

//function texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')
/*
echo "<tr><td style='width:150px;' >". $langs->trans('parcoursId') . "</td><td style='width:150px;' >";
echo $formCore->texte('', 'parcoursId', $parcoursId, 22, 255, '');

echo "<tr><td >". $langs->trans('userId') . "</td><td>";
echo $formCore->texte('', 'userId', $userId, 22, 255, '');

echo "<tr><td>";

if ($mode == 'edit') echo $formCore->btsubmit( $langs->trans('Save'), 'bt_save' );

echo "</td><td style='padding-top:20px;'>";

echo '<a class="button"  href="' .  dol_buildpath('/minigolf/listPartie.php?',1) .'">' . $langs->trans("backToParcours") . '</a>';

echo "</td>";
*/

//on veux afficher la liste des parcours disponible

//ainsi que la liste des joueurs existant.

//a la validation de ces 2 informations on va générer dynamiquement un formulaire

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




echo "</table>";

echo "</div>";



if ($mode == 'edit') echo $formCore->end_form();

//if ($mode == 'view' && $object->getId()) $somethingshown = $form->showLinkedObjectBlock($object->generic);

llxFooter();