<?php
class HM_StudyGroup_StudyGroupTable extends HM_Db_Table
{
	protected $_name = 'study_groups';
	protected $_primary = 'group_id';

    protected $_dependentTables = array(
        'HM_StudyGroup_Users_UsersTable');

    protected $_referenceMap = array(
        'StudyGroup_Users' =>array(
            'columns'       => 'group_id',
            'refTableClass' => 'HM_StudyGroup_Users_UsersTable',
            'refColumns'    => 'group_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'studyGroup'
        ),
    );
}