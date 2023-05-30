<?php
class HM_DataSheet_Field_Render_Abstract implements HM_DataSheet_Field_Render_Interface
{
    const DEFAULT_FIELD_NAME = 'data';

    protected $_value;
    protected $_options;
    protected $_hId;
    protected $_vId;

    public function __construct($value, $options = array(), $hId, $vId)
    {
        $this->_value = (string) $value;
        $this->_options = $options;
        $this->_hId = $hId;
        $this->_vId = $vId;
    }

    public function render()
    {
        return $this->_value;
    }

    public function setHorizontalId($hId)
    {
        $this->_hId = $hId;
    }

    public function getHorizontalId()
    {
        return $this->_hId;
    }

    public function setVerticalId($vId)
    {
        $this->_vId = $vId;
    }

    public function getVerticalId()
    {
        return $this->_vId;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function getOptions()
    {
        return $this->_options;
    }
}