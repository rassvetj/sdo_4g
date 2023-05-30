<?php

class HM_Ppt2swf_Ppt2swfModel extends HM_Model_Abstract
{
    
    // Статусы
    const STATUS_PREPARE   = 0; // Файл только загружен в систему
    const STATUS_PROCESS   = 1; // Файл конвертируется в данный момент
    const STATUS_READY     = 2; // Файл готов к загрузке
    const STATUS_ERROR     = 3; // Проызошел ошибк(( пичаль(
    
    public static function getStatuses(){
        return array(self::STATUS_PREPARE => _('Ожидает очереди'),
                     self::STATUS_PROCESS => _('В обработке'),
                     self::STATUS_READY   => _('Презентация готова'),
                     self::STATUS_ERROR   => _('Произошла ошибка. Проверьте презентацию и повторите запрос.'),
        );
    }

    public static function getStatus($status){
        $statuses = self::getStatuses();
        return $statuses[$status];
    }
    
}