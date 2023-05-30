<?php
class HM_View_Helper_ShowcaseBillet extends HM_View_Helper_Abstract
{

    public function showcaseBillet($name, $options = array(), $params = null,$attribs = null)
    {


        return $this->view->render('showcaseBillet.tpl');
    }


    
}