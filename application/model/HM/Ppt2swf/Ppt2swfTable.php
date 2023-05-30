<?php

class HM_Ppt2swf_Ppt2swfTable extends HM_Db_Table
{
    protected $_name = "ppt2swf";
    protected $_primary = array('webinar_id', 'pool_id');


    protected $_referenceMap = array();

    public function getDefaultOrder()
    {
        return array('ppt2swf.pool_id');
    }
}