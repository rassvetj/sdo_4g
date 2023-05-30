<?php
class HM_Activity_Resource_ResourceModel extends HM_Model_Abstract
{
    const TYPE_ACTIVITY_FORUM = 2;
    const TYPE_ACTIVITY_BLOG = 64;
    const TYPE_ACTIVITY_CHAT = 512;
    const TYPE_ACTIVITY_WIKI = 128;   

    static public function getActivityTypes()
    {
        return array(
            self::TYPE_ACTIVITY_FORUM => _('Тема форума'),
            self::TYPE_ACTIVITY_CHAT => _('Канал чата'),
            self::TYPE_ACTIVITY_BLOG => _('Блог'),                
            self::TYPE_ACTIVITY_WIKI => _('Wiki'),
        );
    }    
    
    static public function factory($data, $default = 'HM_Activity_Resource_ResourceModel')
    {

        if (isset($data['activity_type']))
        {
            switch($data['activity_type']) {
                case self::TYPE_ACTIVITY_FORUM:
                    return parent::factory($data, 'HM_Activity_Resource_Type_ForumModel');
                    break;
                case self::TYPE_ACTIVITY_CHAT:
                    return parent::factory($data, 'HM_Activity_Resource_Type_ChatModel');
                    break;
                case self::TYPE_ACTIVITY_BLOG:
                    return parent::factory($data, 'HM_Activity_Resource_Type_BlogModel');
                    break;
                case self::TYPE_ACTIVITY_WIKI:
                    return parent::factory($data, 'HM_Activity_Resource_Type_WikiModel');
                    break;
            }
            return parent::factory($data, $default);
        }
    }
    
}