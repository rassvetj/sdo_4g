<?php

/**
 * @author Mike
 * @version 0.5
 * @package HM_Forum
 * @copyright Hypermethod
 * 
 * @property int $section_id
 * @property int $lesson_id ID занятия
 * @property int $parent_id
 * @property int $forum_id
 * @property int $user_id
 * @property string $user_name
 * @property string $user_ip
 * @property string $title
 * @property string $text
 * @property string $created
 * @property string $updated
 * @property string $last_msg
 * @property int $count_msg
 * @property int $order
 * 
 * @property int $subject_id ID курса (Только для форумов имеющих отношеник к занятиям)
 * @property HM_Forum_Message_MessageModel[] $messages Сообщения раздела (Сущность не обязательная, может не существовать)
 * @property HM_Forum_Section_SectionModel[] $subsections Подразделы раздела (Сущность не обязательная, может не существовать)
 * 
 * @property bool $flags->active  раздел активен и доступен для пользователей
 * @property bool $flags->theme   раздел является темой, не может содержать подразделы, может содержать только сообщения
 * @property bool $flags->closed  раздел закрыт и доступен только для чтения
 * @property bool $flags->private доступ в раздел только по списку допусков пользователей
 */
class HM_Forum_Section_SectionModel extends HM_Forum_Library_ModelAbstract
{
    const FLAG_ACTIVE  = 1;
    const FLAG_THEME   = 2;
    const FLAG_CLOSED  = 3;
    const FLAG_PRIVATE = 4;
    
    static protected $flagsPattern = array(
        'active'  => self::FLAG_ACTIVE,
        'theme'   => self::FLAG_THEME,
        'closed'  => self::FLAG_CLOSED,
        'private' => self::FLAG_PRIVATE
    );
    
    public function __construct($data){
        parent::__construct($data);
        // Если не было сообщений в разделе/теме, дата последнего сообщения равна дате создания раздела/темы
        if(!strtotime($this->_data['last_msg'])) $this->_data['last_msg'] = $this->_data['created'];
    }
    
    /**
     * Раздел прикреплён
     * 
     * @return bool
     */
    public function isPinned(){
        return $this->_data['order'] > 0;
    }
    
    /**
     * URL раздела
     * 
     * @param array $url параметры
     * @param string $name название роута
     * @param bool $reset очистить параметры
     * 
     * @return string
     */
    public function url(array $url = array(), $name = null, $reset = true, $encode = true){
        if($name === null) $name = $this->_routeName;
        
        switch($name){
            // Для форумов имеющих отношение к занятиям
            case self::ROUTE_SUBJECT:
                $params = array(
                    HM_Controller_Action_Activity::PARAM_SUBJECT_ID => $this->_data['subject_id'],
                    self::PARAM_SECTION_ID => $this->_data['section_id']
                );
                
                if($this->_data['lesson_id'] > 0){
                    $params[HM_Controller_Action_Activity::PARAM_LESSON_ID] = $this->_data['lesson_id'];
                }
                
                $url = $url + $params;
                break;
                
            // Для форумов общего типа (На пример форум портала)
            case self::ROUTE_FORUM:                
            default:
                $url = $url + array(
                    self::PARAM_FORUM_ID   => $this->_data['forum_id'],
                    self::PARAM_SECTION_ID => $this->_data['section_id']
                );
        }
        
        return $this->_router->assemble($url, $name, $reset, $encode);
    }
    
    /**
     * Изменить приоритет вывода раздела
     *
     * @param int $order приоритет
     */
    public function setOrder($order = 0){
        $order = (int) $order;
        if($order > 1000 || $order < -1000) $order = 0;
        
        $this->getService()->setOrderOfSection($this, $order);
    }
    
    /**
     * Закрыть/открыть раздел
     * 
     * @param bool $flag true = закрыть, false = открыть
     */
    public function setClosed($flag = true){
        $this->getService()->setClosedFlagsOfSection($this, (bool) $flag);
    }
    
    /**
     * Удалить раздел 
     */
    public function delete(){
        $this->getService()->deleteSection($this);
    }
}