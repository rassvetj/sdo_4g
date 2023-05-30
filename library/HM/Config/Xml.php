<?php
/**
 * Created by PhpStorm.
 * User: yury
 * Date: 09.09.2010
 * Time: 12:46:29
 * To change this template use File | Settings | File Templates.
 */

class HM_Config_Xml extends Zend_Config_Xml
{
    public function __construct($xml, $section = null, $options = false)
    {
        parent::__construct($xml, $section, $options);
        $this->_convert($this->_data);
    }

    private function _convert($data)
    {
        if (count($data)) {
           if ($data instanceof Zend_Config) {
               $allowModifications = $data->_allowModifications;
               $data->_allowModifications = true;
           }
           foreach($data as $key => $value) {
               if (is_string($value)) {

                   $data->$key = iconv('UTF-8', Zend_Registry::get('config')->charset, $value);
               }
               if ($value instanceof Zend_Config) {
                   $this->_convert($value);
               }
           }
           if ($data instanceof Zend_Config) {
               $data->_allowModifications = $allowModifications;
           }
        }
    }
}
 
