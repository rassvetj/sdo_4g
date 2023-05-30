<?php
class HM_View_Helper_ReportList extends HM_View_Helper_Abstract
{
    const CLASS_BRIEF = 'brief';
    const CLASS_NORMAL = 'normal';
    
    public function reportList($data, $class = self::CLASS_NORMAL)
    {
        $this->view->data = $data;
        $this->view->class = $class;
        return $this->view->render('report-list.tpl');
    }
}