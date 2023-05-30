<?php
class HM_Provider_ProviderService extends HM_Service_Abstract
{
    public function autodetectProvider($key)
    {
        if (in_array($key, array(
            'PagePlayer.properties',
            'SimPagePlayer.properties',
        ))) {
            return HM_Provider_ProviderModel::SKILLSOFT;
        }
        return false;
    }
}