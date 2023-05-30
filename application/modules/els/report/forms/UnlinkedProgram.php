<?php
class HM_Form_UnlinkedProgram extends HM_Form
{
	
	private $_serviceProgramm 	= null;
	private $_serviceGroup 		= null;
	
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('form-unlinked-program');
		$this->setAction($this->getView()->url(array('module' => 'report', 'controller' => 'unlinked-program', 'action' => 'get')));
		
        $config = new HM_Report_Config();
		
		if(!$this->_serviceProgramm){ $this->_serviceProgramm	= $this->getService('Programm'); 	}
		if(!$this->_serviceGroup)	{ $this->_serviceGroup 		= $this->getService('StudyGroup');	}

        $this->addElement('select', 'status_subject', array(
                'label' 		=> _('Сессия (без учета даты продления)'),
                'required' 		=> false,				
                'multiOptions' 	=> HM_Report_ReportModel::getTimeStatuses(),
            )
        );
		
		$this->addElement('select', 'student_semester', array(
                'label' 		=> _('Семестр студента'),
                'required' 		=> false,				
                'multiOptions' 	=> array('' => _('Все')) + HM_Report_ReportModel::getSemesters(),
            )
        );
		
		$this->addElement('select', 'subject_semester', array(
                'label' 		=> _('Семестр сессии'),
                'required' 		=> false,				
                'multiOptions' 	=> array('' => _('Все')) + HM_Report_ReportModel::getSemesters(),
            )
        );
		
		$this->addElement('select', 'student_activity', array(
                'label' 		=> _('Активность студента в сессии'),
                'required' 		=> false,				
                'multiOptions' 	=> HM_Report_ReportModel::getActivity(),
            )
        );
		
		
		$this->addElement('select', 'student_groups[]', array(
                'label' 		=> _('Учебные группы студентов'),
                'required' 		=> false,				
                'multiOptions' 	=> array('' => _('Все')) + $this->_serviceGroup->getGroupList(),
				'multiple'		=> 'multiple',
            )
        );
		
		$this->addElement('select', 'subject_programs[]', array(
				'label' 		=> _('Программы сессий'),
				'required' 		=> false,				
				'multiOptions' 	=> array('' => _('Все')) + $this->_serviceProgramm->getProgrammList(),
				'multiple'		=> 'multiple',
			)
        );
		
        $this->addElement('Submit', 'submit', array('Label' => _('Сформировать')));

        parent::init(); // required!
	}

}