<?php

class HM_Poll_Feedback_FeedbackTable extends HM_Db_Table
{
    protected $_name = "quizzes_feedback";
    protected $_primary = array('user_id', 'subject_id', 'lesson_id');
    //protected $_sequence = "S_36_1_LOGUSER";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'users' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Subject' => array(
            'columns'       => 'subject_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns'    => 'subid',
            'propertyName'  => 'subjects' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Lesson' => array(
            'columns'       => 'lesson_id',
            'refTableClass' => 'HM_Lesson_LessonTable',
            'refColumns'    => 'SHEID',
            'propertyName'  => 'lessons'            
        )
    );

    public function getDefaultOrder()
    {
        return array('quizzes_feedback.user_id ASC');
    }
}