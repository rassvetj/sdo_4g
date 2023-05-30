<?php
class HM_Controller_Action_Chart extends HM_Controller_Action
{
	protected $chartType;

	public function init()
	{
		parent::init();

        $this->_helper->layout()->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		$this->chartType = $this->getRequest()->getControllerName();

        $title = $this->chartType . '_' . date('Y-m-d_H-i');
		$this->_helper->ContextSwitch()->addContext(
            'csv',
            array(
                'suffix' => 'csv',
                'headers' => array(
                	'Content-Type' => 'text/csv',
	                'Content-Disposition' => "attachment; filename=\"{$title}.csv\"",
            	),
            )
        );
        header('Pragma: cache'); // интеллигентныый способ не работает.(
//		$this->_helper->ContextSwitch()->setHeader('xml', 'Pragma',	'cache'); // fixing amcharts over sll bug

		$this->_helper->ContextSwitch()->addActionContext('get-settings', 'xml')->initContext();
		$this->_helper->ContextSwitch()->addActionContext('get-data', array('xml', 'csv'))->initContext();
	}

	public function getSettingsAction()
	{
	}

}