<?php
class HM_Form_Interviewlight extends HM_Form
{
	public function init()
	{
        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();

        $this->setMethod(Zend_Form::METHOD_POST);
		$this->setAttrib('id', 'target');
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('interview');

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
		$type = array();
		
        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(
            HM_Role_RoleModelAbstract::ROLE_TEACHER, HM_Role_RoleModelAbstract::ROLE_TUTOR 
        ))){
            $type = HM_Interview_InterviewModel::getTeacherLightTypes();			
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
            
			$this->addElement('radio', 'range_mark', array(
				'Label' 		=> _('Диапазон баллов'),
				'Description' 	=> _('Выберите требуемый диапазон, а затем укажите точное кол-во баллов в поле "Балл"'),
				'Required' 		=> false,				
				'MultiOptions' 	=> array(					
					1 => _('Неявка'),
				),				
				'separator' 	=> '&nbsp;',				
			));
			
			# по умолчанию все возможные варианты
			$listScales = HM_Interview_InterviewModel::getBallListScales();
			$scale = array(-1 => _('Выберите')) + $listScales[1];			
			
			$this->addElement('select', 'ball', array(
				'label' => _('Балл'),				
				'multiOptions' => $scale,
				'class' => 'bs_hidden',
				'validators' => array(
					'int',
					array('GreaterThan', false, array(-1))
				),			
			));
        }



        $this->addElement($this->getDefaultWysiwygElementName(), 'message', array(
            'Label' => 'Текст',            
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

        $this->addElement($this->getDefaultFileElementName(), 'files', array(
            'Label' => _('Файлы'),
            'Validators' => array(
                array('Count', false, 999),                
            ),
            'Destination' 		=> Zend_Registry::get('config')->path->upload->temp,
            'file_size_limit' 	=> 0,
            'file_types' 		=> '*.*',
            'file_upload_limit' => 999,
			'file_size_limit'	=> 52428800, # 50 Mb			
            'Required' 			=> false
        ));

		$this->addElement('Submit', 'button', array('Label' => _('Добавить'),'id'=>'interview'));

        $this->addDisplayGroup(
            array(
                'interview_id',
                'type',
            	'range_mark', # 4 бальная шкала
				'ball',	# 100 бальная шкала			
            	'files',
            	'message',
                'button'
            ),
            'importGroup',
            array('legend' => _('Добавить'))
        );

        parent::init(); // required!
	}

}
