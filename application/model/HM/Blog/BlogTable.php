<?php

class HM_Blog_BlogTable extends HM_Db_Table
{
    protected $_name = "blog";
    protected $_primary = "id";
    protected $_sequence = "S_100_1_BLOG";

    protected $_referenceMap = array(
        'TagRef' => array(
            'columns'       => 'id',
            'refTableClass' => 'HM_Tag_Ref_RefTable',
            'refColumns'    => 'item_id', // не будет работать, это не есть id блога
            'propertyName'  => 'tagref'
        ),
        'User' => array(
            'columns' => 'created_by',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns' => 'MID',
            'propertyName' => 'User' 
        )
    );


    public function getDefaultOrder()
    {
        return array('blog.created DESC');
    }
}
