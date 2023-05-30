<?php
class HM_Form_Ball extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('ball-form');
		$this->setAction($this->getView()->url(array('module' => 'report', 'controller' => 'ball', 'action' => 'get')));
		
        $config = new HM_Report_Config();
		
		$type_list = HM_Subject_SubjectModel::getFacultys();
		asort($type_list);
		$this->addElement('select', 'subject_type_id', array(
                'label' 		=> _('Выберите тип'),				
                'required' 		=> true,
                'multiOptions' 	=> $type_list + array(-1 => 'Все'),
            )
        );
		
		$chair_list = $this->getService('Subject')->getChairList();
		asort($chair_list);
		$this->addElement('select', 'chair_name', array(
                'label' 		=> _('Выберите кафедру'),
				'Description' 	=> _('Список всех кафедр, указанных в сессии и курсе'),
                'required' 		=> true,
                'multiOptions' 	=> array(0 => 'Все', -1 => '--Без кафедры--') + $chair_list,
            )
        );
		
		$faculty_list = $this->getService('Subject')->getFacultyList();
		asort($faculty_list);
		$this->addElement('select', 'faculty_name', array(
                'label' 		=> _('Выберите факультет'),
				'Description' 	=> _('Список всех факультетов, указанных в сессии и курсе'),
                'required' 		=> true,
                'multiOptions' 	=> array(0 => 'Все', -1 => '--Без факультета--') + $faculty_list,
            )
        );
		
		$tutor_list = $this->getService('Report')->getTutorList();
		asort($tutor_list);
        $this->addElement('select', 'tutor_id', array(
                'label' 		=> _('Выберите тьютора'),
				'Description' 	=> _('Условия отбора сессий:<br>1. Есть назначенный тьютор<br>2. Есть непроверенные работы'),
                'required' 		=> true,
                'multiOptions' 	=> array('' => 'Все') + $tutor_list,
            )
        );
		
		
		
		
		$this->addElement('DatePicker', 'subject_begin', array(
            'Label' => _('Дата начала сессии больше или равна, чем'),
            'Required' => false,
			'Value'		 => date('01.09.2016'),
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
		
		
		
		$this->addDisplayGroup(array(
            'subject_type_id',
			'tutor_id',
			'chair_name',
			'faculty_name',
			'faculty_id',
			'subject_begin',
        ),
            'base',
            array('legend' => _(''))
        );
			
		
        $this->addElement('Submit', 'submit', array('Label' => _('Сформировать')));

        parent::init(); // required!
	}

}