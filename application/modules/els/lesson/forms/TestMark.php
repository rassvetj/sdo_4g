<?php
class HM_Form_TestMark extends HM_Form
{
	public function init()
	{
        $front = Zend_Controller_Front::getInstance();
        $request = $front->getRequest();

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('id', 'form_test_mark');
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('form_test_mark');

        $this->addElement('hidden', 'student_id', array(
            'value' 		=> 0,
            'Validators' 	=> array('Int'),
            'Filters' 		=> array('Int'),
            'Required' 		=> true,
        ));
        
        $this->setAction(
            $this->getView()->url(
                array(
                    'module' 			=> 'lesson',
                    'controller' 		=> 'test',
                    'action' 			=> 'change-mark',                    
                )
            )
        );
        
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
			'label' 			=> _('Балл'),			
			'multiOptions' 		=> $scale,
			'validators' 		=> array(
                'int',
                array('GreaterThan', false, array(-1))
            ),			
		));	
		
		
        $this->addElement('Submit', 'button', array(
			'Label' 	=> _('Сохранить'),			
			'disabled' 	=> 'disabled',
		));

        $this->addDisplayGroup(
            array(
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
