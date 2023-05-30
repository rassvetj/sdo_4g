<?php
class HM_Role_EmployeeService extends HM_Service_Abstract
{

    /**
     *
     * Возвращает коллекцию орг-единиц   - руководителей или false
     *
     * @param $userId
     * @return bool|HM_Collection
     */
    public function getSupervisors($userId)
    {

        $org = $this->getService('Orgstructure')->getOne(
            $this->getService('Orgstructure')->fetchAll(
                array(
                    'mid = ?' => $userId
                )
            )
        );


        if($org){
            $supervisors = $this->getService('Orgstructure')->fetchAll(
                array(
                    'owner_soid = ?' => $org->owner_soid,
                    'is_manager = 1'
                )
            );

            return $supervisors;
        }

        return false;
    }

    public function assign($mid)
    {
        if($mid > 0){
            $res = $this->fetchAll(array('user_id = ?' => $mid));

            if(count($res) > 0 ){
                return true;
            }else{
                $this->insert(array('user_id' => $mid));
                return true;
            }
        }
        return false;
    }



}