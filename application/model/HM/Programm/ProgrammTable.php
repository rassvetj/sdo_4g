<?php
class HM_Programm_ProgrammTable extends HM_Db_Table
{
	protected $_name = "programm";
    protected $_primary = "programm_id";

    protected $_sequence = "S_45_1_PROGRAMM";

    protected $_referenceMap = array(
        'ProgrammEvents' => array(
            'columns' => 'programm_id',
            'refTableClass' => 'HM_Programm_Event_EventTable',
            'refColumns' => 'programm_id',
            'onDelete' => self::CASCADE,
            'propertyName' => 'programm_events'),
		
		'ProgrammUser' => array(
			'columns'       => 'programm_id',
            'refTableClass' => 'HM_Programm_User_UserTable',
            'refColumns'    => 'programm_id',            
            'propertyName'  => 'users'
		),
    );

}