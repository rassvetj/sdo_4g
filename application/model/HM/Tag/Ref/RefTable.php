<?php

class HM_Tag_Ref_RefTable extends HM_Db_Table
{
    protected $_name = "tag_ref";
    protected $_primary = array('tag_id', 'item_type', 'item_id');

    protected $_referenceMap = array(
        'Tag' => array(
            'columns'       => 'tag_id',
            'refTableClass' => 'HM_Tag_TagTable',
            'refColumns'    => 'id',
            'propertyName'  => 'tag'
        ),
        'Blog' => array(
            'columns'       => 'item_id',
            'refTableClass' => 'HM_Blog_BlogTable',
            'refColumns'    => 'id',
            'propertyName'  => 'blog'
        ),
        'Resource' => array(
            'columns'       => 'item_id',
            'refTableClass' => 'HM_Resource_ResourceTable',
            'refColumns'    => 'resource_id',
            'propertyName'  => 'resource'
        ),
        'Course' => array(
            'columns'       => 'item_id',
            'refTableClass' => 'HM_Course_CourseTable',
            'refColumns'    => 'CID',
            'propertyName'  => 'course'
        ),
        'BlogComments' => array(
            'columns' => 'item_id',
            'refTableClass' => 'HM_Comment_CommentTable',
            'refColumns' => 'item_id',
            'propertyName' => 'comments'
        )
    );
}
