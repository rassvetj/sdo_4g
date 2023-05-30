<?php

class HM_DataSheet_Actions
{
    private $_label = '';
    private $_actions = array();

    public function __construct($label, $actions = array())
    {
        $this->_label = $label;
        $this->_actions = $actions;
    }

    public function setLabel($label)
    {
        $this->_label = $label;
    }

    public function getLabel()
    {
        return $this->_label;
    }

    public function getActions()
    {
        if (count($this->_actions) && !isset($this->_actions['none'])) {
            $this->_actions = array_merge(array('none' => _('Не выбрано')), $this->_actions);
        }
        return $this->_actions;
    }

    public function addAction($title, $url)
    {
        $this->_actions[$url] = $title;
        return $this->_actions;
    }
}