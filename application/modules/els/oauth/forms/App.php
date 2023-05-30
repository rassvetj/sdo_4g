<?php
class HM_Form_App extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('app');
        //$this->setAction($this->getView()->url(array('module' => 'notice', 'controller' => 'index', 'action' => 'new')));

        $this->addElement('hidden', 'cancelUrl', array(
            'required' => false,
            'value' => $this->getView()->url(array('module' => 'oauth', 'controller' => 'app', 'action' => 'index'), NULL, TRUE)
        ));

        $this->addElement('hidden', 'app_id', array(
            'required' => false,
            'Filters' => array(
                'Int'
            )
        ));

        $this->addElement('text', 'title', array(
            'Required' => true,
            'Label' => _('Название'),
            'Validators' => array(
                array('StringLength',255,3)
            )
        ));

        $this->addElement($this->getDefaultWysiwygElementName(), 'description', array(
            'Label' => _('Описание'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',4096,3)
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

        $this->addElement('text', 'callback_url', array(
            'Required' => true,
            'Label' => _('Callback URL'),
            'Validators' => array(
                array('StringLength',255,8)
            )
        ));


		$this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        $this->addDisplayGroup(
            array(
                'app_id',
                'cancelUrl',
                'title',
            	'description',
                'callback_url',
                'submit'
            ),
            'appGroup',
            array('legend' => _('Общие свойства'))
        );

        parent::init(); // required!
	}

}