<?php

/**
 * @author Mike
 * @version 0.5
 * @package HM_Forum
 * @copyright Hypermethod
 * 
 * @property int    $message_id
 * @property int    $forum_id
 * @property int    $section_id
 * @property int    $user_id
 * @property string $user_name
 * @property string $user_ip
 * @property int    $level Уровень вложенности сообщения в древовидной структуре
 * @property int    $answer_to
 * @property string $title
 * @property string $text
 * @property string $text_preview
 * @property string $created
 * @property string $updated
 * @property string $teacher_comment
 * @property int    $rating
 * @property bool   $new
 * @property bool   $showed
 * 
 * @property int    $subject_id ID курса (Только для форумов имеющих отношеник к занятиям)
 * @property int    $lesson_id ID занятия
 * 
 * @property bool $flags->active     Сообщение показывается
 * @property bool $flags->onlyauthor Сообщение показывается только автору темы
 * @property bool $flags->private    Сообщение показывается только по списку допусков пользователей
 * @property bool $flags->deleted    Сообщение отмеченно как удалённое
 */
class HM_Forum_Message_MessageModel extends HM_Forum_Library_ModelAbstract
{    
    const FLAG_ACTIVE     = 1;
    const FLAG_ONLYAUTHOR = 2;
    const FLAG_PRIVATE    = 3;
    const FLAG_DELETED    = 4;
    
    static protected $flagsPattern = array(
        'active'     => self::FLAG_ACTIVE,
        'onlyauthor' => self::FLAG_ONLYAUTHOR,
        'private'    => self::FLAG_PRIVATE,
        'deleted'    => self::FLAG_DELETED
    );
    
    /**
     * Список ответов на сообщение
     * 
     * @var HM_Forum_Message_MessageModel[]
     */
    protected $_answers = array();
    
    protected $_primaryName = 'message_id';
    
    /**
     * Добавить сообщение-ответ на данное сообщение
     * 
     * @param $answer HM_Forum_Message_MessageModel
     */
    public function addAnswer(HM_Forum_Message_MessageModel $answer){
        $this->_answers[$answer->message_id] = $answer;
    }
    
    /**
     * Получить список сообщений-ответов на это сообщение (каждое из которых тоже может содержать список ответов)
     * 
     * @param bool $recursively Всё дерево ответов в виде плоского списка
     * @return HM_Forum_Message_MessageModel[]
     */
    public function getAnswers($recursively = null){
        if(!$recursively) return $this->_answers;
        
        $answers = array();
        foreach($this->_answers as $answer) $answers = $answers + $answer->getAnswers(true);
        
        return $this->_answers + $answers;
    }
    
    /**
     * Сортировать ответы на сообщение по времени создания
     * 
     * @param bool $reverse обратная сортировка (последние наверх)
     */
    public function sortAnswersByTime($reverse = null){
        $timeSorted = array();
        foreach($this->_answers as &$answer){
            $timeSorted[strtotime($answer->created)] = &$answer;
            $answer->sortAnswersByTime($reverse);
        }
        
        $reverse ? krsort($timeSorted) : ksort($timeSorted);
        
        $this->_answers = array();
        foreach($timeSorted as &$answer) $this->_answers[$answer->message_id] = &$answer;
    }
    
    /**
     * URL сообщения
     * 
     * @param array параметры
     * @param string название роута
     * @param bool очистить параметры
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
                    self::PARAM_SECTION_ID => $this->_data['section_id'],
                    self::PARAM_MESSAGE_ID => $this->_data['message_id']
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
                    self::PARAM_SECTION_ID => $this->_data['section_id'],
                    self::PARAM_MESSAGE_ID => $this->_data['message_id']
                );
        }
        
        return $this->_router->assemble($url, $name, $reset, $encode);
    }
    
    /**
     * Удалить сообщение 
     */
    public function delete(){
        $this->getService()->deleteMessage($this);
        $this->flags->deleted = true;
    }
    
    /**
     * Выставить оценку сообщения
     * 
     * @param int $rating 
     */
    public function setRating($rating){
        $this->getService()->setMessageRating($this, $rating);
    }
    
    /**
     * Пометить сообщение как прочитанное
     */
    public function markShowed(){
        $this->getService()->markMessageShowed($this);
    }
}