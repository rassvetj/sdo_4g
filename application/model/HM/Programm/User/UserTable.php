<?php
class HM_Programm_User_UserTable extends HM_Db_Table
{
    protected $_name    = "programm_users";
    protected $_primary = array("programm_id","user_id");
	
	
    protected $_referenceMap = array(
        'Programm' => array(
            'columns' 		=> 'programm_id',
            'refTableClass' => 'HM_Programm_ProgrammTable',
            'refColumns' 	=> 'programm_id',
            'propertyName' 	=> 'programms',
		),
    );

}