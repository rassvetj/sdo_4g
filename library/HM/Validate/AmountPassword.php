<?php
class HM_Validate_AmountPassword extends Zend_Validate_Abstract
{
    const NOT_NEW = 'notNew';

    public $_name = null;
    
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_NEW => "Вы использовали данный пароль ранее. Придумайте новый."
    );
    
    protected $_messageVariables = array(
        //'max' => '_max'
    );

    public function isValid($value)
    {
        $this->_setValue($value);
        $passwordOptions = Zend_Registry::get('serviceContainer')->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_PASSWORDS);
        
        if($passwordOptions['passwordMinNoneRepeated'] > 0){
            $lastPasswords = Zend_Registry::get('serviceContainer')->getService('UserPassword')->getLastPasswords(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId(), $passwordOptions['passwordMinNoneRepeated']);

            foreach($lastPasswords as $password){
                if($password->password == md5($value) || $password->password == md5((string)new Zend_Db_Expr("PASSWORD(" . Zend_Registry::get('serviceContainer')->getService('User')->getSelect()->getAdapter()->quote($value) . ")"))){
                    $this->_error(self::NOT_NEW);
                    return false;
                }
            }
        }
        return true;
    }
    

}