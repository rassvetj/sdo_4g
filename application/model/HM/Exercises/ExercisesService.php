<?php
class HM_Exercises_ExercisesService extends HM_Service_Abstract
{
    
    public function delete($id)
    {
        //удаляем метки
        $this->getService('TagRef')->deleteBy($this->quoteInto(array('item_id=?',' AND item_type=?'), 
                                                               array($id,HM_Tag_Ref_RefModel::TYPE_EXERCISES)));
        return parent::delete($id);
    }

	 public function insert($data)
    {
        $data['created_by'] = $this->getService('User')->getCurrentUserId();
        $data['created'] = $data['updated'] = $this->getDateTime();
        return parent::insert($data);
    }

    public function update($data)
    {
        $data['updated'] = $this->getDateTime();

        $test = parent::update($data);

        if ($test) {
            if (isset($data['data'])) {
                $this->getService('Test')->updateWhere(array('data' => $data['data']), $this->quoteInto('test_id = ?', $test->test_id));
            }
        }

        return $test;
    }

    public function publish($id)
    {
        $this->update(array(
            'exercise_id' => $id,
            'status' => HM_Test_Abstract_AbstractModel::STATUS_PUBLISHED
        ));
    }

    public function isEditable($subjectIdFromResource, $subjectId, $status){
        $all = array(HM_Role_RoleModelAbstract::ROLE_DEVELOPER, HM_Role_RoleModelAbstract::ROLE_MANAGER);
        $role = $this->getService('User')->getCurrentUserRole();
        if(in_array($role, $all)){
            return true;
        }

        if(
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TEACHER, HM_Role_RoleModelAbstract::ROLE_DEAN))
            //in_array($role, array(HM_Role_RoleModelAbstract::ROLE_TEACHER, HM_Role_RoleModelAbstract::ROLE_DEAN))
            && $status == HM_Poll_PollModel::STATUS_UNPUBLISHED) {
            return true;
        }

        return false;
    }
	
}