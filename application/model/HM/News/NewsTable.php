<?php

class HM_News_NewsTable extends HM_Db_Table
{
    protected $_name = "news";
    protected $_primary = "id";
    protected $_sequence = "S_41_1_NEWS";

  

    public function getDefaultOrder()
    {
        return array('news.created DESC');
    }
}