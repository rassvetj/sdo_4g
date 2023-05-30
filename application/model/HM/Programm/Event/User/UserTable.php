<?php
class HM_Programm_Event_User_UserTable extends HM_Db_Table
{
	protected $_name = "programm_events_users";
    protected $_primary = array("programm_event_id", 'user_id', 'item_id', 'begin_date', 'end_date');

}