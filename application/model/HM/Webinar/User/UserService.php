<?php
class HM_Webinar_User_UserService extends HM_Service_Abstract
{

    public function ping($pointId, $userId) 
    {
        $user = $this->find($pointId, $userId);
        $date = new Zend_Date();
        $array = array(
    	    'userId'  => $userId,
    	    'pointId' => $pointId,
    	    'last'    => $date->toString('yyyy-MM-dd H:m:s')
        );
        if ($user->count()) {
            $res = $this->update($array);
        } else {
            $this->insert($array);
        }
    }

    public function getUserList($pointId, $teacherId = 0)
    {
        $result = array();
        
        $res = $this->getService('LessonAssign')->fetchAllDependence('User', 'SHEID = ' . (int) $pointId);
        foreach($res as $value){
            if(isset($value->users[0]->MID)){
                $result[$value->users[0]->MID] = $value->users[0];
            }
        }

        //Если препод отсутствует в scheduleID, что логично, подтягиваем его отдельно
        if ($teacherId && empty($result[$teacherId])) {
            $result[$teacherId] = $this->getService('User')->getOne($this->getService('User')->find($teacherId));
        }


        return $result;
    }

    public function getUserListOnline($pointId)
    {
        $result = array();
        $this->deleteBy(
            array(
            	'last < ?' => date('Y-m-d H:i:s', time() - 60*5), 
            	'pointId = ?' => $pointId
            )
        );
        $res = $this->fetchAllDependence('User', 'pointId = ' . (int) $pointId);
        foreach($res as $value){
            if(isset($value->users[0]->MID)){
                $result[$value->users[0]->MID] = $value->users[0];
            }
        }
        return $result;
    }

}