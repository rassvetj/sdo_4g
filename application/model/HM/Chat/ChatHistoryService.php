<?php

class HM_Chat_ChatHistoryService extends HM_Activity_ActivityService
{
    protected $_isIndexable = false;

    public function insert($data, $unsetNull = null)
    {
        $data['created'] = $this->getDateTime();
        $item = parent::insert($data, $unsetNull);
        return $item;
    }
    
    public function getByChannel($channelId, $limit = null)
    {
        return $this->fetchAllDependence('Users', $this->quoteInto('channel_id = ?', $channelId), 'created DESC', $limit);
    }
    
    static public function postToChat($data)
    {
        $postData = array();
        foreach($data as $k => $v) {
            $postData []= $k.'='.$v;
        }
        $config = Zend_Registry::get('config');
        $c = curl_init($config->chat->postUrl.'?action=pub');
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, implode('&', $postData));
        $r = curl_exec($c);
        curl_close($c);
        return $r;
    }    
}
