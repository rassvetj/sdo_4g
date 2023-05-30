<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';

class HM_View_Infoblock_TopsubjectsBlock extends HM_View_Infoblock_ScreenForm
{
    protected $id = 'topsubjects';

    public function topsubjectsBlock($title = null, $attribs = null, $options = null)
    {
        $period = HM_Date::getCurrendPeriod(HM_Date::PERIOD_YEAR_CURRENT);
//		$this->view->begin = $period['begin']->toString(HM_Locale_Format::getDateFormat());
//		$this->view->end = $period['end']->toString(HM_Locale_Format::getDateFormat());
		$this->view->begin = $period['begin']->toString('dd.MM.yyyy');
		$this->view->end = $period['end']->toString('dd.MM.yyyy');

		$content = $this->view->render('topsubjectsBlock.tpl');
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/topsubjects/style.css');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/topsubjects/script.js');

        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
    }
}