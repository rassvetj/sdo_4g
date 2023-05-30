<?php
class HM_Role_DeanService extends HM_Service_Abstract
{

    public function userIsDean($userId)
    {
        $res = false;
        if($this->countAll('MID = '. (int) $userId) > 0) $res = true;
        return $res;
    }

    public function getResponsibilityOptions($userId)
    {
        $options = $this->getOne($this->getService('DeanOptions')->fetchAll('user_id = ' . (int) $userId));
        if(!$options){
            $options = array('user_id' => (int) $userId, 'unlimited_subjects' => 1, 'unlimited_classifiers' => 1, 'assign_new_subjects' => 0);
        }
        else{
            $options = $options->getValues();
        }
        return $options;
    }

    /**
     * Устанавливает параметры областей ответственности
     * @param array $options('user_id', 'unlimited_courses', 'unlimited_subjects', 'assign_new_courses')
     */
    public function setResponsibilityOptions($options)
    {
        if($this->getOne($this->getService('DeanOptions')->find($options['user_id']))){
            $this->getService('DeanOptions')->update($options, false);
        }else{
            $this->getService('DeanOptions')->insert($options, false);
        }
    }

    /**
     * Проверяет наличие области ответственности
     *
     * @param unknown_type $userId
     * @param unknown_type $subjectId
     * @return string|string
     */
    public function isSubjectResponsibility($userId, $subjectId)
    {
        $options = $this->getResponsibilityOptions($userId);
        if($options['unlimited_subjects'] == 1)
        {
            return true;
        }else{
            $res = $this->countAll(
                $this->quoteInto(
                    array('MID = ?', ' AND subject_id = ?'),
                    array($userId, $subjectId)
                )
            );
            if($res > 0) return true;
        }
        return false;
    }

    public function isClassifierResponsibility($userId, $classifierId)
    {
        $options = $this->getResponsibilityOptions($userId);
        $res = $options['unlimited_classifiers'];
        if(!$res) $res = $this->getService('DeanResponsibilities')->isResponsibilitySet($userId, $classifierId);
        return $res;
    }
    
    /**
     * Добавляет область ответственности
     * 
     * @param unknown_type $userId
     * @param unknown_type $subjectId
     * @return string|string
     */
    public function addSubjectResponsibility($userId, $subjectId)
    {
        $res = $this->fetchAll(array('MID = ?' => $userId, 'subject_id = ?' => $subjectId));

        if(count($res) == 0){
            $this->insert(
                array(
                	'MID' => $userId,
                    'subject_id' => $subjectId
                )
            );
            return true;
        }
        return false;
        
    }

    public function deleteSubjectsResponsibilities($userId)
    {
        //#13907 - вынес добавление в контроллер,чтобы автоматически не добавлялась запись с subject_id = 0
        /*if($this->countAll($this->quoteInto(array('MID = ? AND subject_id = 0'), array($userId))) == 0)
            $this->insert(array('MID' => $userId,'subject_id' => '0'));*/
        return $this->deleteBy($this->quoteInto('MID = ?', $userId));
    }

    /**
     * deprecated! use $this->getService('DeanResponsibility')->deleteResponsibilities($userId);
     *
     * @param unknown_type $userId
     * @param unknown_type $subjectId
     * @return string|string
     */
    public function deleteClassifiersResponsibility($userId)
    {
        return $this->getService('DeanResponsibility')->deleteResponsibilities($userId);
    }

    /**
     * По ид пользователя возвращаем коллекцию моделей областей ответственности
     * (т.е. учебных курсов)
     *
     * @param unknown_type $userId
     * @return HM_Collection
     */
    public function getSubjectsResponsibilities($userId)
    {
        
        $options = $this->getResponsibilityOptions($userId);
        
        if($options['unlimited_subjects'] == 1)
            return $this->getService('Subject')->fetchAll(null, 'subjects.name');
        else
            return $this->getAssignedSubjectsResponsibilities($userId);
        
    }
    
     /**
     * По ид пользователя возвращаем коллекцию АКТИВНЫХ моделей областей ответственности
     * (т.е. учебных курсов дата окончания которых > сегодня или их время неограничено)
     *
     * @param unknown_type $userId
     * @return HM_Collection
     */
    public function getActiveSubjectsResponsibilities($userId)
    {
        
        $options = $this->getResponsibilityOptions($userId);
        
        if($options['unlimited_subjects'] == 1)
            return $this->getService('Subject')->fetchAll("period IN (1,2) OR end > NOW() OR end IS NULL", 'subjects.name');
            //return $this->getService('Subject')->fetchAll("end > NOW() OR end = '0000-00-00' OR end IS NULL", 'subjects.name');
        else
            return $this->getAssignedSubjectsResponsibilities($userId);
        
    }


    public function getAssignedSubjectsResponsibilities($userId)
    {
    	$dean = $this->getService('User')->fetchAllManyToMany('Subject','Dean', array('MID = ?' => $userId)/*, 'subjects.name'*/); // wtf???
    	return ($dean[0]->subjects) ? $dean[0]->subjects : new HM_Collection();
        
    }

    public function getSubjects($userId)
    {
        return $this->getSubjectsResponsibilities($userId);
    }

}