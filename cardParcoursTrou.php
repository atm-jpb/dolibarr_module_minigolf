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
$object = new TParcoursTrou();

if (!empty($id)) $object->load($PDOdb, $id);
elseif (!empty($ref)) $object->loadBy($PDOdb, $ref, 'ref');

//var_dump($object);

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

            header('Location: '.dol_buildpath('/minigolf/listParcoursTrou.php', 1) ) ; //.'?id= .$object->getId());
            exit;

            break;
        case 'confirm_clone':
            $object->cloneObject($PDOdb);

            header('Location: '.dol_buildpath('/minigolf/cardParcoursTrou.php', 1).'?id='.$object->getId());
            exit;
            break;
        case 'modif':
            if (!empty($user->rights->minigolf->write)) $object->setDraft($PDOdb);

            break;
        case 'confirm_validate':
            if (!empty($user->rights->minigolf->write)) $object->setValid($PDOdb);

            header('Location: '.dol_buildpath('/minigolf/cardParcoursTrou.php', 1).'?id='.$object->getId());
            exit;
            break;
        case 'confirm_delete':
            if (!empty($user->rights->minigolf->write)) $object->delete($PDOdb);

            header('Location: '.dol_buildpath('/minigolf/listParcoursTrou.php', 1));
            exit;
            break;
        // link from llx_element_element
        case 'dellink':
            $object->generic->deleteObjectLinked(null, '', null, '', GETPOST('dellinkid'));
            header('Location: '.dol_buildpath('/minigolf/cardParcoursTrou.php', 1).'?id='.$object->getId());
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
    load_fiche_titre($langs->trans("minigolf cardParcoursTrou"));
    dol_fiche_head();
}
else
{
    $head = minigolfAdminPrepareHead();
    $picto = 'generic';
    dol_fiche_head($head, 'tabParcours', $langs->trans("cardParcoursTrouTitle"), 0, $picto);
}

$formCore = new TFormCore;
$formCore->Set_typeaff($mode);

$form = new Form($PDOdb);

$formconfirm = getFormConfirm($PDOdb, $form, $object, $action);
if (!empty($formconfirm)) echo $formconfirm;


if ($mode == 'edit') echo $formCore->begin_form($_SERVER['PHP_SELF'], 'form_minigolf_card');

$linkback = '<a href="'.dol_buildpath('custom/minigolf/listParcoursTrou.php', 1).'">' . $langs->trans("BackToList") . '</a>';



//var_dump($object); exit;

if(empty($object->fk_parcours)){

    $fk_trou     = $langs->trans('TrouId');
    $fk_parcours = $langs->trans('ParcoursId');
    $ordre = $langs->trans('Ordre');

}

else {

    $fk_trou     = $object->fk_trou;
    $fk_parcours = $object->fk_parcours;
    $ordre = $object->ordre;

}

echo "<input type=hidden name='id' value='".$id."' />";
echo"<input type=hidden name='action' value='save' />";


echo "<div name='newParcoursTrou' style='padding:20px;'>";


echo "<table  >";

//function texte($pLib,$pName,$pVal,$pTaille,$pTailleMax=0,$plus='',$class="text", $default='')

echo "<tr><td style='width:150px;' >". $langs->trans('fk_trou') . "</td><td style='width:150px;' >";
echo $formCore->texte('', 'fk_trou', $fk_trou  , 22, 255, '');

echo "<tr><td style='width:150px;' >". $langs->trans('fk_parcours') . "</td><td style='width:150px;' >";
echo $formCore->texte('', 'fk_parcours', $fk_parcours, 22, 255, '');

echo "<tr><td style='width:150px;' >". $langs->trans('ordre') . "</td><td style='width:150px;' >";
echo $formCore->texte('', 'ordre', $ordre, 22, 255, '');


echo "<tr><td>";

if ($mode == 'edit') echo $formCore->btsubmit( $langs->trans('Save'), 'bt_save' );

echo "</td><td style='padding-top:20px;'>";

echo '<a class="button"  href="' .  dol_buildpath('/minigolf/listParcoursTrou.php?',1) .'">' . $langs->trans("backToParcours") . '</a>';

echo "</td>";

echo "</table>";

echo "</div>";



if ($mode == 'edit') echo $formCore->end_form();

//if ($mode == 'view' && $object->getId()) $somethingshown = $form->showLinkedObjectBlock($object->generic);

var_dump($_GET);
//var_dump($object);

llxFooter();
