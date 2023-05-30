<?php
class HM_Webinar_WebinarService extends HM_Service_Abstract
{

    public function delete($id)
    {
        $this->getService('WebinarFiles')->deleteBy(array('webinar_id = ?' => $id));
        parent::delete($id);
    }


    public function isUserAllowed($pointId, $userId) {
        $res = $this->getService('LessonAssign')->fetchAll(array('SHEID = ?' => $pointId, 'MID = ?' => $userId));
        if(count($res) == 0){
            return false;
        }else{
            return true;
        }
    }

    public function isTeacherAllowed($pointId, $userId)
    {
        $res = $this->getService('Lesson')->fetchAll(array('SHEID = ?' => $pointId, 'teacher = ?' => $userId));
        if(count($res) == 0){
            return false;
        }else{
            return true;
        }
    }

    public function isWebinarTeacherAllowed($webinarId, $userId, $subjectId)
    {
        $res = $this->getOne($this->find($webinarId));
        if($res){
            if($this->getService('Subject')->isTeacher($subjectId, $userId)){
                return true;
            }
        }

        return false;
    }

    public function getDefaults()
    {
        $user = $this->getService('User')->getCurrentUser();
        return array(
            'create_date' => $this->getDateTime(),
        );
    }
    public function getRecordFiles($pointId) {
        return $this->getService('WebinarHistory')->getFiles($pointId);
    }
	/**
	 * возвращ€ет путь где наход€тс€ файлы на сервере ped5
	 * @param $pointId
	 * @return getPathToFiles
	 */
    public function getPathToFiles($pointId) {
        return $this->getService('WebinarHistory')->getPathToFiles($pointId);
    }

}