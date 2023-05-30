<?php

class HM_Oauth_App_AppService extends HM_Service_Abstract
{    
    public function insert($data)
    {
        $data['created'] = $this->getDateTime();
        $data['created_by'] = $this->getService('User')->getCurrentUserId();

        // Генерация api_key, consumer_key, consumer_secret
        $data['api_key'] = $this->getService('User')->getRandomString(32);
        list($data['consumer_key'], $data['consumer_secret']) = $this->generateConsumerKeys();

        return parent::insert($data);
    }

    public function generateConsumerKeys()
    {
        $entropy = $this->getService('User')->getRandomString(32);

        $hash = sha1($entropy); // sha1 gives us a 40-byte hash
        // The first 30 bytes should be plenty for the consumer_key
        // We use the last 10 for the shared secret
        return array(substr($hash,0,10),substr($hash,10,30));
    }
}
