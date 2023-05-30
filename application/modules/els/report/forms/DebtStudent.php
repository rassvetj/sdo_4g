<?php
class HM_Form_DebtStudent extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
		$this->setAction($this->getView()->url(array('module' => 'report', 'controller' => 'debt-student', 'action' => 'get')));
        $this->setName('news');
		
		$this->addElement('Submit', 'submit', array('Label' => _('Сформировать')));

        parent::init(); // required!
	}

}