<?php

class HM_Subject_Room_RoomTable extends HM_Db_Table
{
    protected $_name = "rooms2course";
    protected $_primary = array("rid", "cid");

/*
     protected $_dependentTables = array(
        "HM_Role_StudentTable",
         "HM_Role_TeacherTable"
    );
*/    
    protected $_referenceMap = array(
        'Subject' => array(
            'columns' => 'cid',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns' => 'subid',
            'propertyName' => 'subject'
        ),
        'Room' => array(
            'columns' => 'rid',
            'refTableClass' => 'HM_Room_RoomTable',
            'refColumns' => 'rid',
            'propertyName' => 'room'
        )
    );// имя свойства текущей модели куда будут записываться модели зависимости

    

    public function getDefaultOrder()
    {
        return array('rooms2course.rid ASC');
    }
}