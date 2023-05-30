<?php
class HM_Form_Interview extends HM_Form
{
	public function init()
	{
        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();

        $this->setMethod(Zend_Form::METHOD_POST);
		$this->setAttrib('id', 'subject_interview');
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('subject_interview');

        
		$this->setAction(
			$this->getView()->url(
				array(
					'module' 			=> 'subject',
					'controller' 		=> 'interview',
					'action' 			=> 'create',
					'referer_redirect'	=> 1
				)
			)
		);
	
		
        $currentRole = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole();

        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)){
            $type = HM_Interview_InterviewModel::MESSAGE_TYPE_QUESTION;
        }else{
			$type = HM_Interview_InterviewModel::MESSAGE_TYPE_ANSWER;
        }


        $this->addElement('hidden', 'interview_id', array(
            'value' 	 => 0,
            'Validators' => array('Int'),
            'Filters'    => array('Int'),
            'Required' 	 => true
        ));

		$this->addElement('hidden', 'type', array(
			'value'		=> $type,				
			'Required'	=> true
		));
				

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

        $this->addElement($this->getDefaultFileElementName(), 'files', array(
            'Label' => _('Файлы'),
            'Validators' => array(
                array('Count', false, 999),
                //array('Extension', false, '')
            ),
            'Destination' => Zend_Registry::get('config')->path->upload->temp,
            'file_size_limit' => 0,
            'file_types' => '*.*',
            'file_upload_limit' => 999,
            'Required' => false
        ));

		$this->addElement('Submit', 'button', array('Label' => _('Добавить'),'id'=>'interview'));

        $this->addDisplayGroup(
            array(
                'interview_id',
                'type',				
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
