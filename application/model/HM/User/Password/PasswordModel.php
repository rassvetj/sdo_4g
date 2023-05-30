<?php
class HM_User_Password_PasswordModel extends HM_Model_Abstract
{
    const TYPE_BLOCK   = 0;
    const TYPE_CAPTCHA = 1;
    
    
    const RESTRICTION_FREE = 0;
    const RESTRICTION_WITH = 1;
    
    
    static function getWrongPasswordTypes()
    {
        
        return array(
            self::TYPE_BLOCK   => _('Блокировать учетную запись'),
            self::TYPE_CAPTCHA => _('Требовать подтверждение ручного ввода пароля (captcha)') 
        );
        
    }
    
    static function getRestrictionTypes()
    {
        
        return array(
            self::RESTRICTION_FREE   => _('Без ограничений'),
            self::RESTRICTION_WITH => _('С ограничениями') 
        );
        
    }
    

}