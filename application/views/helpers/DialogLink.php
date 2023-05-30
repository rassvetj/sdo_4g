<?php

class HM_View_Helper_DialogLink extends HM_View_Helper_Abstract
{

    public function dialogLink($title, $content, $linkText = '', $options = NULL)
    {
        $this->view->title  = $title;
        $this->view->linkText = ($linkText)? $linkText : $title;
        $this->view->content = $content;

        $this->view->dialogOptions = '';
        $this->view->dialogButtons = '';
        if ( is_array($options) ) {
            $this->view->dialogOptions .= (isset($options['width']))? ' ,width: ' . intval($options['width']): ''; 
            $this->view->dialogOptions .= (isset($options['height']))? ' ,height: ' . intval($options['height']): '';
            
            if (is_array($options['buttons'])) {
                $buttons = array();
                foreach ($options['buttons'] as $title=>$code) {
                    $buttons[] = "{text: '$title', click: function(){{$code}}}";
                }
                $this->view->dialogButtons = ',buttons:[' . implode(',', $buttons) . ']';
            }
        }
        return $this->view->render('dialoglink.tpl');
    }
}