<?php

class TParcours extends TObjetStd
{

    public function __construct()
    {
        global $conf, $langs, $db;

        $this->set_table(MAIN_DB_PREFIX . 'minigolf_parcours');

        $this->add_champs('name', array('type' => 'string' , 'index' => true));

        $this->add_champs('difficulty', array('type' => 'integer'));

        $this->_init_vars();

        $this->start();

        //$this->setChild('TParcoursTrou', 'fk_parcours');

        $this->errors = array();
    }


}


class TParcoursTrou extends TObjetStd {

    public function __construct()
    {
        global $conf,$langs,$db;

        $this->set_table(MAIN_DB_PREFIX.'minigolf_parcours_trou' );

        $this->add_champs('ordre,fk_trou,fk_parcours', array('type' => 'integer', 'index'=>true ));

        $this->_init_vars();

        $this->start();

        $this->trou = null;

        $this->errors = array();
    }


}


class TPartie extends TObjetStd
{

    public function __construct()
    {
        global $conf,$langs,$db;

        $this->set_table(MAIN_DB_PREFIX.'minigolf_partie');

        $this->add_champs('parcoursId', array('type' => 'integer', 'index' => true));

        $this->add_champs('userId', array('type' => 'integer', 'index' => false));

        $this->_init_vars();

        $this->start();

        $this->errors = array();
    }

}



class TTrou extends TObjetStd
{

    public function __construct()
    {
        global $conf,$langs,$db;

        $this->set_table(MAIN_DB_PREFIX.'minigolf_trou');

        $this->add_champs('name', array('type' => 'string', 'index' => true));
        $this->add_champs('difficulty', array('type' => 'integer', 'index' => false));



        $this->_init_vars();

        $this->start();

        $this->errors = array();
    }

}



//utils

function minigolf_PrepareHeadForParcoursOrDetails($idParcours)
{
    global $langs, $conf;

    $langs->load("minigolf@minigolf");

    $h = 0;
    $head = array();


    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    //$this->tabs = array(
    //	'entity:+tabname:Title:@minigolf:/minigolf/mypage.php?id=__ID__'
    //); // to add new tab
    //$this->tabs = array(
    //	'entity:-tabname:Title:@minigolf:/minigolf/mypage.php?id=__ID__'
    //); // to remove a tab


    $head[$h][0] = dol_buildpath("/custom/minigolf/parcourstrou.php?action=viewParcours&id=$idParcours",1); //; //link
    $head[$h][1] = $langs->trans("Editer parcours"); // label
    $head[$h][2] = 'parcoursTab'; //id link
    $h++;
    $head[$h][0] = dol_buildpath("/custom/minigolf/parcourstrou.php?action=viewDetails&id=$idParcours",1);//&id=__ID__", 1);
    $head[$h][1] = $langs->trans("Association des Trous");
    $head[$h][2] = 'trouTab';
    $h++;


    //complete_head_from_modules($conf, $langs, $object, $head, $h, 'minigolf');

    return $head;
}

?>