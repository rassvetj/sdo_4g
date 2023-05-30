<?php
class HM_Form_Unchecked extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('unchecked-form');
		$this->setAction($this->getView()->url(array('module' => 'report', 'controller' => 'unchecked-works', 'action' => 'get')));
		
        $config = new HM_Report_Config();

        $this->addElement('select', 'tutor_id', array(
                'label' 		=> _('Выберите тьютора'),
				'Description' 	=> _('Условия отбора сессий:<br>1. ДО<br>2. Есть непроверенные работы'),
                'required' 		=> true,
                'multiOptions' 	=> $this->getService('Report')->getTutorList(),
            )
        );
		
		
		$this->addElement('select', 'status_time', array(
                'label' 		=> _(''),
                'required' 		=> false,				
                'multiOptions' 	=> array('' => _('Все')) + HM_Report_ReportModel::getTimeStatuses(),
            )
        );
		
		$this->addElement('select', 'status_debt', array(
                'label' 		=> _(''),
                'required' 		=> false,				
                'multiOptions' 	=> array('' => _('Все')) + HM_Report_ReportModel::getDebtStatuses(),
            )
        );
		
		
		$this->addDisplayGroup(array(
            'tutor_id',                                
        ),
            'base',
            array('legend' => _(''))
        );
				
		$this->addDisplayGroup(array(
            'status_time',                    
            'status_debt',                    
        ),
            'session',
            array('legend' => _('Параметры сессии'))
        );
		
        $this->addElement('Submit', 'submit', array('Label' => _('Сформировать')));

        parent::init(); // required!
	}

}