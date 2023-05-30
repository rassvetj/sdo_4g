<?php

class HM_Module_Run_RunTable extends HM_Db_Table
{
    protected $_name = "training_run";
    protected $_primary = "run_id";
    protected $_sequence = "S_74_1_TRAINING_RUN";

    //protected $_dependentTables = array("HM_Role_StudentTable");

    protected $_referenceMap = array(
        'Course' => array(
            'columns'       => 'cid',
            'refTableClass' => 'HM_Course_CourseTable',
            'refColumns'    => 'CID',
            'propertyName'  => 'course' // имя свойства текущей модели куда будут записываться модели зависимости
        )
    );

    public function getDefaultOrder()
    {
        return array('training_run.name ASC');
    }
}