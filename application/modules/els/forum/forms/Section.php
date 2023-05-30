<?php

class HM_Form_Section extends HM_Form{
    
    public function init(){

        $this->addElement('hidden','section_id', array('value' => 0, 'Required' => false));
        $this->addElement('hidden','cancelUrl', array('value' => $_SERVER['HTTP_REFERER'], 'Required' => false));

        $this->addElement('text', 'title', array(
            'label'        => _('Название').':',
            'required'     => true,
            'autocomplete' => 'off',
            'validators'   => array(
                array('StringLength', false, array('min' => 3, 'max' => 255))
            )
        ));
        
        $this->addDisplayGroup(array('title', 'section_id'), 'content', array('legend' => _('Добавить категорию')));
        
        $this->addElement('Submit', 'submit', array('Label' => _('Добавить')));
        
        parent::init();
    }
    
}