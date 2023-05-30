<?php

class HM_View_Helper_HM extends HM_View_Helper_Abstract
{
    public function HM()
    {
        return $this;
    }

    public function create($className, $config = array())
    {
        $view = $this->view;
        $view->inlineScript()->captureStart();
        echo 'HM.create('.json_encode($className).', '.json_encode($config).');';
        $view->inlineScript()->captureEnd();
    }

}