<?php

class HM_Resource_Adapter_Abstract
{
    protected $_file;
    
    public function __construct($params)
    {
        $this->_file = $params['file'];
    }
    
    
    public function readFile()
    {
        readfile($this->_file);
    }
    
}