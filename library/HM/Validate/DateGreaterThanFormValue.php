<?php
class HM_Validate_DateGreaterThanFormValue extends Zend_Validate_Abstract
{
    const NOT_LESS = 'notLessThan';

    public $_name = null;
    
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_LESS => "Дата '%value%' должна быть больше и равна '%max%'"
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
            $this->_max = $_REQUEST[$this->_name];
            $value1 = strtotime($value);
            $value2 = strtotime($_REQUEST[$this->_name]);
        	if ($value1 < $value2) {
                $this->_error(self::NOT_LESS);
                return false;
            }
        }

        return true;
    }
    

}