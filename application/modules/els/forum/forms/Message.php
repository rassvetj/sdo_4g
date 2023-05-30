<?php

class HM_Form_Message extends HM_Form{
    
    public function init(){
        $router = Zend_Controller_Front::getInstance()->getRouter();
        $this->setAction($router->assemble(array()));
        $connectorURL = $router->assemble(array(
            'module'     => 'storage',
            'controller' => 'index',
            'action'     => 'elfinder',
            'subject'    => $this->getView()->subjectName,
            'subject_id' => $this->getView()->subjectId
        ), 'default', true);
        
        $this->setDecorators(array('FormElements', 'Form'));
        $this->setElementDecorators(array('UiWidgetElement', 'Label'));
        
        // Заголовок сообщения
        $this->addElement('text', 'title', array(
            'label'        => _('Заголовок') . ':',
            'id'           => 'topic-reply-' . mt_rand(0, 9999),
            'autocomplete' => 'off',
            'decorators'   => array(
                'ViewHelper',            
                array('HtmlTag', array('tag' => 'div', 'class' => 'topic-input')),
                'Label',
            )
        ));
        
        // Текст сообщения
        $this->addElement($this->getDefaultWysiwygElementName(), 'text', array(
            'label'        => _('Сообщение') . ':',
            'required'     => false,
            'validators'   => array( array('StringLength', 65535, 1) ),
            'filters'      => array('HtmlSanitizeRich'),
            'id'           => 'topic-reply-' . mt_rand(0, 9999),
            'connectorUrl' => $connectorURL,
            'width'        => '100%',
            'toolbar'      => 'hmToolbarTiny',
            'fmAllow'      => true,
            'setup'        => new Zend_Json_Expr('function (ed) { ed.onKeyUp.add( comment_editor_keyup_handler ); }'),
            'decorators'   => array(
                'UiWidgetElement',
                array('Label', array('class' => 'for-tinymce')),
            )
        ));

        $this->addElement(
            'checkbox',
            'is_hidden',
            array(
                'Label'       => _('Режим скрытого ответа'),
                'description' => _('Скрытое сообщение отображается только его автору, автору темы и автору сообщения на которое пишется ответ.'),
                'value'       => 0,
                'id'          => 'is_hidden_' . mt_rand(0,9999),
                'decorators'  =>array(
                    'ViewHelper',
                    'Label',
                )
            )

        );

        // Submit
        $this->addElement('submit', 'submit', array(
            'label'      => _('Отправить'),
            'class'      => 'ui-widget ui-button topic-reply',
            'decorators' => array(
               'ViewHelper',
                array('HtmlTag', array('tag' => 'div', 'class' => 'topic-replyeditor-buttons')),
            )
        ));
    }


    public function isValid($data)
    {
        if (!$data['title'] && !$data['text']) {
            //$this->getElement('text')->setRequired(true);
            return false;
        }
        return parent::isValid($data);
    }
}