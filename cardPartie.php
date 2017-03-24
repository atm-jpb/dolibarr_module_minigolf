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

$hookmanager->initHooks(array('minigolfCardHook', 'globalcard'));

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

//if ($action == 'create' && $mode == 'edit')
//{
//    load_fiche_titre($langs->trans("minigolf cardPartie"));
//    dol_fiche_head();
//}
//else
//{
//    $head = minigolfAdminPrepareHead();
//    $picto = 'generic';
//    dol_fiche_head($head, 'card', $langs->trans("cardPartieTitle"), 0, $picto);
//}

//on veux afficher le résumé d'une partie

// cad nom du parcour, nom du joueur et le score pour chacuns des trous associés.


$myPartie = new TPartie();

$myPartie->load($PDOdb, $id);

$myParcoursId = $myPartie->parcoursId;

$myUser = $myPartie->userId;


echo "<br/> Partie n° $id";
echo "<br/> Joueur : ". _getUserNameFromId($myUser);



// on croise parcoursId avec les infos de parcoursTrou pour obtenir la liste des trous.

//$myTrouList = new TParcoursTrou();

$ficheScore = TFicheScore::getScoreForPartie($id); //$PDOdb,$myParcoursId

//var_dump($ficheScore) ;exit;

$html = "<table style='width: 20%; margin:20px;' name='FicheScore'>";
foreach($ficheScore as $key => $trou){

    //var_dump($trou);exit;

    $html .= "<tr><td>" . _getTrouNameFromId($trou['fk_trou']) . '</td><td>' . $langs->trans('Score') . ' : ' . $trou['score'] . "</td></tr>" ;
}

$html .= "</table>";
echo $html;

//$myUserId= $myPartie->userId;
//
//$user->fetch($myUserId);
//
//$myUsername = $user->getFullName($langs);

echo '<br/> <a class="button"  href="' .  dol_buildpath('/minigolf/listPartie.php',1) .'">' . $langs->trans("backToParcours") . '</a>';

llxFooter();