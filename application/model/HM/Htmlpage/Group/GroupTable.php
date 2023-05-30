<?php

class HM_Htmlpage_Group_GroupTable extends HM_Db_Table_NestedSet
{
    protected $_name = "htmlpage_groups";
    protected $_left = 'lft';	
    protected $_right = 'rgt';
    protected $_level = 'level';
    protected $_primary = "group_id";
    protected $_sequence = "S_100_1_HTMLPAGE_GROUPS";

    protected $_dependentTables = array("HM_Htmlpage_HtmlpageTable");

    protected $_referenceMap = array(
        'Htmlpage' => array(
            'columns'       => 'group_id',
            'refTableClass' => 'HM_Htmlpage_HtmlpageTable',
            'refColumns'    => 'group_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'pages' // имя свойства текущей модели куда будут записываться модели зависимости
        )
    );

    public function getDefaultOrder()
    {
        return array('htmlpage_groups.name ASC');
    }
}