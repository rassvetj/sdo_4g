<?php
class HM_DisabledPeople_DisabledPeopleTable extends HM_Db_Table
{
    protected $_name = "disabled_people_ways";
    protected $_primary = "way_id";
    protected $_sequence = "";

    protected $_referenceMap = array(
    );

    public function getDefaultOrder()
    {
        return array('disabled_people_ways.way_id ASC');
    }
}