<?php
class HM_Form_StudentSubjects extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('student_subjects');
		$this->setAction($this->getView()->url(array('module' => 'report', 'controller' => 'student-subjects', 'action' => 'get')));
		
        $config = new HM_Report_Config();
		
		

        $this->addElement('select', 'group_id', array(
                'label' 		=> _('Выберите группу'),				
                'required' 		=> false,
				'multiple'		=> true,
                'multiOptions' 	=> $this->getService('StudyGroup')->getGroupList(),
            )
        );
		
		
		$this->addElement('select', 'student_id', array(
                'label' 		=> _('Выберите студента'),				
                'required' 		=> false,
				'multiple'		=> true,
                #'multiOptions' 	=> array(),
            )
        );
		
		
		
		$this->addDisplayGroup(array(
				'group_id',
				'student_id',
			),
			'base',
            array('legend' => _(''))
        );
				
        $this->addElement('Submit', 'submit', array('Label' => _('Сформировать')));

        parent::init(); // required!
	}

}