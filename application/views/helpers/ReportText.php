<?php
class HM_View_Helper_ReportText extends HM_View_Helper_Abstract
{
    public function reportText($data, $title = '')
    {
        if (empty($data)) return '';
        
        $this->view->data = trim($data);
        $this->view->title = trim($title);
        return $this->view->render('report-text.tpl');
    }
}