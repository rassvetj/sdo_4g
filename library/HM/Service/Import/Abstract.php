<?php
abstract class HM_Service_Import_Abstract extends HM_Service_Abstract
{
    protected $_options = null;

    public function setFileName($filename)
    {
        return $this->getMapper()->getAdapter()->setFileName($filename);
    }

    public function needToUploadFile()
    {
        return $this->getMapper()->getAdapter()->needToUploadFile();
    }

    public function setOptions($options)
    {
        $this->_options = $options;
        $this->getMapper()->getAdapter()->setOptions($options);
    }
}