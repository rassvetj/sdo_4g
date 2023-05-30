<?php
class HM_Form_Mark extends HM_Form
{
	public function init()
	{
        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('id', 'target');
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('mark');

        $this->addElement('hidden', 'interview_id', array(
            'value' => 0,
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'Required' => true
        ));
        
        $this->setAction(
            $this->getView()->url(
                array(
                    'module' => 'interview',
                    'controller' => 'index',
                    'action' => 'change-mark',
                    'referer_redirect' => 1
                )
            )
        );
        
		/*
		$this->addElement('select','ball', array( 
			'label' => _('Оценка'),
			'value' => '1', 
			'multiOptions' => array(
				'1' => _('1'),
				'2' => _('2'),
				'3' => _('3'),
				'4' => _('4'),
				'5' => _('5'),				
			),
		));
		*/
		
		$this->addElement('hidden', 'is_change_mark', array(
            'value' => 1,          
        ));
		
		
		
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
				'separator' 	=> '&nbsp;',				
			));
			
			# по умолчанию все возможные варианты
			$listScales = HM_Interview_InterviewModel::getBallListScales();		
			$scale = array(-1 => _('Выберите')) + $listScales[5] + $listScales[4] + $listScales[3] + $listScales[2] + $listScales[1];			
			
			$this->addElement('select', 'ball', array(
				'label' => _('Балл'),			
				'multiOptions' => $scale,
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
            'filters' => array(array('Int'),),
            'Validators' => array(
                'Int',
                array('GreaterThan', false, array(-1)),
                array('LessThan', false, array(101))
            ),
        ));
		*/

        $this->addElement('Submit', 'button', array(
			'Label' 	=> _('Сохранить'),
			'id'		=>'interview',
			'disabled' 	=> 'disabled',
		));

        $this->addDisplayGroup(
            array(
                'interview_id',
            	'range_mark',
            	'ball',
                'button'
            ),
            'mark',
            array('legend' => _('Изменить оценку'))
        );

        parent::init(); // required!
	}

}
