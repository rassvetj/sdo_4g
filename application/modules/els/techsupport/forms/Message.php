<?php
class HM_Form_Message extends HM_Form
{
    public function init() {
        $this->setMethod(Zend_Form::METHOD_POST);
        
        $this->setName('support_message');
       
        $this->addElement('hidden', 'cancelUrl', array(
            'required' => false,
            'value' => $defaultNS->message_referer_page
        ));
        
        $this->addElement('hidden', 'support_request_id', array(
            'required' => false,
            'filters' => array(
                'Int'
            ),
            'value' => $this->getParam('user_id', 0)
        ));


        $this->addElement($this->getDefaultWysiwygElementName(), 'message', array(
            'Label' => _('Сообщение'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',255,3)
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

        $this->addElement('select', 'status', array('Label' => _('Статус'),
            'Required' => true,
            'Validators' => array(),
            'Filters' => array('StripTags'),
            'multiOptions' => HM_Techsupport_TechsupportModel::getStatuses(),
        ));
        
        $this->addElement('Submit', 'submit', array('Label' => _('Отправить')));
        
        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'support_request_id',
                'status',
                'message',
                'submit'
            ),
            'messageGroup',
            array('legend' => _('Сообщение'))
        );

        parent::init(); // required!
    }

}