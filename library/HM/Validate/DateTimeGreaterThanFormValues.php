<?php
class HM_Validate_DateTimeGreaterThanFormValues extends Zend_Validate_Abstract
{
    const NOT_GREATER = 'notGreaterThan';

    public $_minDateName = null;
    public $_minTimeName = null;
    public $_dateName = null;
    
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_GREATER => "Дата '%value%' должна быть больше '%min%'"
    );
    
    protected $_messageVariables = array(
        'min' => '_min'
    );

    public function __construct($options)
    {
        if (is_array($options)) {
            if (array_key_exists('minDateName', $options)) {
                $minDateName = $options['minDateName'];
                $this->setMinDateName($minDateName);
            } else {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception("Missing option 'minDateName'");
            }
            
            if (array_key_exists('minTimeName', $options)) {
                $minTimeName = $options['minTimeName'];
                $this->setMinTimeName($minTimeName);
            } else {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception("Missing option 'minTimeName'");
            }

            if (array_key_exists('dateName', $options)) {
                $dateName = $options['dateName'];
                $this->setDateName($dateName);
            } else {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception("Missing option 'dateName'");
            }
            
        }
    }


    public function getMinDateName()
    {
        return $this->_minDateName;
    }

    public function setMinDateName($name)
    {
        $this->_minDateName = $name;
    }
    
    public function getMinTimeName()
    {
        return $this->_minTimeName;
    }

    public function setMinTimeName($name)
    {
        $this->_minTimeName = $name;
    }
    
    public function getDateName()
    {
        return $this->_dateName;
    }

    public function setDateName($name)
    {
        $this->_dateName = $name;
    }
    

    public function isValid($value)
    {
        $this->_setValue($value);
        if (($this->_minDateName !== null) && isset($_REQUEST[$this->_minDateName])
            && ($this->_minTimeName !== null) && isset($_REQUEST[$this->_minTimeName])
            && ($this->_dateName !== null) && isset($_REQUEST[$this->_dateName])) {
            
            $this->_min = $_REQUEST[$this->_minDateName].' '.$_REQUEST[$this->_minTimeName];

            $this->_setValue($_REQUEST[$this->_dateName].' '.$value);
            
            $value1 = strtotime($_REQUEST[$this->_dateName].' '.$value);
            $value2 = strtotime($this->_min);
        	if ($value1 <= $value2) {
                $this->_error(self::NOT_GREATER);
                return false;
            }
        }

        return true;
    }
    

}