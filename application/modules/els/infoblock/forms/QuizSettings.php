<?php
class HM_Form_QuizSettings extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('quiz-settings');
        $this->setAction($this->getView()->url());

        $this->addElement('hidden', 'cancelUrl', array(
            'required' => false,
            'value' => $this->getView()->url(array('module' => 'default', 'controller' => 'index', 'action' => 'index'))
        ));

        $this->addElement('hidden', 'getQuestionsUrl', array(
            'required' => false,
            'value' => $this->getView()->url(array('module' => 'infoblock', 'controller' => 'quizzes', 'action' => 'get-questions'))
        ));

		$this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        $collection = $this->getService('Poll')->fetchAll('subject_id = 0 AND status = 1');
        $tests = $collection->getList('quiz_id', 'title', _('Выберите опрос'));

        $this->addElement('select', 'quiz_id', array(
            'Label' => _('Опрос'),
            'required' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => "Необходимо выбрать значение из списка")))
            ),
            'filters' => array('int'),
            'multiOptions' => $tests
        ));

        $this->addElement('select', 'question_id', array(
            'Label' => _('Вопрос'),
            'required' => true,
            'validators' => array(
                'int',
                array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => "Необходимо выбрать значение из списка")))
            ),
            'filters' => array('int'),
            'multiOptions' => array(_('Выберите вопрос'))
        ));

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'getQuestionsUrl',
                'quiz_id',
                'question_id',
                'submit',
            ),
            'QuizGroup',
            array('legend' => _('Настройки блока опросов'))
        );

        parent::init(); // required!
	}
}