<?php
class HM_Form_News extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
		$this->setAction($this->getView()->url(array('module' => 'report', 'controller' => 'news', 'action' => 'get')));
        $this->setName('news');
		
		
		if(!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TUTOR)){
		
		
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
					'multiOptions' 	=> array('0' => _('Все'), HM_Report_ReportModel::FACULTY_EMPTY => _('-Без факультета-')) + $this->getService('Subject')->getFacultyList(),
				)
			);
			
			
			$this->addElement('select', 'chair_name', array(
					'label' 		=> _('Выберите кафедру сессии'),				
					'required' 		=> false,
					'multiOptions' 	=> array('0' => _('Все'), HM_Report_ReportModel::CHAIR_EMPTY => _('-Без кафедры-')) + $this->getService('Subject')->getChairList(),
				)
			);
			
			
			$this->addElement('select', 'type_subject', array(
					'label' 		=> _('Выберите тип сессии'),				
					'required' 		=> false,
					'multiOptions' 	=> array('0' => _('Все')) + HM_Report_ReportModel::getReportTypes(),
				)
			);
			
			$this->addElement('select', 'type_do', array(
					'label' 		=> _('Выберите форму обучения'),				
					'required' 		=> false,
					'multiOptions' 	=> array('-1' => _('Все')) + HM_Subject_SubjectModel::getFacultys(),
				)
			);
			
			
			


			
			
			
			$this->addElement('DatePicker', 'subject_end', array(
				'Label' => _('Дата окончания сессии больше или равна, чем'),
				'Required' => false,
				'Value'		 => date('d.m.Y'), #date('29.08.2016'),
				'Validators' => array(
					array(
						'StringLength',
					false,
					array('min' => 10, 'max' => 50)
					),
					array(
						'DateGreaterThanFormValue',
						false,
						array('name' => 'begin')
					)
				),
				'Filters' => array('StripTags'),
				'JQueryParams' => array(
					'showOn' => 'button',
					'buttonImage' => "/images/icons/calendar.png",
					'buttonImageOnly' => 'true'
				)
			)
			);
			
			
			$this->addElement('checkbox', 'role_lector',
				array(
					'Label'			=> _('Только роль "лектор"'),
					'Required' 		=> false,
					'Validators' 	=> array(),
					'Filters' 		=> array('StripTags'),
					'Value' 		=> 1,
				)
			);
		
		}
        
        $this->addElement('Submit', 'submit', array('Label' => _('Сформировать')));

        parent::init(); // required!
	}

}