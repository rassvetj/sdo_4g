<?php
class HM_Ldap_Patched_Auth extends HM_Ldap
{    
    public function getCanonicalAccountName($acctname, $form = 0)
    {
        if($form == self::ACCTNAME_FORM_DN){
            return $acctname;
        }

        parent::getCanonicalAccountName($acctname, $form);
    }

}