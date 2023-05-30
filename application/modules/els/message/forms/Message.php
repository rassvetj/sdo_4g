<?php
class HM_Form_Message extends HM_Form
{
	public function init()
	{
	    $defaultNS = new Zend_Session_Namespace('default');
	    $front = Zend_Controller_Front::getInstance();
        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        //$this->setAttrib('onSubmit', "select_list_select_all('list2');");
        $this->setName('resource');
        $this->setAction($this->getView()->url());

        $this->addElement('hidden', 'cancelUrl', array(
            'required' => false,
            'value' => $defaultNS->message_referer_page
        ));

        $this->addElement('hidden', 'users', array(
            'Required' => true,
            'Validators' => array(),
            'Filters' => array()
        ));

        $this->addElement('hidden', 'subject', array(
            'required' => false,
            'filters' => array(
                'StripTags'
            )
        ));

        $this->addElement('hidden', 'subject_id', array(
            'required' => false,
            'filters' => array(
                'Int'
            )
        ));


        $this->addElement($this->getDefaultWysiwygElementName(), 'message', array(
            'Label' => _('Сообщение'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',255,3)
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));
        
        //$this->getElement('message')->addFilter(new HM_Filter_Utf8());

		$this->addElement('Submit', 'submit', array('Label' => _('Отправить')));

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'subject',
                'subject_id',
                'users',
                'message',
                'submit'
            ),
            'messageGroup',
            array('legend' => _('Сообщение'))
        );

        parent::init(); // required!
	}

}