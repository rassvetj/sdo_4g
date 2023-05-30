<?php

class HM_Form_CreateArticle extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAction($this->getView()->url(array('module' => 'wiki', 'controller' => 'index', 'action' => 'new')));
     
        $this->addElement(new Zend_Form_Element_Text('title', array(
            'Label' => _('Название страницы'),
            'Required' => true
        )));
            
        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        $this->addDisplayGroup(
            array(
                'title',
                'submit'
            ),
            'articleGroup',
            array('legend' => _('Страница'))
        );
        
        parent::init(); // required!
    }
}