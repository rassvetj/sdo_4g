<?php

/**
 * Модель списка форумов
 * 
 * Реализует доступ к данным списка форумов.
 * Не содержит бизнес-логики.
 * Ничего не знает о хранении других данных форума.
 */
class HM_Forum_Forum_ForumService extends HM_Forum_Library_ServiceAbstract{
    
    /**
     * Создать форум
     * 
     * @param array $data параметры
     * @return int forum id
     */
    public function createForum(array $data){
        $data['flags'] = HM_Forum_Forum_ForumModel::flagsEncode($data['flags']);
        $data['created'] = $this->getDateTime();        
        $this->_prepareData(&$data);
        
        $forum = $this->insert($data);
        
        return $forum;
    }
    
    /**
     * Изменить информацию о форуме в базе
     * 
     * @param int $id forum id
     * @param array $data data
     */
    public function updateForum($id, array $data){
        switch(true){
            case !isset($data['flags']):
                break;

            case is_array($data['flags']):
                $data['flags'] = HM_Forum_Forum_ForumModel::flagsEncode($data['flags']);
                break;

            case $data['flags'] instanceof HM_Forum_Library_Flags:
                $data['flags'] = $data['flags']->getEncoded();
                break;
        }
        
        $data['updated'] = $this->getDateTime();
        unset($data['forum_id']);        
        $this->_prepareData(&$data);
                
        $this->updateWhere($data, 'forum_id = ' . $id);
        $this->cacheUnset($id);
    }
    
    /**
     * Получить форум по id
     * 
     * @param int $id id
     * @return HM_Forum_Forum_ForumModel | null если не найден
     */
    public function getForum($id){
        $forum = $this->cacheGet($id);
        if($forum) return $forum;
        
        $forum = $this->fetchRow(array('forum_id = ?' => $id));
        if(!$forum) $forum = null;
        
        $this->cacheSet($id, $forum);
        return $forum;
    }
    
    /**
     * Получить форум по id занятия
     * 
     * @param int $id id
     * @return HM_Forum_Forum_ForumModel | null если не найден
     */
    public function getForumBySubjectId($id){
        $forum = $this->cacheGet('subj_' . $id);
        if($forum) return $forum;
        
        $forum = $this->fetchRow(array('subject_id = ?' => $id));
        if(!$forum) $forum = null;
        
        $this->cacheSet('subj_' . $id, $forum);
        return $forum;
    }
    
    /**
     * Удалить форум
     * 
     * @param int $id forum id
     */
    public function deleteForum($id){
        $this->deleteBy('forum_id = ' . $id);
        $this->cacheUnset($id);
    }
    
}