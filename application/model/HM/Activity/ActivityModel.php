<?php
class HM_Activity_ActivityModel extends HM_Model_Abstract
{
    const ACTIVITY_NEWS       = 1; // < 0 зарезервировано под Custom Events
    const ACTIVITY_FORUM      = 2;
    const ACTIVITY_OPROS      = 4;
    const ACTIVITY_LIBRARY    = 8;
    const ACTIVITY_CONTACT    = 16;
    const ACTIVITY_MESSAGES   = 32;
    const ACTIVITY_BLOG       = 64;
    const ACTIVITY_WIKI       = 128;
    const ACTIVITY_VIDEOCHAT  = 256;
    const ACTIVITY_CHAT       = 512; // 513-4095 - зарезервировано под внутренние eventId
    const ACTIVITY_CONTACTS   = 4096;
    const ACTIVITY_TAG        = 8192;

    static public function getActivityServices()
    {
        return array(
            self::ACTIVITY_FORUM => "Forum",
            self::ACTIVITY_NEWS  => "News",
            self::ACTIVITY_WIKI  => "WikiArticles",
            self::ACTIVITY_CHAT  => "ChatChannels"
        );
    }

    static public function getActivityService($activity) {
        $services = self::getActivityServices();
        if (!isset($services[$activity])) {
            throw new HM_Exception(sprintf(_('Не найден сервисный слой для данного вида взаимодействия: %d'), $activity));
        }
        return $services[$activity];
    }



    /*
     * Для того чтобы изменить названия в unmanaged
     * нужно просто пересохранить сервисы в настройках у админа!!!!!!!!!
     *
     * UPD: Уже не нужно пересохранять, при сохарнении сервисов взаимодействия в админке, названия сейчас сохраняются без перевода,
     *      и переводятся только при построении меню.
     */
    static public function getTabActivities($translate = true)
    {
        $activities = array(
            self::ACTIVITY_NEWS       => 'Новости',
            self::ACTIVITY_FORUM      => 'Форум',
            self::ACTIVITY_BLOG       => 'Блог',
            self::ACTIVITY_WIKI       => 'Wiki',
            //self::ACTIVITY_OPROS      => 'Опрос',
            self::ACTIVITY_CHAT       => 'Чат',
            //self::ACTIVITY_VIDEOCHAT  => 'Видеочат',
            self::ACTIVITY_CONTACT    => 'Контакты',
            self::ACTIVITY_MESSAGES   => 'Сообщения',
            self::ACTIVITY_LIBRARY    => 'Файловое хранилище'
        );

        return ($translate)? array_map('_', $activities) : $activities;
    }

    static public function getLessonActivities()
    {
        return array(
            self::ACTIVITY_FORUM      => _('Форум'),
            self::ACTIVITY_WIKI       => _('Wiki'),
            self::ACTIVITY_CHAT       => _('Чат'),
            //self::ACTIVITY_VIDEOCHAT  => _('Видеочат'),
            self::ACTIVITY_LIBRARY    => _('Файловое хранилище')
        );
    }

    static public function getTabUrls()
    {
        return array(
            self::ACTIVITY_NEWS => array(
                'module' => 'news',
                'controller' => 'index',
                'action' => 'index'
            ),
            self::ACTIVITY_BLOG => array(
                'module' => 'blog',
                'controller' => 'index',
                'action' => 'index'
            ),
            self::ACTIVITY_FORUM =>  array(
                'module' => 'forum',
                'controller' => 'index',
                'action' => 'index',
                'route' => 'forum_subject'
            ),
            self::ACTIVITY_CHAT => array(
                'module' => 'chat',
                'controller' => 'index',
                'action' => 'index'
            ),
            self::ACTIVITY_CONTACT => array(
                'module' => 'message',
                'controller' => 'contact',
                'action' => 'index'
            ),
            self::ACTIVITY_LIBRARY => array(
                'module' => 'storage',
                'controller' => 'index',
                'action' => 'index'
            ),
            self::ACTIVITY_MESSAGES => array(
                'module' => 'message',
                'controller' => 'view',
                'action' => 'index',
            ),
            self::ACTIVITY_OPROS => '',
            self::ACTIVITY_VIDEOCHAT => '',
            self::ACTIVITY_WIKI => array(
                'module' => 'wiki',
                'controller' => 'index',
                'action' => 'index',
            )
        );
    }


    static public function getContextUrls()
    {
        return array(
            self::ACTIVITY_NEWS => array(
                'module' => 'news',
                'controller' => 'index',
                'action' => 'index'
            ),
            self::ACTIVITY_BLOG => array(
                'module' => 'blog',
                'controller' => 'index',
                'action' => 'index'
            ),
            self::ACTIVITY_FORUM =>  array(
                'module' => 'forum',
                'controller' => 'index',
                'action' => 'index',
                'route' => 'forum_subject'
            ),
            self::ACTIVITY_CHAT =>  array(
                'module' => 'chat',
                'controller' => 'index',
                'action' => 'index'
            ),
            self::ACTIVITY_CONTACT => array(
                'module' => 'message',
                'controller' => 'contact',
                'action' => 'index'
            ),
            self::ACTIVITY_LIBRARY => array(
                'module' => 'storage',
                'controller' => 'index',
                'action' => 'index'
            ),
            self::ACTIVITY_MESSAGES => array(
                'module' => 'message',
                'controller' => 'view',
                'action' => 'index',
            ),
            self::ACTIVITY_OPROS => '#',
            self::ACTIVITY_VIDEOCHAT => '#',
            self::ACTIVITY_WIKI => array(
                'module' => 'wiki',
                'controller' => 'index',
                'action' => 'index',
            )
        );
    }

    static public function getCollaborationUrls()
    {
        return array(
            self::ACTIVITY_NEWS => array(
                'module' => 'news',
                'controller' => 'index',
                'action' => 'index'
            ),
            self::ACTIVITY_BLOG => array(
                'module'     => 'blog',
                'controller' => 'index',
                'action'     => 'index'
            ),
            self::ACTIVITY_FORUM =>  array(
                'module' => 'forum',
                'controller' => 'index',
                'action' => 'index',
                'route' => 'forum'
            ),
            self::ACTIVITY_CHAT => array(
                'module' => 'chat',
                'controller' => 'index',
                'action' => 'index'
            ),
            self::ACTIVITY_CONTACT => array(
                'module' => 'message',
                'controller' => 'contact',
                'action' => 'index',
            ),
            self::ACTIVITY_LIBRARY => array(
                'module' => 'storage',
                'controller' => 'index',
                'action' => 'index'
            ),
            self::ACTIVITY_MESSAGES => array(
                'module' => 'message',
                'controller' => 'view',
                'action' => 'index',
            ),
            self::ACTIVITY_OPROS => array(
                'module' => '',
                'controller' => '',
                'action' => '',
            ),
            self::ACTIVITY_VIDEOCHAT => array(
                'module' => '',
                'controller' => '',
                'action' => '',
            ),
            self::ACTIVITY_WIKI => array(
                'module' => 'wiki',
                'controller' => 'index',
                'action' => 'index',
            )
        );
    }

    static public function getEventActivities()
    {
        return array(
            self::ACTIVITY_FORUM      => _('Форум'),
/*            self::ACTIVITY_CHAT       => _('Чат'),
            self::ACTIVITY_OPROS      => _('Опрос'),
            self::ACTIVITY_VIDEOCHAT  => _('Видеочат'),*/
            self::ACTIVITY_CHAT       => _('Чат'),
            self::ACTIVITY_WIKI       => _('Wiki')
        );
    }
    
    /**
     * Возвращает массив с типами сервисов взаимодействия 
     * на основе которых можно создать занятие 
     * даже если сам сервис отключен для курса.
     * Перечисленные ключи должны присутствовать в getEventActivities()
     */
    static public function getFreeEventActivities()
    {
        return array(self::ACTIVITY_FORUM);
    }

}