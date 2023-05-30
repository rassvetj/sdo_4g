<?php

class HM_Validate_GreaterOrEqualThanValue extends Zend_Validate_Abstract
{
    const IDENTICAL = 'identical';

    public $_name = null;

    protected $_messageTemplates = array(
        self::IDENTICAL => 'Значение должно быть больше или равно %min%'
    );
    
    protected $_messageVariables = array(
        'min' => '_min'
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
        	$this->_min = (int) $_REQUEST[$this->_name];
            if ($value < $_REQUEST[$this->_name]) {
                $this->_error(null);
                return false;
            }
        }

        return true;
    }
}