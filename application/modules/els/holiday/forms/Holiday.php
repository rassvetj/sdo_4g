<?php
class HM_Form_Holiday extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('holiday');
        //$this->setAction($this->getView()->url(array('module' => 'holiday', 'controller' => 'index', 'action' => 'new')));

        $this->addElement('hidden', 'cancelUrl', array(
            'required' => false,
            'value' => $this->getView()->url(array('module' => 'holiday', 'controller' => 'index', 'action' => 'index'),NULL,TRUE)
        ));

        $this->addElement('hidden', 'id', array(
            'required' => false,
            'Filters' => array(
                'Int'
            )
        ));

        $this->addElement(new HM_Form_Element_FcbkComplete('user_id', array(
                'Label' => _('Пользователь'),
                'Description' => _('Для поиска можно вводить любое сочетание букв из фамилии, имени и отчества, после ввода слова нажать &laquo;Enter&raquo;'),
                'json_url' => $this->getView()->url(array('module' => 'user', 'controller' => 'ajax', 'action' => 'users-list'), null, true),
                'newel' => false,
                'maxitems' => 1
            )
        ));

        $this->addElement('DatePicker', 'date', array(
            'Label' => _('Дата'),
            'Required' => true,
            'Validators' => array(
                array(
                    'StringLength',
                    50,
                    10
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

        $this->addElement('text', 'title', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',255,3)
            )
        ));

		$this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        $this->addDisplayGroup(
            array(
                'id',
                'cancelUrl',
                'user_id',
                'date',
            	'title',
                'submit'
            ),
            'holidayGroup',
            array('legend' => _('Общие свойства'))
        );

        parent::init(); // required!
	}

}