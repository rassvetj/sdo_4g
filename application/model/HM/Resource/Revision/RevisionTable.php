<?php

class HM_Resource_Revision_RevisionTable extends HM_Db_Table
{
    protected $_name = "resource_revisions";
    protected $_primary = "revision_id";
    protected $_sequence = "S_100_1_RESOURCE_REVISIONS";

    protected $_referenceMap = array(
        'Resource' => array(
            'columns'       => 'resource_id',
            'refTableClass' => 'HM_Resource_ResourceTable',
            'refColumns'    => 'resource_id',
            'propertyName'  => 'resource'
        ),
        'DependentRevision' => array(
            'columns'       => 'revision_id',
            'refTableClass' => 'HM_Resource_ResourceTable',
            'refColumns'    => 'parent_revision_id',
            'propertyName'  => 'dependentRevisions'
        ),
    );
}