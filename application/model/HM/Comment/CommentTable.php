<?php

class HM_Comment_CommentTable extends HM_Db_Table
{
    protected $_name = "comments";
    protected $_primary = "id";
    protected $_sequence = "S_94_1_COMMENTS";

    //protected $_dependentTables = array("HM_Lesson_Assign_AssignTable");

    protected $_referenceMap = array(
        /*'Course' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Course_CourseTable',
            'refColumns'    => 'CID',
            'propertyName'  => 'courses' // имя свойства текущей модели куда будут записываться модели зависимости            
        )*/
        'TagList' => array(
            'columns' => 'item_id',
            'refTableClass' => 'HM_Tag_Ref_RefTable',
            'refColumns' => 'item_id',
            'propertyName' => 'refTags'
        ),
        'User' => array(
            'columns' => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns' => 'MID',
            'propertyName' => 'User'
        )
    );

    public function getDefaultOrder()
    {
        return array('comments.id');
    }
}
