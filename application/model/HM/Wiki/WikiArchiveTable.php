<?php

class HM_Wiki_WikiArchiveTable extends HM_Db_Table
{
    protected $_name = 'wiki_archive';
    protected $_primary = 'id';
    protected $_sequence = 'S_ID_WIKI_ARCHIVE';
    
    protected $_referenceMap = array(
        'Author' => array(
            'columns' => 'author',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns' => 'MID',
            'propertyName' => 'author'
        )
    );
}