<?php

class HM_Webinar_User_UserTable extends HM_Db_Table
{
    protected $_name = "webinar_users";
    protected $_primary = array('pointId' ,'userId');

    protected $_referenceMap = array(
        'User' => array(
           'columns'       => 'userId',
           'refTableClass' => 'HM_User_UserTable',
           'refColumns'    => 'MID',
           'propertyName'  => 'users'
        )
    );
    
    
    public function getDefaultOrder()
    {
        return array('webinar.userId');
    }
}