<?php
class HM_Form_Base extends HM_Form
{
	public function init()
	{
        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('id', 'journal');
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('journal');

        
        $this->setAction(
            $this->getView()->url(
                array(
                    'module' 			=> 'journal',
                    'controller' 		=> 'storage',
                    'action' 			=> 'save',
                    'referer_redirect' => 1
                )
            )
        );
        
		
		$this->addElement('hidden', 'journal_type', array(                        
			'value' => HM_Event_EventModel::TYPE_JOURNAL_LECTURE,						
        ));
		
		$listScales = HM_Interview_InterviewModel::getBallListScales();		
		$scale = array(
			-1 						=> _('Выберите'),
			'Отлично' 				=> $listScales[5],
			'Хорошо' 				=> $listScales[4],
			'Удовлетворительно' 	=> $listScales[3],
			'Неудовлетворительно' 	=> $listScales[2],
			'Неявка' 				=> $listScales[1],
		);
		
		$scalePromotion = array(
			-1	=> _('Выберите'),
			0	=> 0,
			1	=> 1,
			2	=> 2,
			3	=> 3,
			4	=> 4,
			5	=> 5,
			6	=> 6,
			7	=> 7,
			8	=> 8,
			9	=> 9,
			10	=> 10,
		);
		
		$this->addElement('select', 'promotion', array(
			'label' 		=> _('Поощрение'),			
			'disabled'		=> 'disabled',
			'multiOptions' 	=> $scalePromotion,
			'validators' 	=> array(
                'int',
                array('GreaterThan', false, array(-1))
            ),			
		));
		
		$this->addElement('select', 'ball', array(
			'label' 		=> _('Балл'),			
			'disabled'		=> 'disabled',
			'multiOptions' 	=> $scale,
			'validators' 	=> array(
                'int',
                array('GreaterThan', false, array(-1))
            ),			
		));

		$this->addElement('checkbox', 'isBe', array(
			'Label' 		=> _('Присутствует'),			
			'required' 		=> false,
			'validators' 	=> array('Int'),
			'filters' 		=> array('int'),
			'value' 		=> 0,
			'disabled'		=> 'disabled',
		));	

		$this->addElement('select', 'format_attendance', array(
			'label' 		=> _('Формат присутствия'),			
			'multiOptions' 	=> HM_Lesson_Journal_Result_ResultModel::getFormatAttendanceList(),
			'value'			=> HM_Lesson_Journal_Result_ResultModel::FORMAT_ATTENDANCE_ONLINE,
			'validators' 	=> array('int'),
		));		
		
		
		
		$this->addElement('DatePicker', 'day', array(
            'Label' 		=> _(''),
            'Required' 		=> false,
			'disabled'		=> 'disabled',
			'value'			=> date('d.m.Y'),
            'Validators' 	=> array(
                array(
                    'StringLength',
                false,
                array('min' => 10, 'max' => 50)
                )
            ),
            'Filters' 		=> array('StripTags'),
            'JQueryParams' 	=> array(
                'showOn' 			=> 'button',
                'buttonImage' 		=> "/images/icons/calendar.png",
                'buttonImageOnly' 	=> 'true'
            ),			
        ));
		
		
	
        $this->addElement('Submit', 'button', array(
			'Label' 	=> _('Сохранить'),
			'id'		=> 'journal',			
		));

       

        parent::init(); // required!
	}

}
