<?php

class HM_Library_Item_ItemTable extends HM_Db_Table
{
    protected $_name = "library";
    protected $_primary = "bid";
    protected $_sequence = "S_90_1_LIBRARY";

    //protected $_dependentTables = array("HM_Role_StudentTable");

    protected $_referenceMap = array(
        'Course' => array(
            'columns'       => 'cid',
            'refTableClass' => 'HM_Course_CourseTable',
            'refColumns'    => 'CID',
            'propertyName'  => 'course' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'CourseItem' => array(
            'columns'       => 'bid',
            'refTableClass' => 'HM_Course_Item_ItemTable',
            'refColumns'    => 'module',
            'propertyName'  => 'courseitem' // имя свойства текущей модели куда будут записываться модели зависимости
        )

    );

    public function getDefaultOrder()
    {
        return array('test.title ASC');
    }
}