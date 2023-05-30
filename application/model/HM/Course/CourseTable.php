<?php

class HM_Course_CourseTable extends HM_Db_Table
{
    protected $_name = "Courses";
    protected $_primary = "CID";
    protected $_sequence = "S_19_1_COURSES";

    protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable",
        'HM_Course_Comp2Course_Comp2CourseTable',
        'HM_Subject_Course_CourseTable',
        'HM_Course_Item_History_HistoryTable',
        'HM_Course_Item_Current_CurrentTable'
    );

    protected $_referenceMap = array(
        'SubjectAssign' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Subject_Course_CourseTable',
            'refColumns'    => 'course_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'subjects' // имя свойства текущей модели куда будут записываться модели зависимости        
        ),
        'Item' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Course_Item_ItemTable',
            'refColumns'    => 'cid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'items'
        ),
        'Test' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Module_Test_TestTable',
            'refColumns'    => 'cid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'tests'
        ),
        'Run' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Module_Run_RunTable',
            'refColumns'    => 'cid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'runs'
        ),
        'Material' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Module_Material_MaterialTable',
            'refColumns'    => 'cid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'materials'
        ),
        'Speciality_Assign' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Speciality_Course_CourseTable',
            'refColumns'    => 'cid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'assigns'            
        ),
        'Comp2Course' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Course_Comp2Course_Comp2CourseTable',
            'refColumns'    => 'CID',
            'propertyName'  => 'competences'            
        ),
        'Teacher' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Role_TeacherTable',
            'refColumns'    => 'CID',
            'propertyName'  => 'teachers'
        ),
        'itemCurrent' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Course_Item_Current_CurrentTable',
            'refColumns'    => 'cid',
        	'onDelete'      => self::CASCADE,
            'propertyName'  => 'itemCurrent'
        ),
        'itemHistory' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Course_Item_History_HistoryTable',
            'refColumns'    => 'cid',
        	'onDelete'      => self::CASCADE,
            'propertyName'  => 'itemHistory'
        ),
        'TagRef' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Tag_Ref_RefTable',
            'refColumns'    => 'item_id',
            'propertyName'  => 'tagRefs' // ВНИМАНИЕ!!! коллекцию нужно еще отфильтровать по item_type!
        ),
    );

    public function getDefaultOrder()
    {
        return array('Courses.Title ASC');
    }
}