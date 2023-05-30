<?php

abstract class HM_Adapter_Csv_Abstract implements HM_Adapter_Interface
{
    private $_filename = null;

    protected $_length = 0;
    protected $_delimiter = ';';
    protected $_enclosure = '"';
    protected $_skipLines = 0;

    public function __construct($filename = null, $options = null)
    {
        $this->_filename = $filename;
        $this->setOptions($options);
    }

    public function setOptions($options = null)
    {
        if (null !== $options) {
            if (isset($options['length'])) {
                $this->_length = $options['length'];
            }
            if (isset($options['delimiter'])) {
                $this->_delimiter = $options['delimiter'];
            }
            if (isset($options['enclosure'])) {
                $this->_enclosure = $options['enclosure'];
            }
            if (isset($options['skipLines'])) {
                $this->_skipLines = $options['skipLines'];
            }
        }
    }

    public function setFileName($filename)
    {
        $this->_filename = $filename;
    }

    public function needToUploadFile()
    {
        return true;
    }

    private function _checkFile()
    {
        if (null === $this->_filename) {
            throw new HM_Exception(_('Не указан файл с данными'));
        }
        if (!file_exists($this->_filename)) {
            throw new HM_Exception(sprintf(_("Файл с данными '%s' не найден"), basename($this->_filename)));
        }
        if (!is_readable($this->_filename)) {
            throw new HM_Exception(sprintf(_("Файл с данными '%s' недоступен для чтения"), basename($this->_filename)));
        }

        return true;
    }

    public function getMappingArray()
    {
        return false;
    }

    public function getFormatData($name,$resUserFunc='')
    {
        $map = array();
        switch($name['format']){
            case 'array':
                $map = $resUserFunc;
                break;
            case 'integer':
                $resUserFunc = (int)$resUserFunc;
                break;
            default:
                break;
        }

        if(is_array($resUserFunc)&&count($resUserFunc)){
            return $map;
        }else{
            if(isset($name['field']))
                $map[$name['field']] = $resUserFunc;
            else
                $map[$name[0]] = $resUserFunc;
        }
        return $map;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $result = array();
        if ($this->_checkFile()) {
            $counter = 0;
            Zend_Registry::get('serviceContainer')->getService('Unmanaged')->detectFileEncoding($this->_filename);
            if ($fh = fopen($this->_filename, 'r')) {
                $mapping = $this->getMappingArray();
                
             while(($data = fgetcsv($fh, $this->_length, $this->_delimiter, $this->_enclosure)) !== false) {
// 	            for ($i=0; $row=fgets($fh,1000); $i++){
// 					$data=explode($this->_delimiter, $row);
                    $counter++;
                    $dataIsEmpty = true;
                    if ($counter <= $this->_skipLines) continue;
                    if (is_array($mapping) && count($mapping)) {
                        $map = array();
                        foreach($mapping as $index => $name) {
                            if (isset($data[$index])) {
                                if($dataIsEmpty && strlen(trim($data[$index]))) {
                                    $dataIsEmpty = false;
                                }
                                if (is_array($name)&&count($name)) {
                                    if (isset($name['callback'])&&!empty($name['callback'])) {
                                    	$resUserFunc = call_user_func(array($this,$name['callback']),trim($data[$index]));
                                    } else {
                                    	$resUserFunc = trim($data[$index]);
                                    }
                                    $resUserFunc = $this->getFormatData($name,$resUserFunc);
                                    $map = $resUserFunc ? array_merge((array)$map,(array)$resUserFunc) : $map;
                                } else {
                                    $map[$name] = trim($data[$index]);
                                }
                            }
                        }
                        $data = $map;
                    }
                    if (!$dataIsEmpty) {
                        $result[] = $data;
                    }
				}

                fclose($fh);
            }
        }
        return $result;
    }

}