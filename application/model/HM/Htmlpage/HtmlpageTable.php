<?php

class HM_Htmlpage_HtmlpageTable extends HM_Db_Table
{
    protected $_name = "htmlpage";
    protected $_primary = "page_id";
    protected $_sequence = "S_100_1_HTMLPAGE";

    protected $_dependentTables = array('HM_Htmlpage_Group_GroupTable');
    
    protected $_referenceMap = array(
        'HtmlPage_Group' => array(
            'columns'       => 'group_id',
            'refTableClass' => 'HM_Htmlpage_Group_GroupTable',
            'propertyName'  => 'group'
        )
    );
    
    public function getDefaultOrder()
    {
        return array('htmlpage.name ASC');
    }
}