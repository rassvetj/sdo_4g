<?php

class HM_Tag_TagTable extends HM_Db_Table
{
    protected $_name = "tag";
    protected $_primary = "id";
    protected $_sequence = "S_101_1_TAG";

    protected $_dependentTables = array(
        "HM_Tag_TagRefTable"
    );

    protected $_referenceMap = array(
        'TagRef' => array(
            'columns'       => 'id',
            'refTableClass' => 'HM_Tag_Ref_RefTable',
            'refColumns'    => 'tag_id',
            'propertyName'  => 'tagRef'
        )
    );

    public function getDefaultOrder()
    {
        return array();
    }
}