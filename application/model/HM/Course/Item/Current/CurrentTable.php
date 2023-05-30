<?php

class HM_Course_Item_Current_CurrentTable extends HM_Db_Table
{
    protected $_name = "sequence_current";
    protected $_primary = array('mid', 'cid', 'subject_id', 'lesson_id');
   // protected $_sequence = "S_44_1_ORGANIZATIONS";

    //protected $_dependentTables = array("HM_Role_StudentTable");

    protected $_referenceMap = array(
        'Subject' => array(
            'columns' => 'subject_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns' => 'subid',
            'propertyName' => 'subjects'
        ),
        'Course' => array(
            'columns' => 'cid',
            'refTableClass' => 'HM_Course_CourseTable',
            'refColumns' => 'CID',
            'propertyName' => 'courses'
        ),
        'Item' => array(
            'columns' => 'current',
            'refTableClass' => 'HM_Course_Item_ItemTable',
            'refColumns' => 'oid',
            'propertyName' => 'items'
        ),
        'People' => array(
            'columns' => 'mid',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns' => 'MID',
            'propertyName' => 'peoples'
        )
    );// имя свойства текущей модели куда будут записываться модели зависимости
    

    public function getDefaultOrder()
    {
        return array('sequence_current.mid ASC');
    }
}