<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 */

if(!defined('INC_FROM_DOLIBARR')) {
	define('INC_FROM_CRON_SCRIPT', true);

	require('../config.php');

}
dol_include_once('/minigolf/class/minigolf.class.php');

$PDOdb=new TPDOdb;


$o=new TParcours;
$o->init_db_by_vars($PDOdb);


$o=new TParcoursTrou;
$o->init_db_by_vars($PDOdb);


$o=new TPartie;
$o->init_db_by_vars($PDOdb);



$o=new TTrou;
$o->init_db_by_vars($PDOdb);

$o=new TFicheScore;
$o->init_db_by_vars($PDOdb);


/*$o=new TMyModuleChild;
$o->init_db_by_vars($PDOdb);*/
