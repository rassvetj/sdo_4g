<?php
class HM_Form_UncheckedNew extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('unchecked-form');
		$this->setAction($this->getView()->url(array('module' => 'report', 'controller' => 'unchecked-works-new', 'action' => 'get')));
		
        $config = new HM_Report_Config();
		
		
		
		

        $this->addElement('select', 'tutor_id', array(
                'label' 		=> _('Выберите тьютора'),
				'Description' 	=> _('Условия отбора сессий:<br>1. ДО<br>2. Есть непроверенные работы'),
                'required' 		=> true,
                'multiOptions' 	=> $this->getService('Report')->getTutorList(),
            )
        );
		
		
		$this->addElement('select', 'faculty_name', array(
                'label' 		=> _('Выберите факультет сессии'),				
                'required' 		=> false,
                'multiOptions' 	=> array('' => _('Все'), HM_Report_ReportModel::FACULTY_EMPTY => _('-Без факультета-')) + $this->getService('Subject')->getFacultyList(),
            )
        );
		
		
		$this->addElement('select', 'chair_name', array(
                'label' 		=> _('Выберите кафедру сессии'),				
                'required' 		=> false,
                'multiOptions' 	=> array('' => _('Все'), HM_Report_ReportModel::CHAIR_EMPTY => _('-Без кафедры-')) + $this->getService('Subject')->getChairList(),
            )
        );
		
		
		$this->addElement('select', 'type_do', array(
                'label' 		=> _('Тип'),
                'required' 		=> false,				
                'multiOptions' 	=> array(HM_Report_ReportModel::DO_ALL => _('Все')) + HM_Subject_SubjectModel::getFacultys(),
            )
        );
		
		
		$this->addElement('select', 'status_time', array(
                'label' 		=> _('Время'),
                'required' 		=> false,				
                'multiOptions' 	=> array('' => _('Все')) + HM_Report_ReportModel::getTimeStatuses(),
            )
        );
		
		$this->addElement('select', 'status_debt', array(
                'label' 		=> _('Продление'),
                'required' 		=> false,				
                'multiOptions' 	=> array('' => _('Все')) + HM_Report_ReportModel::getDebtStatuses(),
            )
        );
		
		
		
		$this->addElement('DatePicker', 'date_end_from', array(
            'Label' 		=> _('Дата окончания (с)'),
			'value' 		=> '',
            'Required' 		=> false,
            'Validators' 	=> array( array('StringLength', false, array('min' => 10, 'max' => 50) ) ),
            'Filters' 	 	=> array('StripTags'),
            'JQueryParams' 	=> array(
                'showOn' 			=> 'button',
                'buttonImage' 		=> "/images/icons/calendar.png",
                'buttonImageOnly'	=> 'true'
            ),			
        ));
		
		$this->addElement('DatePicker', 'date_end_to', array(
            'Label' 		=> _('Дата окончания (по)'),
			'value' 		=> '',
            'Required' 		=> false,
            'Validators' 	=> array( array('StringLength', false, array('min' => 10, 'max' => 50) ) ),
            'Filters' 	 	=> array('StripTags'),
            'JQueryParams' 	=> array(
                'showOn' 			=> 'button',
                'buttonImage' 		=> "/images/icons/calendar.png",
                'buttonImageOnly'	=> 'true'
            ),			
        ));
		
		
		
		
		
		
		$this->addDisplayGroup(array(
            'tutor_id',                                
            'faculty_name',                                
            'chair_name',                                
        ),
            'base',
            array('legend' => _(''))
        );
				
		$this->addDisplayGroup(array(
            'type_do',                    
            'status_time',                    
            'status_debt',                    
            'date_end_from',                    
            'date_end_to',                    
        ),
            'session',
            array('legend' => _('Параметры сессии'))
        );
		
        $this->addElement('Submit', 'submit', array('Label' => _('Сформировать')));

        parent::init(); // required!
	}

}