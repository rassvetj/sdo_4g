<?php
class Infoblock_ScheduleDailyController extends HM_Controller_Action
{
    public function indexAction()
    {
        $begin = strtotime($this->_getParam('begin', false));

        if (!$begin) {
            $begin = time();
        }

        $end = $begin + 60*60*24;

        $this->view->begin = $begin;
        $this->view->end = $end;
        
    }
}