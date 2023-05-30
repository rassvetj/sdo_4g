<?php

class HM_Form_Answer extends HM_Form_Message{
    
    public function init(){
        parent::init();
        
        $submit = $this->getElement('submit');
        $submit->setDescription(_("Отменить"));
        $submit->setAttribs(array('class' => 'ui-widget ui-button topic-comment-reply'));
        $submit->setDecorators(array(
            array('Description', array('tag' => 'span', 'class' => '')),
            array(array('cancel' => 'HtmlTag'), array('tag' => 'a', 'class' => 'ui-widget ui-button topic-comment-replycancel', 'href' => '#')),
            'ViewHelper',
            array(array('wrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'topic-comment-replyeditor-buttons')),
        ));
    }
    
}