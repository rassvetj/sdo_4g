<?php
class HM_StudyGroup_Programm_ProgrammTable extends HM_Db_Table
{
	protected $_name = "study_groups_programms";
	protected $_primary = array(
        'group_id',
        'programm_id'
    );
}