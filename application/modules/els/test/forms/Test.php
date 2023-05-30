<?php
class HM_Form_Test extends HM_Form
{
    private $themsUrl = '';
    
    public function setThemsUrl ($url)
    {
        $this->themsUrl = $url;
    }
    
    public function init()
    {
        
        $this->setMethod(Zend_Form::METHOD_POST);
       
        $this->setName('test');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('module' => 'test', 'controller' => 'abstract', 'action' => 'index', 'subject_id' => $this->getParam('subject_id', 0)), null, true)
        ));

        
        $subjectId = (int) $this->getParam('subject_id', 0);
        $testId    = (int) $this->getParam('test_id'   , 0);               
        $lessonId  = (int) $this->getParam('lesson_id' , 0);

        $this->addElement('text', 'title', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));
		// Text field for name translation			
		        $this->addElement('text', 'test-translate', array(
            'Label' => _('Перевод'),
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 0)
            ),
            'Filters' => array(
                'StripTags'
            )
			
        ));

        $this->addElement('radio', 'mode', array(
                                                    'Label' => _('Переключение между страницами теста'),
                                                    'required' => true,
                                                    'validators' => array('Int'),
                                                    'filters' => array('int'),
                                                    'multiOptions' => HM_Test_TestModel::getModes()
                                                ));
        
        $this->addElement('hidden', 'questions_by_theme');

        $this->addElement('ajaxRadioGroup', 'questions', array(
            'Label' => _('Способ выборки'),
            'required' => false,
            'multiOptions' => HM_Test_TestModel::getQuestionsByThemes(),
            'form' => $this,
             'dependences' => array(
                HM_Test_TestModel::QUESTIONS_BY_THEMES_SPECIFIED =>
                        $this->getView()->url(array('module'     => 'lesson', 
                        							'controller' => 'list', 
                        							'action'     => 'themes', 
                        							'test_id'    => $testId,
                                                    'lesson_id'  => $lessonId))
                        . "'"
            )
        ));

        $this->addElement('text', 'lim', array(
            'Label' => _('Количество вопросов из общего числа для включения в тест'),
            'required' => true,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Description' => _('При нулевом значении включаются все вопросы'),
            'Value' => 0
        ));

        $this->addElement('text', 'qty', array(
            'Label' => _('Количество вопросов для одновременного отображения на странице'),
            'required' => true,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(0))
            ),
            'filters' => array('int'),
            'Value' => 1
        ));

        $this->addElement('text', 'startlimit', array(
            'Label' => _('Количество попыток слушателю на прохождение теста'),
            'required' => true,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Value' => 1,
            'Description' => _('При нулевом значении количество попыток не ограничено')
        ));

        $this->addElement('text', 'limitclean', array(
            'Label' => _('Количество дней, после которых обнуляется счетчик попыток'),
            'required' => true,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Value' => 0,
            'Description' => _('При нулевом значении счетчик никогда не обнуляется')
        ));

        $this->addElement('text', 'timelimit', array(
            'Label' => _('Время (в минутах) на прохождение теста'),
            'required' => true,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Value' => 0,
            'Description' => _('При нулевом значении время не ограничено')
        ));
        
        $this->addElement('text', 'threshold', array(
            'Label' => _('Пороговое значение прохождения'),
            'required' => true,
            'validators' => array(
                'Int',
                array('GreaterThan', false, array(-1))
            ),
            'filters' => array('int'),
            'Value' => 75
        ));

        $this->addElement('checkbox', 'random', array(
            'Label' => _('Выбирать вопросы случайным образом'),
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => 1
        ));


        $this->addElement('checkbox', 'endres', array(
            'Label' => _('По окончании отображать результат тестирования'),
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => 1
        ));

        $this->addElement('checkbox', 'skip', array(
            'Label' => _('Разрешить досрочное завершение теста с получением оценки'),
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int')
        ));

        $this->addElement('checkbox', 'allow_view_log', array(
            'Label' => _('Разрешить слушателю просмотр подробного отчета'),
            'required' => false,
            'validators' => array('Int'),
            'filters' => array('int'),
            'value' => 1
        ));


        $this->addDisplayGroup(
            array('title',
			'test-translate'),
            'testGroup1',
            array('legend' => _('Общие свойства'))
        );
        
        $this->addDisplayGroup(array(
                'lim',
                'questions',
                'random',
        ),
            'questionSelect',
            array('legend' => _('Выборка вопросов'))
        );

        $this->addDisplayGroup(array(
                'startlimit',
                'timelimit',
        		'limitclean',
                'threshold',
        		'mode',
                'skip',
            ),
            'progress',
            array('legend' => _('Режим прохождения'))
        );
        $this->addDisplayGroup(array(
                'qty',
                'endres',
                'allow_view_log'
            ),
            'view',
            array('legend' => _('Режим отображения'))
        );
        
        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));
        
        $this->getElement('questions_by_theme')->setIsArray(TRUE);
        
         parent::init(); // required!
    }
}