<?php

class HM_Question_Need_NeedTable extends HM_Db_Table
{
    protected $_name = "testneed";
    protected $_primary = array('tid','kod');

    protected $_referenceMap = array(
       
    );// имя свойства текущей модели куда будут записываться модели зависимости

    

    public function getDefaultOrder()
    {
        return array('testneed.kod ASC');
    }
}