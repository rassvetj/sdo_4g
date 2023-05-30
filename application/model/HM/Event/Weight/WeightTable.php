<?php

class HM_Event_Weight_WeightTable extends HM_Db_Table
{
    protected $_name = "eventtools_weight";
    protected $_primary = "id";
    protected $_sequence = "S_97_1_EVENTTOOLS_WEIGHT";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'Event' => array(
            'columns'       => 'event',
            'refTableClass' => 'HM_Event_EventTable',
            'refColumns'    => 'TypeID',
            'propertyName'  => 'events' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Course' => array(
            'columns'       => 'cid',
            'refTableClass' => 'HM_Course_CourseTable',
            'refColumns'    => 'CID',
            'propertyName'  => 'courses' // имя свойства текущей модели куда будут записываться модели зависимости            
        )

    );

    public function getDefaultOrder()
    {
        return array('eventtools.weight.id ASC');
    }
}