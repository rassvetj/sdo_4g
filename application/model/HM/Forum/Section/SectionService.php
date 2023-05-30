<?php

/**
 * Модель списка разделов (тем) форума
 * 
 * Реализует доступ к данным разделов форумов.
 * Не содержит бизнес-логики.
 * Ничего не знает о хранении других данных форума.
 */
class HM_Forum_Section_SectionService extends HM_Forum_Library_ServiceAbstract
{
    /**
     * Создать новый раздел в форуме
     * 
     * @param array $data данные нового раздела
     * @return HM_Forum_Section_SectionModel
     */
    public function createSection(array $data){        
        $data['flags'] = HM_Forum_Section_SectionModel::flagsEncode($data['flags']);
        $data['created'] = $this->getDateTime();        
        $section = $this->insert($data);
        
        if(empty($section->lesson_id)) $where = array('section_id' => (int) $section->section_id);
        else $where = array('lesson_id' => (int) $section->lesson_id);
        $this->cacheSet($where, $section);
        
        return $section;
    }
    
    /**
     * Получить раздел форума по его ID
     * 
     * @param int $id ID темы
     * @return HM_Forum_Section_SectionModel
     */
    public function getSection($sectionId){
        return $this->_getSection(array('section_id = ?' => (int) $sectionId));
    }
    
    /**
     * Получить раздел форума по ID занятия
     * 
     * @param int $id ID занятия
     * @return HM_Forum_Section_SectionModel
     */
    public function getSectionByLessonId($lessonId){
        return $this->_getSection(array('lesson_id = ?' => (int) $lessonId));
    }
    
    /**
     * Получить раздел форума по условию
     * 
     * @param array условие
     * @return HM_Forum_Section_SectionModel
     */
    protected function _getSection(array $where){
        $section = $this->cacheGet($where);
        if($section) return $section;
        
        $section = $this->fetchRow($where);
        if(!$section) $section = null;
        
        $this->cacheSet($where, $section);
        return $section;
    }
    
    /**
     * Получить все разделы форума по его (форума) id
     * 
     * @param int $forumId section id
     * @param int $parentId parent section id
     * @return HM_Collection
     */
    public function getSectionsList($forumId = null, $parentId = null){
        $where = array('lesson_id = 0'); // В список разделов не должны попадать темы занятий типа "форум"
        
        if(is_array($forumId)) $where['forum_id IN(?)'] = $forumId;
        elseif(is_numeric($forumId)) $where['forum_id = ?'] = $forumId;
        
        if(is_numeric($parentId)) $where['parent_id = ?'] = $parentId;
        elseif(is_array($parentId)) $where['parent_id IN(?)'] = $parentId;
        else $where['parent_id = ?'] = 0;
        
        $sections = $this->cacheGet($where);
        if($sections) return $sections;
        
        $sections = $this->fetchAll($where);
        $this->cacheSet($where, $sections);
        return $sections;
    }
    
    /**
     * Изменить информацию о разделе в базе
     * 
     * @param int $id section id
     * @param array $data data
     */
    public function updateSection($id, array $data){
        switch(true){
            case !isset($data['flags']):
                break;

            case is_array($data['flags']):
                $data['flags'] = HM_Forum_Section_SectionModel::flagsEncode($data['flags']);
                break;

            case $data['flags'] instanceof HM_Forum_Library_Flags:
                $data['flags'] = $data['flags']->getEncoded();
                break;
        }
            
        $data['updated'] = $this->getDateTime();
        unset($data['section_id']);        
        $this->_prepareData(&$data);
        
        $this->updateWhere($data, 'section_id = ' . $id);
        $this->cacheUnset($id);
    }
    
    /**
     * Обновить время последнего сообщения
     * 
     * @param int $id section id
     */
    public function incMessagesCounter($id){
        $this->updateWhere(
            array(
                'last_msg'  => $this->getDateTime(),
                'count_msg' => new Zend_Db_Expr('count_msg + 1')
            ),
            'section_id = ' . $id
        );
        $this->cacheUnset($id);
    }
    
    /**
     * Удалить раздел
     * 
     * @param int $id section id
     * @return array affected id's
     */
    public function deleteSection($id){
        $idsList = $this->_getAllChildsIds($id);
        if(is_array($id)) $idsList += $id;
        else $idsList[] = $id;
        
        $this->deleteBy(array('section_id IN(?)' => $idsList));
        
        $delList = array();
        foreach($idsList as $id) $delList[] = array('section_id' => $id);
        $this->cacheUnset($delList, true);
        
        return $idsList;
    }
    
    /**
     * Удалить разделы в определённом подразделе
     * 
     * @param int $id parent id
     */
    public function deleteSectionsByParentId($id){        
        if(is_array($id)) $where = array('parent_id IN(?)' => $id);
        else $where = array('parent_id = ?' => $id);
        
        $this->deleteBy($where);
    }
    
    /**
     * Удалить разделы определённого форума
     * 
     * @param int $id forum id
     */
    public function deleteSectionsByForumId($id){
        $this->deleteBy(array('forum_id = ?' => $id));
    }
    
    /**
     * Получить список всех дочерних id раздела включая их подразделы (рекурсивно)
     * 
     * @param int | array $id id
     * @return array id's list
     */
    protected function _getAllChildsIds($id){
        $allChilds = array();
        
        if(is_array($id)) $where = array('parent_id IN(?)' => $id);
        else $where = array('parent_id = ?' => $id);
        
        $childs = $this->fetchAll($where);
        foreach($childs as $child) $allChilds[] = $child->section_id;
        
        if(!empty($allChilds)) $allChilds += $this->_getAllChildsIds($allChilds);
        
        return $allChilds;
    }


    /**
     * Показывает количество неудаленных сообщений
     * @param HM_Forum_Section_SectionModel $section
     * @param null $messages
     * @param null $userID - пользователь для которого проверяется видимость
     * @param null $parentMessage
     */
    public function getMessagesCount($section, $messages = null, $userID = null, $parentMessage = null)
    {
        $count = 0;
        $messages = ($messages === null)? $this->getService('ForumMessage')->getMessagesList($section->forum_id, $section->section_id ) /*$section->messages*/ : $messages;
        $userID   = ($userID === null)? $this->getService('User')->getCurrentUserId() : $userID;

        if (!count($messages)) return 0;

        foreach ($messages as $message) {
            // видимые/невидимые сообщения сейчас считаем всегда, а удаленные не считаем никогда
            //if ($this->isVisibleMessage($section, $message, $userID, $parentMessage)) {
            if (!$message->flags->deleted) {
                $count++;
            }
            $answers = $message->getAnswers(true);
            if (count($answers)) {
                $count += $this->getMessagesCount($section, $answers, $userID, $message);
            }
        }

        return $count;
    }

    /**
     * Проверяет видимость сообщения
     * @param $section
     * @param $message
     * @param $userID
     * @param null $parentMessage
     * @return bool
     */
    public function isVisibleMessage($section, $message, $userID = null, $parentMessage = null)
    {
        $userID   = ($userID === null)? $this->getService('User')->getCurrentUserId() : $userID;

        // сообщение помечено как удаленное
       /*if ( $message->flags->deleted ) {
           return false;
       } */
        // обработка сообщений в скрытых темах
        if ($section->is_hidden
            && $section->user_id != $userID
            && $message->user_id != $userID
            && $message->user_id != $section->user_id)
        {
            return false;
        }
        // обработка скрытых сообщений
        $parentAuthor = ($parentMessage !== null)? $parentMessage->user_id : 0;
        if ($message->is_hidden
            && $message->user_id != $userID
            && $section->user_id != $userID
            && $parentAuthor     != $userID)
        {
            return false;
        }

        return true;
    }
}