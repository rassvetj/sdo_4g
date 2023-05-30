<?php

class HM_Lesson_Poll_Dean_Assign_AssignTable extends HM_Db_Table
{
    protected $_name = "dean_poll_users";
    protected $_primary = array('lesson_id', 'head_mid', 'student_id');
    //protected $_sequence = "S_58_1_SCHEDULEID";

    //protected $_dependentTables = array("HM_Role_StudentTable");

    protected $_referenceMap = array(
    );

    public function getDefaultOrder()
    {
        return array('dean_poll_users.lesson_id ASC');
    }
}