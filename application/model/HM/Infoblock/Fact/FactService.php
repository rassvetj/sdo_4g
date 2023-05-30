<?php

class HM_Infoblock_Fact_FactService  extends HM_Service_Abstract
{

    public function getRandomFact()
    {
        // Функция рандомной строки в каждой ДБ разная( Подумать как лучше
        $res = $this->fetchAll(array('status = ?' => HM_Infoblock_Fact_FactModel::STATUS_PUBLISHED));
        $val = $res->asArray();
        pr($val);
        $res = array_rand($val);
        return $val[$res];      
    }



}