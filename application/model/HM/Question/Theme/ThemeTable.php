<?php

class HM_Question_Theme_ThemeTable extends HM_Db_Table
{
    protected $_name = "testquestions";
    protected $_primary = array('tid','cid');

    protected $_referenceMap = array(
       
    );// имя свойства текущей модели куда будут записываться модели зависимости

    

    public function getDefaultOrder()
    {
        return array('testquestions.tid ASC');
    }
}