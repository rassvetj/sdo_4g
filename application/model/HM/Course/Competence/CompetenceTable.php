<?php

class HM_Course_Competence_CompetenceTable extends HM_Db_Table
{
    protected $_name = "competence";
    protected $_primary = "coid";
    protected $_sequence = "S_85_1_COMPETENCE";
    
    protected $_dependentTables = array(
        'HM_Course_Comp2Course_Comp2CourseTable');
    
    protected $_referenceMap = array(
        'Comp2Course' => array(
            'columns'       => 'coid',
            'refTableClass' => 'HM_Course_Comp2Course_Comp2CourseTable',
            'refColumns'    => 'coid',
            'propertyName'  => 'competences'            
        )

    );
    
    
    
    
   public function getDefaultOrder()
    {
        return array('Competence.name ASC');
    }
}