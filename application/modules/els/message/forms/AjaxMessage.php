<?php
class HM_Form_AjaxMessage extends HM_Form
{
	public function init()
	{
	    $defaultNS = new Zend_Session_Namespace('default');
	    $front = Zend_Controller_Front::getInstance();
        $this->setMethod(Zend_Form::METHOD_POST);

        $this->setName('resource');
        $this->setAction($this->getView()->url());



        $this->addElement($this->getDefaultWysiwygElementName(), 'message', array(
            'Label' => _('Сообщение'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',255,3)
            )
        ));
        

		$this->addElement('Submit', 'submit', array('Label' => _('Отправить')));


        parent::init(); // required!
	}

}