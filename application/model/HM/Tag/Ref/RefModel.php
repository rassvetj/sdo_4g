<?php
class HM_Tag_Ref_RefModel extends HM_Model_Abstract implements HM_Tag_Ref_RefModel_Interface
{
    /**
    * ВАЖНО
    * При добавлении или изменении типов,
    * не забывать менять их значения в виде БД 'kbase_items'
    */
    const TYPE_BLOG      = 0; // нужна ещё проверка на subject
    const TYPE_RESOURCE  = 1;
    const TYPE_COURSE    = 2;
    const TYPE_TEST      = 3;
    const TYPE_EXERCISES = 4;
    const TYPE_POLL      = 5;
    const TYPE_TASK      = 6;
    const TYPE_USER      = 10;

    public function getType(){}

    static public function factory($data, $default = 'HM_Tag_Ref_RefModel')
    {

        if ( !isset($data['item_type']) ) return parent::factory($data, $default);

        switch ( $data['item_type'] ) {
            case self::TYPE_BLOG      : return parent::factory($data, 'HM_Tag_Ref_Blog_BlogModel');
                                        break;
            case self::TYPE_RESOURCE  : return parent::factory($data, 'HM_Tag_Ref_Resource_ResourceModel');
                                        break;
            case self::TYPE_COURSE    : return parent::factory($data, 'HM_Tag_Ref_Course_CourseModel');
                                        break;
            case self::TYPE_TEST      : return parent::factory($data, 'HM_Tag_Ref_Test_TestModel');
                                        break;
            case self::TYPE_EXERCISES : return parent::factory($data, 'HM_Tag_Ref_Exercises_ExercisesModel');
                                        break;
            case self::TYPE_POLL      : return parent::factory($data, 'HM_Tag_Ref_Poll_PollModel');
                                        break;
            case self::TYPE_TASK      : return parent::factory($data, 'HM_Tag_Ref_Task_TaskModel');
                                        break;
            case self::TYPE_USER      : return parent::factory($data, 'HM_Tag_Ref_User_UserModel');
                                        break;
            default: return parent::factory($data, $default);
        }
    }

    static function getAllTypes()
    {
        return array(
                    self::TYPE_BLOG   => _('Блог'),
                    self::TYPE_RESOURCE => _('Информационные ресурсы'),
                    self::TYPE_COURSE => _('Учебные модули'),
                    self::TYPE_EXERCISES => _('Упражнения'),
                    self::TYPE_POLL => _('Опросы'),
                    self::TYPE_TASK => _('Задания'),
                    self::TYPE_TEST => _('Тесты'),
                    self::TYPE_USER => _('Пользователи'),
                    );
    }

    static function getBZTypes()
    {
        return array(
                    self::TYPE_RESOURCE => _('Информационные ресурсы'),
                    self::TYPE_COURSE => _('Учебные модули'),
                    //self::TYPE_EXERCISES => _('Упражнения'),
                    //self::TYPE_POLL => _('Опросы'),
                    //self::TYPE_TASK => _('Задания'),
                    //self::TYPE_TEST => _('Тесты'),
                    //self::TYPE_USER => _('Пользователи'), // #12068
                    );
    }

    static function getTypeTitle($type)
    {
        $types = self::getAllTypes();
        return ( array_key_exists($type, $types) )? $types[$type] : '';
    }
}