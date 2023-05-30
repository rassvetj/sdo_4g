<?php

class HM_Military_Info_InfoTable extends HM_Db_Table
{
    protected $_name = "military_info";
    protected $_primary = "info_id";
    protected $_sequence = "";

    protected $_referenceMap = array(
    );

    public function getDefaultOrder()
    {
        return array('military_info.info_id DESC');
    }
}