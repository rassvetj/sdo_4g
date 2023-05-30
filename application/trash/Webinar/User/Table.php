<?php
class Webinar_User_Table extends HM_Db_Table {
    protected $_name = 'webinar_users';
    protected $_primary = array('pointId', 'userId');
    protected $_rowClass = 'Webinar_User';    

    protected $_dependentTables = array();

    public function getDefaultOrder() {
        //return array('People.LastName ASC', 'People.FirstName ASC', 'People.Patronymic ASC');
    }
}