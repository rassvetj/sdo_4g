<?php

class HM_Htmlpage_HtmlpageModel extends HM_Model_Abstract
{
    const ORDER_DEFAULT = 10;
    
    static public function getActionsPath()
    {
        return APPLICATION_PATH . '/../data/temp/actions_extended.xml';
    }    
}