<?php
class HM_Programm_Event_User_UserService extends HM_Service_Abstract
{
    public function assign($userId, $event)
    {

        $this->insert(
            array(
                'user_id' => $userId,
                'programm_event_id' => $event->programm_event_id,
            )
        );



    }




}