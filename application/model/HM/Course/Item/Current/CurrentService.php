<?php
class HM_Course_Item_Current_CurrentService extends HM_Service_Abstract
{

    public function updateCurrent($userId, $subjectId, $courseId, $itemId, $lessonId = 0){

        $one = $this->getOne($this->find($userId, 
                                         $courseId, 
                                         $subjectId,
                                         $lessonId
                             )
        );
        
        $data = array('mid'        => $userId,
                      'cid'        => $courseId,
                      'subject_id' => $subjectId,
                      'current'    => $itemId,
                      'lesson_id'  => $lessonId
                );

        if($one){
            $this->update($data);
        }else{
            $this->insert($data);
        }
        return true;
    }
    
    public function getCurrent($userId, $subjectId, $courseId, $lessonId = 0){
        if(
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER)
           // $this->getService('User')->getCurrentUserRole() == HM_Role_RoleModelAbstract::ROLE_STUDENT
        ){
            $one = $this->getOne($this->find($userId,
                                             $courseId, 
                                             $subjectId,
                                             $lessonId
                                 )
            );
            
            if($one){
                $testExist = $this->getService('CourseItem')->find($one->current);
                if($testExist){
                    return $one->current;
                }
            }
        }    
        $one = $this->getOne(
            $this->getService('CourseItem')->fetchAll(
                array('cid =?'        => $courseId,
                      'prev_ref >= ?' => -1,
                      'module > 0'
                ), array('oid')
            )
        );
        
        if($one){
            return $one->oid;
        }
        return false;
    }
    

}