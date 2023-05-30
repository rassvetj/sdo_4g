<?php
class HM_Auth_Adapter_Ldap extends Zend_Auth_Adapter_Ldap
{
    public function __construct(array $options = array(), $username = null, $password = null) { 
        if (empty($options['server'])) {
            $options['server'] = Zend_Registry::get('config')->ldap->options->toArray();
            
            $options['server']['accountDomainName']      = current($options['server']['accountDomainName']);
            $options['server']['accountDomainNameShort'] = current($options['server']['accountDomainNameShort']);
            $options['server']['baseDn']                 = current($options['server']['baseDn']);
            $options['server']['host']                   = current($options['server']['host']);
            
            
        }
        parent::__construct($options, $username, $password);
    }
    
    public function getLdap()
    {
        if ($this->_ldap === null) {
            require_once 'HM/Ldap_Patched_Auth.php';
            $this->_ldap = new HM_Ldap_Patched_Auth();
        }

        return $this->_ldap;
    }
    
}