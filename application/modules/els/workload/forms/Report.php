<?php
class HM_Form_Report extends HM_Form {
	
	protected $_currentLang	= 'rus'; 

	public function init(){

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('report');
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$this->_currentLang = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
		
		/*
		$action = $this->getRequest()->getActionName();		
		$this->setAction(
			$this->getView()->url(array(                
                'controller' => 'report',
                'action' => 'get-'.$action.'-report', 				
            ))
		);
		*/
		
		$this->addElement('checkbox', 'ignorePeriods',
			array(
				'Label' 	=> _('Не разделять на периоды'),
				'Required' 	=> false,								
				'Value' 	=> 0,
			)
		);
		
		$userService = $this->getService('User');
		$user_id = $userService->getCurrentUserId();
		
		$this->addElement('select', 'report_type_id', array(
			'Label' => _('Тип отчета'),
			'Required' => false,
			'multiOptions' => array(
									HM_Workload_WorkloadModel::REPORT_TYPE_CURRENT 	=> _('Текущие сессии'),
									HM_Workload_WorkloadModel::REPORT_TYPE_EXTENDED => _('Продленные сессии'),
									HM_Workload_WorkloadModel::REPORT_TYPE_ALL 		=> _('Текущие + продленные сессии'),
							),
			'Validators' => array('Int'),
			'Filters' => array('Int'),							
		));
		
		$this->addElement('select', 'type_do', array(
                'label' 		=> _('Тип'),
                'required' 		=> false,				
                'multiOptions' 	=> array(HM_Report_ReportModel::DO_ALL => _('Все')) + HM_Subject_SubjectModel::getFacultys(),
            )
        );
		
		/*
		$this->addElement('select', 'subject_type_id', array(
                'label' 		=> _('Выберите тип'),				
                'required' 		=> true,
                'multiOptions' 	=> HM_Subject_SubjectModel::getFacultys() + array(-1 => '--все--'),
            )
        );
		
		
		$this->addElement('select', 'chair_name', array(
                'label' 		=> _('Выберите кафедру'),
				'Description' 	=> _('Список всех кафедр, указанных в сессии и курсе'),
                'required' 		=> true,
                'multiOptions' 	=> array(0 => 'Все', -1 => '--Без кафедры--') + $this->getService('Subject')->getChairList(),
            )
        );
		
		$this->addElement('select', 'faculty_name', array(
                'label' 		=> _('Выберите факультет'),
				'Description' 	=> _('Список всех факультетов, указанных в сессии и курсе'),
                'required' 		=> true,
                'multiOptions' 	=> array(0 => 'Все', -1 => '--Без факультета--') + $this->getService('Subject')->getFacultyList(),
            )
        );
		*/


		
		if (in_array($userService->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TUTOR))) {
			$this->addElement('hidden', 'user_id', array(
                'Required' => true,
                'value' => $user_id,				
            ));
		} else {
			$isEnd = $this->getRequest()->getParam('end', false); //--для окончательного отчета. Значит, что берем только сессии, по которым ведомость передана			
			
			$tutors = $this->getService('Workload')->getListOrgstructurePersons($user_id, $isEnd);
			
			// echo '<pre>'; exit(var_dump($tutors));
			
			if($this->_currentLang == 'eng') {
				foreach($tutors as $key => $value) 
					$tutors[$key] = $this->translit($value);
			
				$tutors[-1] = 'All';		
			}			
			
			
			$this->addElement('select', 'user_id', array(
				'Label' => _('Тьюторы'),
				'Required' => false,
				'multiOptions' => $tutors,
				'Validators' => array('Int'),
				'Filters' => array('Int'),				
				'onChange' => 'changeForm()', //--jQuery ф-ция в tpl шаблоне
			));
		}


		$year = date('Y',time());
		$year = ( strtotime($year.'-09-01') > time() ) ? ($year - 1) : ($year); //--если текущая дата меньше 1 сентября, то надо взять прошлый год, т.е. у нас еще идет прошлогодний семестр.		
		
		$this->addElement('DatePicker', 'date_begin', array(
            'Label' => _('Дата начала'),
			'value' => '01.09.'.$year,
            'Required' => false,
            'Validators' => array(
                array(
                    'StringLength',
                false,
                array('min' => 10, 'max' => 50)
                )
            ),
            'Filters' => array('StripTags'),
            'JQueryParams' => array(
                'showOn' => 'button',
                'buttonImage' => "/images/icons/calendar.png",
                'buttonImageOnly' => 'true'
            ),			
        )
        );
		
		$this->addElement('DatePicker', 'date_end', array(
            'Label' => _('Дата окончания'),
            'Required' => false,
			'value' => date('d.m.Y'),
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
            ),
        )
        );
		
		if (!in_array($userService->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TUTOR))) {
			$this->addDisplayGroup(array(
				#'subject_type_id',			
				'user_id',
				#'chair_name',
				#'faculty_name',			
			),
				'period',
				array('legend' => _(''))
			);
		}
		
		$this->addDisplayGroup(array(			
			'report_type_id',
			'type_do',
            'date_begin',
            'date_end', 
			'ignorePeriods',
        ),
            'period_2',
            array('legend' => _('Параметры сессии'))
        );
		
        
        $this->addElement(
            'Submit',
            'submit',
            array(
				'Label' => _('Сформировать'),
				'order' => 99, //--чтобы кнопка была всегда последней
				'onClick' => 'sendForm(); return false;', //--jQuery ф-ция в tpl шаблоне
            ));
		
		parent::init();

    }

	//Транслит с русского на английски1
	public function translit($str='') {
		
		$cyrForTranslit = array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
						   'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
						   'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
						   'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'); 
		$latForTranslit = array('a','b','v','g','d','e','yo','zh','z','i','y','k','l','m','n','o','p',
							'r','s','t','u','f','h','ts','ch','sh','sch','','y','','ae','yu','ya',
							'A','B','V','G','D','E','Yo','Zh','Z','I','Y','K','L','M','N','O','P',
							'R','S','T','U','F','H','Ts','Ch','Sh','Sch','','Y','','Ae','Yu','Ya'); 
							
		return str_replace($cyrForTranslit, $latForTranslit, $str);
	}
	
}