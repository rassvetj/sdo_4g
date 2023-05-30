<?php

class HM_Resource_Reader
{
    
    protected $_adapter;
    
    public function __construct($filePath, $fileName)
    {
        
        $pathParts = pathinfo($fileName);
        if(file_exists(dirname(__FILE__) . '/Adapter/' . ucfirst($pathParts['extension']) . '.php') && file_exists($filePath))
        {
            $class = 'HM_Resource_Adapter_' . ucfirst($pathParts['extension']);
            $adapter = new $class(array('file' => $filePath));
            $this->_adapter = $adapter;
        }else{
            $this->_adapter = Null;
        }
    }
    
    
    public function readFile()
    {
        if($this->_adapter != Null){
            $this->_adapter->readFile();
        }else{
            return false;
        }
    }
    
    
}