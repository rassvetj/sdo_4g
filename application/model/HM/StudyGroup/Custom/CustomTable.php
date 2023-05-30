<?php
class HM_StudyGroup_Custom_CustomTable extends HM_Db_Table
{
	protected $_name = "study_groups_custom";
	protected $_primary = array(
        'group_id',
	    'user_id'
    );
}