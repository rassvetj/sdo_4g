<?php

/**
 * Модель списка сообщений в форумах
 * 
 * Реализует доступ к данным списка сообщений.
 * Не содержит бизнес-логики.
 * Ничего не знает о хранении других данных форума.
 */
class HM_Forum_Message_MessageService extends HM_Forum_Library_ServiceAbstract
{
    /**
     * Добавить сообщение
     * 
     * @param array $data данные сообщения
     * @return HM_Forum_Message_MessageModel
     */
    public function addMessage(array $data){
		$data['flags'] = HM_Forum_Message_MessageModel::flagsEncode($data['flags']);
        
        if(isset($data['answer_to']) && $data['answer_to'] > 0) $parent = (int) $data['answer_to'];
        else $parent = null;
        
        $data['created'] = $this->getDateTime();
        $this->_prepareData(&$data);
        $message = $this->insert($data, $parent);
        if ($message) $message->text_size = strlen($data['text']);
		
		
		//--выполняется также и на обычном форуме, не привязанном к сессии.
		if($this->getService('Acl')->inheritsRole(	$this->getService('User')->getCurrentUserRole(), //--фиксируем просрочку при ответе на форуме в сессии.
													array(HM_Role_RoleModelAbstract::ROLE_TEACHER, HM_Role_RoleModelAbstract::ROLE_TUTOR)
		)){					
			$this->getService('WorkloadForum')->setForumViolation($this->getService('User')->getCurrentUserId(), $data['forum_id'], $data['section_id']);
		}		
        return $message;
    }
    
    /**
     * Получить сообщение
     * 
     * @param int $id id
     * @return HM_Forum_Message_MessageModel | null 
     */
    public function getMessage($id){
        $message = $this->cacheGet($id);
        if($message) return $message;
        
        $message = $this->fetchRow(array('message_id = ?' => $id));
        if(!$message) $message = null;
        
        $this->cacheSet($id, $message);
        return $message;
    }
    
    /**
     * Получить список сообщений форума по всем темам или по конкретной теме
     * 
     * @param int $forumId id форума (по умолчанию без органичения по форуму)
     * @param int $sectionId id темы (по умолчанию по всем темам)
     * @param Zend_Config $config Структура вывода сообщений
     * @param int $userId Только новые сообщения для указанного ID пользователя
     * @return array
     */    
    public function getMessagesList($forumId = null, $sectionId = null, Zend_Config $config = null, $userId = null){
        if(is_null($config)) $config = new Zend_Config(array());
        $where = array();
        
        if(is_array($forumId)) $where['forum_id IN(?)'] = $forumId;
        elseif(is_numeric($forumId)) $where['forum_id = ?'] = $forumId;
        
        if(is_array($sectionId)) $where['section_id IN(?)'] = $sectionId;
        elseif(is_numeric($sectionId)) $where['section_id = ?'] = $sectionId;
        
        // Подзапрос к таблице просмотренных сообщений
        if($config->only_new && $userId){
            $config->as_tree = false; // Отключение древовидной структуры
            
            $subselect = $this->getSelect();
            $subselect->from('forums_messages_showed', 'message_id');
            $subselect->where('user_id = ?', $userId);
            
            $where['message_id NOT IN(?)'] = $subselect;
        }

        // !!! объект select нельзя кешировать (серелизовать)
        //     ибо разрывается коннект и сбрасываются форматы даты, кодировки и пр. что задается запросами при ините
        //$messages = $this->cacheGet(array($where, $config));
        //if($messages) return $messages;
        
        $table = $this->getMapper()->getTable();
        $cols = $table->info(HM_Db_Table::COLS);
        
        $select = $table->select()->from($table->getTableName(), $cols);
        foreach($where as $cond => $value) $select->where($cond, $value);
        
        $messages = $this->getMapper()->fetchMessagesList($select, $config);
        //$this->cacheSet(array($where, $config), $messages);
        
        return is_array($sectionId) ? $messages : $messages[$sectionId];
    }
    
    /**
     * Изменить информацию в сообщении в базе
     * 
     * @param int $id message id
     * @param array $data data
     */
    public function updateMessage($id, array $data){        
        switch(true){
            case !isset($data['flags']):
                break;

            case is_array($data['flags']):
                $data['flags'] = HM_Forum_Message_MessageModel::flagsEncode($data['flags']);
                break;

            case $data['flags'] instanceof HM_Forum_Library_Flags:
                $data['flags'] = $data['flags']->getEncoded();
                break;
        }
            
        $data['updated'] = $this->getDateTime();
        $data['text_size'] = strlen($data['text']);
        unset($data['message_id']);        
        $this->_prepareData(&$data);
        
        $this->updateWhere($data, 'message_id = ' . $id);
        $this->cacheUnset($id);
    }
    
    /**
     * Удалить сообщение
     * 
     * @param int $id message id 
     */
    public function deleteMessage($id){
        if(is_array($id)) $where = array('message_id IN(?)' => $id);
        else $where = array('message_id = ?' => $id);
        
        $this->deleteBy($where);
        $this->cacheUnset($id);
    }
    
    /**
     * Удалить сообщения в определённом подразделе
     * 
     * @param int $id section id 
     */
    public function deleteMessagesBySectionId($id){
        if(is_array($id)) $where = array('section_id IN(?)' => $id);
        else $where = array('section_id = ?' => $id);
        $this->deleteBy($where);
    }
    
    /**
     * Удалить сообщения определённого форума
     * 
     * @param int $id forum id
     */
    public function deleteMessagesByForumId($id){
        $this->deleteBy('forum_id = ' . $id);
    }
    
}