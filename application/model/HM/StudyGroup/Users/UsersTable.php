<?php
class HM_StudyGroup_Users_UsersTable extends HM_Db_Table
{
	protected $_name = "study_groups_users";
	protected $_primary = array(
        'group_id',
	    'user_id'
    );

    protected $_dependentTables = array(
        'HM_User_UserTable',
        'HM_StudyGroup_StudyGroupTable'
    );

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'users' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'StudyGroup' => array(
            'columns'       => 'group_id',
            'refTableClass' => 'HM_StudyGroup_StudyGroupTable',
            'refColumns'    => 'group_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'groups' // имя свойства текущей модели куда будут записываться модели зависимости
        )
    );
}