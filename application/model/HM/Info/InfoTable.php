<?php
class HM_Info_InfoTable extends HM_Db_Table
{
    protected $_name = "news2";
    protected $_primary = "nID";
    protected $_sequence = "S_42_1_NEWS2";

    public function getDefaultOrder()
    {
        return array('news2.Title ASC');
    }
}