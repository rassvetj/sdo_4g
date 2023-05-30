<?php
class HM_File_FileModel extends HM_Model_Abstract
{
    private $_id;
    private $_path;
    private $_url;
    private $_displayName;
    
    public function __construct($options)
    {
        $this->_id = $options['id'];
        $this->_path = $options['path'];
        $this->_url = $options['url'];
        $this->_displayName = $options['displayName'];
    }
    
    public function getId()
    {
        return $this->_id;
    }
    
    public function getDisplayName()
    {
        return $this->_displayName;
    }
    
    public function getPath()
    {
        return $this->_path;
    }
    
    public function getFileName()
    {
    }
    
    public function getUrl()
    {
        return $this->_url;
    }
}