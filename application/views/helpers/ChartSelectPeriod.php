<?php
class HM_View_Helper_ChartSelectPeriod extends HM_View_Helper_Abstract {

    public function chartSelectPeriod($periodSet, $periodSelected) 
    {
        $this->session = new Zend_Session_Namespace();

        $this->view->periodDefault = $periodSelected;
        $this->view->periods = HM_Date::pluralFormsPeriods($periodSet);

        return $this->view->render('chart-select-period.tpl');
    }
}
