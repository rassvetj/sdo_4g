<?php
class HM_Validate_HardPassword extends Zend_Validate_Abstract
{
    const NOT_HARD = 'notHard';

    public $_name = null;
    
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_HARD => "В сложном пароле должны присутствовать символы как минимум трех категорий из числа следующий четырех: 
прописные буквы английского или русского алфавита, 
строчные буквы английского или русского алфавита, 
десятичные цифры, 
символы не принадлежащие к алфавитно-цифровому набору (!, $, #, %);"
    );
    
    protected $_messageVariables = array(
        //'max' => '_max'
    );

    public function isValid($value)
    {
        $this->_setValue($value);

        $counters = array('alpha' => 0, 'bigAlpha' => 0, 'numeric' => 0, 'nonAlpha' => 0);
        
        if(preg_match('/[a-zа-яё]+/', $value)){
            $counters['alpha'] = 1;
        }
        
        if(preg_match('/[A-ZА_ЯЁ]+/', $value)){
            $counters['bigAlpha'] = 1;
        }
        
        if(preg_match('/[0-9]+/', $value)){
            $counters['numeric'] = 1;
        }
        
        if(preg_match('/[0-9]+/', $value)){
            $counters['nonAlpha'] = 1;
        }
        
        
        if(array_sum($counters) < 3 || preg_match('/[^a-zа-яёЁ0-9!@#$%\^&\*\(\)_\+]/i', $value)){
            $this->_error(self::NOT_HARD);
            return false;
        }

        return true;
    }
    

}