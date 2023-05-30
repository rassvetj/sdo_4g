<?php

class HM_Course_Comp2Course_Comp2CourseTable extends HM_Db_Table
{
    protected $_name = "comp2course";
    protected $_primary = "ccoid";
    protected $_sequence = "S_84_1_COMP2COURSE";

     protected $_referenceMap = array(
        'Competence' => array(
            'columns'       => 'coid',
            'refTableClass' => 'HM_Course_Competence_CompetenceTable',
            'refColumns'    => 'coid',
            'propertyName'  => 'competences' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Course' => array(
            'columns'       => 'cid',
            'refTableClass' => 'HM_Course_CourseTable',
            'refColumns'    => 'cID',
            'propertyName'  => 'courses'
        )

    );
    
    
    
   public function getDefaultOrder()
    {
        return array('comp2course.ccoid ASC');
    }
}