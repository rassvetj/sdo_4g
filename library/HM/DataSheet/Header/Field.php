<?php
class HM_DataSheet_Header_Field
{
    private $_title = null;
    private $_render = null;
    private $_options = array();

    public function __construct($title = null, $render = null, $options = array())
    {
        $this->_title = $title;
        $this->_render = $render;
        $this->_options = $options;
    }

    public function setTitle($title)
    {
        $this->_title = $title;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function getRender()
    {
        return 'HM_DataSheet_Field_Render_' . ucfirst(strtolower($this->_render));
    }

    public function getOptions()
    {
        return $this->_options;
    }
}