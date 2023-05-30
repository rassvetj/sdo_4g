<?php

class HM_Lesson_Evaluation_EvaluatorsTable extends HM_Db_Table
{
    protected $_name = "schedule_evaluators";
    protected $_primary = "SHEID";

//    protected $_referenceMap = array(
//        'Lesson' => array(
//            'columns'       => 'SHEID',
//            'refTableClass' => 'HM_Lesson_LessonTable',
//            'refColumns'    => 'SHEID',
//            'propertyName'  => 'lessons'
//        ),
//    );
}