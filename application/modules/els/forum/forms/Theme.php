<?php

class HM_Form_Theme extends HM_Form{
    
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
        
        // Название темы
        $this->addElement('text', 'title', array(
            'label'        => _('Название темы') . ":",
            'id'           => 'topic-reply-' . mt_rand(0, 9999),
            'required'     => true,
            'autocomplete' => 'off',
            'validators'   => array( array('StringLength', 65535, 1) ),
            'decorators'   => array(
                'ViewHelper',            
                array('HtmlTag', array('tag' => 'div', 'class' => 'topic-input')),
                'Label',
            )
        ));
        
        // Текст темы
        $this->addElement($this->getDefaultWysiwygElementName(), 'text', array(
            'label'        => _('Текст') . ':',
            'required'     => false,
            'validators'   => array( array('StringLength', 65535, 0) ),
            'filters'      => array('HtmlSanitizeRich'),
            'id'           => 'topic-reply-' . mt_rand(0, 9999),            
            'connectorUrl' => $connectorURL,            
            'width'        => '100%',
            'toolbar'      => 'hmToolbarTiny',
            'fmAllow'      => true,
            'decorators'   => array(
                'UiWidgetElement',
                array('Label', array('class' => 'for-tinymce')),
            )
        ));
        
        // Submit
        $this->addElement('submit', 'submit', array(
            'label'       => _('Отправить'),
            'description' => _('Отменить'),
            'class'       => 'ui-widget ui-button topic-create',
            'decorators'  => array(
                array('Description', array('tag' => 'span', 'class' => '')),
                array(array('cancel' => 'HtmlTag'), array('tag' => 'a', 'class' => 'ui-widget ui-button topic-create-cancel', 'href' => '#')),
                'ViewHelper',
                array(array('wrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'topic-createeditor-buttons')),
            )
        ));
    }
    
}