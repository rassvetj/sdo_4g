<?php

class HM_Activity_Resource_ResourceTable extends HM_Db_Table
{
    protected $_name = "activity_resources";
    protected $_primary = array('resource_id', 'activity_id', 'activity_type');
    
    protected $_referenceMap = array(
        'TagRef' => array(
            'columns'       => 'resource_id',
            'refTableClass' => 'HM_Tag_Ref_RefTable',
            'refColumns'    => 'item_id',
            'propertyName'  => 'tagRefs' // ВНИМАНИЕ!!! коллекцию нужно еще отфильтровать по item_type!
        )
    );
}