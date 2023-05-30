<?php
class HM_StudyGroup_Auto_AutoTable extends HM_Db_Table
{
	protected $_name = "study_groups_auto";
	protected $_primary = array(
        'group_id',
	    'position_code',
	    'department_id'
    );
}