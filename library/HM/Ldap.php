<?php
class HM_Ldap extends Zend_Ldap
{
    public function __construct($options = array())
    {
        if (empty($options)) {
            $options = Zend_Registry::get('config')->ldap->options->toArray();
            $options['username'] = iconv(Zend_Registry::get('config')->charset, 'UTF-8', $options['username']);
        }
        parent::__construct($options);
    }

    public function getEntry($dn, array $attributes = array(), $throwOnNotFound = false)
    {
        $entry = parent::getEntry($dn, $attributes, $throwOnNotFound);
        if ($entry) {
            foreach($entry as $key => $values) {
                if (is_array($values) && count($values)) {
                    foreach($values as $index => $value) {
                        $entry[$key][$index] = iconv('UTF-8', Zend_Registry::get('config')->charset, $value);
                    }
                } else {
                    $entry[$key] = iconv('UTF-8', Zend_Registry::get('config')->charset, $values);
                }
            }
        }
        return $entry;
    }

    public function findUserByLogin($login)
    {
        $dn = $this->getCanonicalAccountName(
            iconv(Zend_Registry::get('config')->charset, 'UTF-8', $login),
            Zend_Ldap::ACCTNAME_FORM_DN
        );
        if ($dn) {
            return $this->getEntry($dn);
        }
        return null;
    }

}