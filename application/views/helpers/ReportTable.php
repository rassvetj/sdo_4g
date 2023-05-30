<?php
class HM_View_Helper_ReportTable extends HM_View_Helper_Abstract
{
    public function reportTable($data, $title = '')
    {
        $this->view->data = $data;
        $this->view->title = $title;
        return $this->view->render('report-table.tpl');
    }
}