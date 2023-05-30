<?php

/**
 * @author Mike
 * @version 0.5
 * @package HM_Forum
 * @copyright Hypermethod
 * 
 * @property int $forum_id
 * @property int $subject_id ID занятия, если форум имеет отношение к таковому (0 - если не имеет отношения)
 * @property int $user_id
 * @property string $user_name
 * @property string $user_ip
 * @property string $title
 * @property string $created
 * @property string $updated
 * 
 * @property bool $flags->active      форум активен и доступен для пользователей
 * @property bool $flags->subsections структура форума имеет подразделы
 * @property bool $flags->subsecttree структура форума допускает более одного уровня вложенности подразделов
 * @property bool $flags->closed      форум закрыт и доступен только для чтения
 * @property bool $flags->private     доступ на форум только по списку допусков пользователей
 * 
 * @property Zend_Config $config Конфигурация форума использованная в запросе
 * @property bool $moderator Пользователь зашедший на форум является модератором
 * @property HM_Forum_Section_SectionModel $section Запрошенный раздел форума (Сущность не обязательная, может не существовать)
 */
class HM_Forum_Forum_ForumModel extends HM_Forum_Library_ModelAbstract
{
    
    const FLAG_ACTIVE      = 1;
    const FLAG_SUBSECTIONS = 2;
    const FLAG_SUBSECTTREE = 3;
    const FLAG_CLOSED      = 4;
    const FLAG_PRIVATE     = 5;
    
    static protected $flagsPattern = array(
        'active'      => self::FLAG_ACTIVE,
        'subsections' => self::FLAG_SUBSECTIONS,
        'subsecttree' => self::FLAG_SUBSECTTREE,
        'closed'      => self::FLAG_CLOSED,
        'private'     => self::FLAG_PRIVATE
    );
    
    /**
     * URL форума
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
                $url = $url + array(
                    HM_Controller_Action_Activity::PARAM_SUBJECT_ID => $this->_data['subject_id'],
                    self::PARAM_SECTION_ID => null
                );
                break;
            
            // Для форумов общего типа (На пример форум портала)
            case self::ROUTE_FORUM:                
            default:
                $url = $url + array(
                    self::PARAM_FORUM_ID   => $this->_data['forum_id'],
                    self::PARAM_SECTION_ID => null
                );
        }
        
        return $this->_router->assemble($url, $name, $reset, $encode);
    }
    
}