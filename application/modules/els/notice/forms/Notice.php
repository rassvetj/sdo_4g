<?php
class HM_Form_Notice extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('notice');
        //$this->setAction($this->getView()->url(array('module' => 'notice', 'controller' => 'index', 'action' => 'new')));

        $this->addElement('hidden', 'cancelUrl', array(
            'required' => false,
            'value' => $this->getView()->url(array('module' => 'notice', 'controller' => 'index', 'action' => 'index'),NULL,TRUE)
        ));

        $this->addElement('hidden', 'id', array(
            'required' => false,
            'Filters' => array(
                'Int'
            )
        ));
        
        $this->addElement('text', 'event', array(
            'Label' => _('Событие'),
            'Validators' => array(
                array('StringLength',255,3)
            ),
            'disable' => true
        ));
                
		        $this->addElement('text', 'event_translation', array(
            'Label' => _('Событие').(' (en)'),
            'Validators' => array(
                array('StringLength',255,3)
            ),
            //'disable' => true
        ));
		
        $this->addElement('select', 'receiver', array(
            'Label' => _('Адресат'),
            'MultiOptions' => HM_Notice_NoticeModel::getReceivers(),
            'Validators' => array(
                'Int',
                array('GreaterThan', false, array('min' => 0)) 
            ),
            'disable' => true
        ));
        
        $this->addElement('text', 'title', array(
            'Label' => _('Тема сообщения'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',255,3)
            )
        ));
		
        $this->addElement('text', 'title_translation', array(
            'Label' => _('Тема сообщения').(' (en)'),
            'Required' => false,
            'Validators' => array(
                array('StringLength',255,3)
            )
        ));

       
        $this->addElement($this->getDefaultWysiwygElementName(), 'message', array(
            'Label' => _('Текст сообщения'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',4096,3)
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));
       
        $this->addElement($this->getDefaultWysiwygElementName(), 'message_translation', array(
            'Label' => _('Текст сообщения').(' (en)'),
            'Required' => false,
            'Validators' => array(
                array('StringLength',4096,3)
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

     
        $this->addElement('checkbox', 'enabled', array(
            'Label' => _('Активно'),
            'Required' => false,
            'Validators' => array(
                array('StringLength',255,1)
            )
        ));


		$this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        $this->addDisplayGroup(
            array(
                'id',
                'cancelUrl',
                'event',
				'event_translation',
            	'receiver',
                'title',
				'title_translation',
                'message',
				'message_translation',
                'enabled',
                'submit'
            ),
            'noticeGroup',
            array('legend' => _('Общие свойства'))
        );

        parent::init(); // required!
	}
}