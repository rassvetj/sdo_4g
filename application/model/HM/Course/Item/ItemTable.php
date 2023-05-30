<?php

class HM_Course_Item_ItemTable extends HM_Db_Table
{
    protected $_name = "organizations";
    protected $_primary = "oid";
    protected $_sequence = "S_44_1_ORGANIZATIONS";

    protected $_dependentTables = array('HM_Course_Item_History_HistoryTable',
                                        'HM_Course_Item_Current_CurrentTable');

    protected $_referenceMap = array(
        'Course' => array(
            'columns'       => 'cid',
            'refTableClass' => 'HM_Course_CourseTable',
            'refColumns'    => 'CID',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'course' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Module' => array(
            'columns'       => 'module',
            'refTableClass' => 'HM_Library_Item_ItemTable',
            'refColumns'    => 'bid',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'module' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'itemCurrent' => array(
            'columns'       => 'oid',
            'refTableClass' => 'HM_Course_Item_Current_CurrentTable',
            'refColumns'    => 'current',
        	'onDelete'      => self::CASCADE,
            'propertyName'  => 'itemCurrent'
        ),
        'itemHistory' => array(
            'columns'       => 'oid',
            'refTableClass' => 'HM_Course_Item_History_HistoryTable',
            'refColumns'    => 'item',
        	'onDelete'      => self::CASCADE,
            'propertyName'  => 'itemHistory'
        )

    );

    public function getDefaultOrder()
    {
        return array('organizations.title ASC');
    }
}