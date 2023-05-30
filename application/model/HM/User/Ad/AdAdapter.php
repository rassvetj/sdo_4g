<?php
class HM_User_Ad_AdAdapter extends HM_Adapter_Import_Abstract
{
    /*
     * HM_Ldap
     */
    private $_ldap = null;

    public function __construct(Zend_Ldap $ldap = null)
    {
        if (null === $ldap) {
            $ldap = Zend_Registry::get('serviceContainer')->getService('Ldap');
        }

        $this->_ldap = $ldap;
    }

    public function needToUploadFile()
    {
        return false;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $values = array(Zend_Registry::get('config')->ldap->user->uniqueIdField);
        foreach(Zend_Registry::get('config')->ldap->mapping->user as $key => $value) {
            $values[] = $key;
        }

        $limit = Zend_Registry::get('config')->ldap->fetchItemsLimit;

        $alphabet = 'qwertyuiopasdfghjklzxcvbnm';

        $dns = array(null);
        if (Zend_Registry::get('config')->ldap->units) {
            $dns = Zend_Registry::get('config')->ldap->units->toArray();
        }

        $ret = array();

        foreach($dns as $dn) {
            $count = 0;
            for($i = 0; $i < strlen($alphabet); $i++) {
                if ($count >= 5) {
                    $this->_ldap->disconnect();
                    $this->_ldap->bind();
                }
                $alpha = substr($alphabet, $i, 1);
                $res = $this->_ldap->search('(&(samaccountname='.$alpha.'*)(objectclass=user)(!(userAccountControl:1.2.840.113556.1.4.803:=2))('.Zend_Registry::get('config')->ldap->user->uniqueIdField.'=*))', $dn, Zend_Ldap::SEARCH_SCOPE_SUB, $values);

                if (count($res) >= $limit) {
                    for($j = 0; $j < strlen($alphabet); $j++) {
                        if ($count >= 5) {
                            $this->_ldap->disconnect();
                            $this->_ldap->bind();
                        }

                        $beta = substr($alphabet, $j, 1);
                        $res = $this->_ldap->search('(&(samaccountname='.$alpha.$beta.'*)(objectclass=user)(!(userAccountControl:1.2.840.113556.1.4.803:=2))('.Zend_Registry::get('config')->ldap->user->uniqueIdField.'=*))', $dn, Zend_Ldap::SEARCH_SCOPE_SUB, $values);

                        if (count($res)) {
                            $ret = array_merge($ret, $res->toArray());
                        }

                        $count++;
                    }
                    continue;
                }

                if (count($res)) {
                    $ret = array_merge($ret, $res->toArray());
                }
                $count++;
            }
        }

        return $ret;
    }

}