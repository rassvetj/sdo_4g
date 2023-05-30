<?php

class HM_Exercises_ExercisesTable extends HM_Db_Table
{
    
    protected $_name = "exercises";
    protected $_primary = "exercise_id";
    protected $_sequence = "S_67_1_EXERCISES";
//
    protected $_dependentTables = array("HM_Test_Question_QuestionTable");
//
    protected $_referenceMap = array(
        'Question' => array(
            'columns'       => 'exercise_id',
            'refTableClass' => 'HM_Exercises_Question_QuestionTable',
            'refColumns'    => 'exercise_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'questions' // имя свойства текущей модели куда будут записываться модели зависимости
        )
    );

    public function getDefaultOrder()
    {
        return array('exercises.exercise_id ASC');
    }
     
     
}