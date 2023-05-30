<?php
class Infoblock_FeedbackController extends HM_Controller_Action
{
    public function indexAction()
    {
        $begin = strtotime($this->_getParam('begin', false));
        $end = strtotime($this->_getParam('end', false));

        if (!$begin) {
            $begin = time();
        }

        if (!$end) {
            $end = time() + 28 * 60*60*24;
        }

        $this->view->begin = $begin;
        $this->view->end = $end;

    }
}