<?php

abstract class HM_Adapter_Import_Abstract implements HM_Adapter_Import_Interface, HM_Adapter_Interface
{
    protected $_filename = null;
    protected $_options = null;

    public function setFileName($filename)
    {
        $this->_filename = $filename;
    }

    public function setOptions($options)
    {
        $this->_options = $options;
    }
}