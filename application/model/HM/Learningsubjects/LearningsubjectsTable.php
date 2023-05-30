<?php
class HM_Learningsubjects_LearningsubjectsTable extends HM_Db_Table
{
    protected $_name = "learning_subjects";
    protected $_primary = "learning_subject_id";
    protected $_sequence = "";

    protected $_referenceMap = array(
        
    );

    public function getDefaultOrder()
    {
        return array('learning_subjects.learning_subject_id ASC');
    }
}