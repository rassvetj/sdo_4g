<?php

class HM_Message_MessageTable extends HM_Db_Table
{
    protected $_name = "messages";
    protected $_primary = "message_id";
    protected $_sequence = "S_100_1_MESSAGES";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'Author' => array(
            'columns'       => 'from',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'authors' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'User' => array(
            'columns'       => 'to',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'users' // имя свойства текущей модели куда будут записываться модели зависимости
        )

    );

    public function getDefaultOrder()
    {
        return array('messages.created DESC');
    }
}