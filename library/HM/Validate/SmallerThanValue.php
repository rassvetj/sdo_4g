<?php

class HM_Validate_SmallerThanValue extends Zend_Validate_Abstract
{
    const IDENTICAL = 'identical';

    public $_name = null;

    protected $_messageTemplates = array(
        self::IDENTICAL => 'Значение должно быть меньше %max%.'
    );
    
    protected $_messageVariables = array(
        'max' => '_max'
    );

    public function __construct($name)
    {
        if ($name instanceof Zend_Config) {
            $name = $name->toArray();
        }

        if (is_array($name)) {
            if (array_key_exists('name', $name)) {
                $name = $name['name'];
            } else {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception("Missing option 'min'");
            }
        }

        $this->setName($name);
    }


    public function getName()
    {
        return $this->_name;
    }

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function isValid($value)
    {
        $this->_setValue($value);

        if (($this->_name !== null) && isset($_REQUEST[$this->_name])) {
            $this->_max = (int) $_REQUEST[$this->_name];
        	if ($value >= $_REQUEST[$this->_name]) {
                $this->_error(null);
                return false;
            }
        }

        return true;
    }
}