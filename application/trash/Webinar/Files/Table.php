<?php
class Webinar_Files_Table extends HM_Db_Table {
    protected $_name = 'files';
    protected $_primary = array('file_id');
    protected $_rowClass = 'Webinar_Files_Item';

    protected $_dependentTables = array();

}
