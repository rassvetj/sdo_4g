<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';

class HM_View_Infoblock_ActivitydevBlock extends HM_View_Infoblock_ScreenForm
{
    protected $id = 'activitydev';
    protected $session;

    const TYPE_TIMES 	= 'times';
    const TYPE_DATES	= 'dates';

    public $periodSet = array(
    	HM_Date::PERIOD_WEEK_CURRENT,
    	HM_Date::PERIOD_WEEK_PREVIOUS,
    	HM_Date::PERIOD_MONTH_CURRENT,
    	HM_Date::PERIOD_MONTH_PREVIOUS,
    	HM_Date::PERIOD_4WEEKS_RELATIVE,
    );

    public function activitydevBlock($title = null, $attribs = null, $options = null)
    {
		$this->session = new Zend_Session_Namespace('infoblock_activitydev');
		$this->_setDefaults();

        $this->view->periodSet = $this->periodSet;

		$this->view->period = $this->session->period;
		$this->view->type	= $this->session->type;

    	$content = $this->view->render('activitydevBlock.tpl');
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/activitydev/style.css');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/activitydev/script.js');

        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
    }

    private function _setDefaults()
    {
		if (!isset($this->session->period)) {
			$this->session->period = HM_Date::PERIOD_WEEK_CURRENT;
		}
		if (!isset($this->session->type)) {
			$this->session->type = 'times';
		}
    }
}