<?php
class HM_Form_Interview extends HM_Form
{
	public function init()
	{
        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();

        $this->setMethod(Zend_Form::METHOD_POST);
		$this->setAttrib('id', 'target');
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('interview');
		
	$request = Zend_Controller_Front::getInstance()->getRequest();
	#$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
        if ($this->isAjaxRequest()) {
            $this->setAction(
                $this->getView()->url(
                    array(
                        'module' => $request->getModuleName(),
                        'controller' => $request->getControllerName(),
                        'action' => $request->getActionName(),
                        'referer_redirect' => 1
                    )
                )
            );
        }
		
        $currentRole = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole();

        if(
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)
            //$currentRole == HM_Role_RoleModelAbstract::ROLE_STUDENT
        ){
            $type = HM_Interview_InterviewModel::getStudentTypes();
        }elseif($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(
            HM_Role_RoleModelAbstract::ROLE_TEACHER, HM_Role_RoleModelAbstract::ROLE_TUTOR 
        ))){
            $type = HM_Interview_InterviewModel::getTeacherTypes();
			
			if(!$this->getService('Subject')->isAvailableSubject($this->getParam('subject_id', 0), $this->getParam('user_id', 0))){ # сессия более недоступна для студента.
				$type = HM_Interview_InterviewModel::getPastTeacherTypes();
			}			
        } else {
            $type = array();
        }


        $this->addElement('hidden', 'interview_id', array(
            'value' => 0,
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'Required' => true
        ));

		if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(
            HM_Role_RoleModelAbstract::ROLE_STUDENT 
        ))){
			$this->addElement('hidden', 'type', array(
				'value' => HM_Interview_InterviewModel::MESSAGE_TYPE_TEST,				
				'Required' => true
			));
		} else {
			$this->addElement('select', 'type', array(
				'label' => _('Тип сообщения'),
				'filters' => array(array('Int')),
				'multiOptions' => $type,
			));
		}

        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(
            HM_Role_RoleModelAbstract::ROLE_TEACHER, HM_Role_RoleModelAbstract::ROLE_TUTOR 
        ))){
		
		$serviceLesson 	= Zend_Registry::get('serviceContainer')->getService('Lesson');
		$lesson_id		= $this->getParam('lesson_id', 0);
		$lesson			= $serviceLesson->getOne($serviceLesson->find($lesson_id));
			
		if($lesson->typeID == HM_Event_EventModel::TYPE_LANGUAGE){
			$listScales = HM_Interview_InterviewModel::getLanguageBallListScales();
			$scale = array(-1 => _('Выберите')) + $listScales;
			
			$this->addElement('select', 'ball', array(
				'label' => _('Уровень'),			
				'multiOptions' => $scale,
				'class' => 'bs_hidden',
				'validators' => array(
					'int',
					array('GreaterThan', false, array(-1))
				),			
			));	
		} else {
			
			
			$this->addElement('radio', 'range_mark', array(
				'Label' 		=> _('Диапазон баллов'),
				'Description' 	=> _('Выберите требуемый диапазон, а затем укажите точное кол-во баллов в поле "Балл"'),
				'Required' 		=> false,				
				'MultiOptions' 	=> array(
					5 => _('Отлично'),
					4 => _('Хорошо'),
					3 => _('Удовлетворительно'),
					2 => _('Неудовлетворительно'),
					1 => _('Неявка'),
				),
				#'Validators' 	=> array('Int'),
				#'Filters' 		=> array('Int'),
				'separator' 	=> '&nbsp;',				
			));
			
			# по умолчанию все возможные варианты
			$listScales = HM_Interview_InterviewModel::getBallListScales();
			$scale = array(-1 => _('Выберите')) + $listScales[5] + $listScales[4] + $listScales[3] + $listScales[2] + $listScales[1];			
			
			$this->addElement('select', 'ball', array(
				'label' => _('Балл'),
				#'filters' => array(array('Int')),
				'multiOptions' => $scale,
				'class' => 'bs_hidden',
				'validators' => array(
					'int',
					array('GreaterThan', false, array(-1))
				),			
			));		
		}
			
			/*
			$this->addElement('text', 'ball', array(
                'label' => _('Оценка'),
                'value'=>'',
                'disabled'=>'disabled',
                'filters' => array(array('Int'),),
                'Validators' => array(
                    'Int',
                    array('GreaterThan', false, array(-1)),
                    array('LessThan', false, array(101))
                    ),
            ));
			*/
			
        }

	# Руки оторвать за такое.
	# К успеху шел, не получилось
	/*
	if ($lng == 'eng') {        
		$this->addElement($this->getDefaultWysiwygElementName(), 'message_en', array(
            'Label' => _('Текст').' (en)',
            //'Required' => true,
            'Validators' => array(
                array(
                    'validator' => 'StringLength',
                    'options' => array('min' => 3)
            )),
            'Filters' 	=> array('HtmlSanitizeRich'),
            'style' 	=> 'width:100%; height:100px',
            'toolbar' 	=> 'hmToolbarMidi',
            'fmAllow' 	=> true,
			'height'	=> '200',
			'theme_advanced_resizing'	=> true,
       ));	}
	   else{
	*/
        $this->addElement($this->getDefaultWysiwygElementName(), 'message', array(
            'Label' => _('Текст'),
            //'Required' => true,
            'Validators' => array(
                array(
                    'validator' => 'StringLength',
                    'options' => array('min' => 3)
            )),
            'Filters' 	=> array('HtmlSanitizeRich'),
            'style' 	=> 'width:100%; height:100px',
            'toolbar' 	=> 'hmToolbarMidi',
            'fmAllow' 	=> true,
			'height'	=> '200',
			'theme_advanced_resizing'	=> true,
       ));
	/*}*/
	/*   
   	if ($lng == 'eng') { 
        $this->addElement($this->getDefaultFileElementName(), 'files_en', array(
            'Label' => _('Файлы').' (en)',
            'Validators' => array(
                array('Count', false, 999),
                //array('Extension', false, '')
            ),
            'Destination' 		=> Zend_Registry::get('config')->path->upload->temp,
            'file_size_limit' 	=> 0,
            'file_types' 		=> '*.*',
            'file_upload_limit' => 999,
			'file_size_limit'	=> 52428800, # 50 Mb			
            'Required' 			=> false
        ));	}
	   else{
	*/		   

        $this->addElement($this->getDefaultFileElementName(), 'files', array(
            'Label' => _('Файлы'),
            'Validators' => array(
                array('Count', false, 999),
                //array('Extension', false, '')
            ),
            'Destination' 		=> Zend_Registry::get('config')->path->upload->temp,
            'file_size_limit' 	=> 0,
            'file_types' 		=> '*.*',
            'file_upload_limit' => 999,
			'file_size_limit'	=> 52428800, # 50 Mb			
            'Required' 			=> false
        ));
	   /*}*/

		$this->addElement('Submit', 'button', array('Label' => _('Добавить'),'id'=>'interview'));

        $this->addDisplayGroup(
            array(
                'interview_id',
                'type',
            	'range_mark', # 4 бальная шкала
				'ball',	# 100 бальная шкала			
            	'files',
            	'files_en',
            	'message',
            	'message_en',
                'button'
            ),
            'importGroup',
            array('legend' => _('Добавить'))
        );

        parent::init(); // required!
	}

}
