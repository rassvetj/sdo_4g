<?php
class HM_Validate_MinimalDatePassword extends Zend_Validate_Abstract
{
    const NOT_MINIMAL_DATE = 'notMinimalDate';

    public $_name = null;
    
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_MINIMAL_DATE => "Вы не можете так часто менять пароль."
    );
    
    protected $_messageVariables = array(
        //'max' => '_max'
    );

    public function isValid($value)
    {
        $this->_setValue($value);
        $passwordOptions = Zend_Registry::get('serviceContainer')->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_PASSWORDS);
        
        if($passwordOptions['passwordMinPeriod'] > 0){
            $lastChange = Zend_Registry::get('serviceContainer')->getService('UserPassword')->getChangePasswordLastDate(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId());
            if((time() - strtotime($lastChange)) > ($passwordOptions['passwordMinPeriod'] * 3600*24)){
                return true;
            }else{
                $this->_error(self::NOT_MINIMAL_DATE);
                return false;
            }
            
        }
        return true;

    }
    

}