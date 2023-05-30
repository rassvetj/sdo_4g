<?php
class HM_Role_Supervisor_Responsibility_ResponsibilityService extends HM_Service_Abstract
{
    public function addResponsibility($userId, $resposibilityId, $resposibilityType)
    {
        $already = $this->countAll($this->quoteInto(
            array('user_id = ?', ' AND responsibility_id = ?', ' AND responsibility_type = ?'),
            array($userId, $resposibilityId, $resposibilityType)
        ));
        if(!$already){
            $this->insert(array('user_id' => (int)$userId, 'responsibility_id' => (int)$resposibilityId, 'responsibility_type' => (int)$resposibilityType));
        }
        return !$already;
    }

    public function getResponsibilityType($userId){
        $select = $this->getSelect();
        $select->from(
            array('sr' => 'supervisors_responsibilities'),
            array('sr.responsibility_type')
            )->where(
                'sr.user_id = ?', $userId
            )->group(array('sr.responsibility_type'));
        $stmt = $select->query();
        $res = $stmt->fetchAll();

        if (count($res)) {
            if (count($res) > 1)  return false;
            foreach($res as $item) {
                $responsibilityType = $item['responsibility_type'];
            }
        } else {
            return false;
        }
        return $responsibilityType;
    }

    public function getResponsibilities ($userId) {
        $responsibilities = $this->fetchAll($this->quoteInto('user_id = ?', $userId));

    }
    public function deleteResponsibilities($userId)
    {
        //#13907 - вынес добавление в контроллер,чтобы автоматически не добавлялась запись с subject_id = 0
        /*if($this->countAll($this->quoteInto(array('MID = ? AND subject_id = 0'), array($userId))) == 0)
            $this->insert(array('MID' => $userId,'subject_id' => '0'));*/
        return $this->deleteBy($this->quoteInto('user_id = ?', $userId));
    }
}