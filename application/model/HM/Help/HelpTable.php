<?php

class HM_Help_HelpTable extends HM_Db_Table
{
    protected $_name = "help";
    protected $_primary = "help_id";
    protected $_sequence = "S_100_1_HELP";

    protected $_dependentTables = array();

    protected $_referenceMap = array(

    );

    public function getDefaultOrder()
    {
        return array('help.title ASC');
    }
}