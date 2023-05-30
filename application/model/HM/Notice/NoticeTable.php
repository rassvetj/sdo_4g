<?php

class HM_Notice_NoticeTable extends HM_Db_Table
{
    protected $_name = "notice";
    protected $_primary = "id";
    protected $_sequence = "S_40_1_NOTICE";

  

    public function getDefaultOrder()
    {
        return array('notice.event DESC');
    }
}