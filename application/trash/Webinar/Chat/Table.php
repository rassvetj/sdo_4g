<?php
class Webinar_Chat_Table extends HM_Db_Table {
    protected $_name = 'webinar_chat';
    protected $_primary = array('id');
    protected $_rowClass = 'Webinar_Chat_Message';

    protected $_dependentTables = array();

    public function getDefaultOrder() {
        //return array('People.LastName ASC', 'People.FirstName ASC', 'People.Patronymic ASC');
    }
}