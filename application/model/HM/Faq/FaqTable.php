<?php

class HM_Faq_FaqTable extends HM_Db_Table
{
    protected $_name = "faq";
    protected $_primary = 'faq_id';
    protected $_sequence = 'S_25_1_FAQ';

/*
    protected $_dependentTables = array("HM_Webinar_Files_FilesTable");

    protected $_referenceMap = array(
        'Webinar' => array(
            'columns'       => 'file_id',
            'refTableClass' => 'HM_Webinar_Files_FilesTable',
            'refColumns'    => 'file_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'webinars' // имя свойства текущей модели куда будут записываться модели зависимости
        )
    );
*/
    public function getDefaultOrder()
    {
        return array('faq.faq_id');
    }
}