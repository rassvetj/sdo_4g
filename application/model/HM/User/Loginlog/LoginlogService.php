<?php
class HM_User_Loginlog_LoginlogService extends HM_Service_Abstract
{

    public function insert($data)
    {
        $data = $this->_prepareData($data);
        if($data['login']!=""){
            return parent::insert($data);
        }
    }
    
    public function update($data)
    {
        $data = $this->_prepareData($data);
        if($data['login']!=""){
            return parent::update($data);
        }
    }
    
    
    protected function _prepareData($data)
    {
        if(isset($data['ip']) && !empty($data['ip']) && !is_numeric($data['ip'])){
            $data['ip'] = sprintf('%u', ip2long($data['ip']));    
        }
        return $data;
    }
    
    
    public function login($login, $comments,$status = 1)
    {

        $this->insert(
            array(
        		'login'  => $login,
                'status' => $status,
                'date'   => $this->getDateTime(),
                'ip'     => $_SERVER['REMOTE_ADDR'],
                'event_type' => HM_User_Loginlog_LoginlogModel::EVENT_LOGIN,
                'comments' => $comments
            )
        );
        
        if($status == 1){
            $status = 'Success';
        }else{
            $status = 'Fail';
        }
        
        
        $this->getService('Log')->log(
            $login,
            'System login',
            $status,
            Zend_Log::NOTICE   
        );
    }
    
    
    public function logout($login, $comments,$status = 1)
    {
        
        $this->insert(
            array(
        		'login'  => $login,
                'status' => $status,
                'date'   => $this->getDateTime(),
                'ip'     => $_SERVER['REMOTE_ADDR'],
                'event_type' => HM_User_Loginlog_LoginlogModel::EVENT_EXIT,
                'comments' => $comments
            )
        );
        
        $this->getService('Log')->log(
            $login,
            'System logout',
            'Success',
            Zend_Log::NOTICE 
        );
    }
    
    
    /**
     * Return ip as unsigned long
     * @param unknown_type $ip
     * @return string
     */
    public function ip2long($ip)
    {
        return sprintf('%u', ip2long($ip));
    }
    
    
    
}