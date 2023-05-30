<?php
class HM_Webinar_Chat_ItemVO extends HM_Webinar_VO {
    public $_explicitType = 'com.hypermethod.eLearning3000.Webinar.vo.ChatMessageVO';

    public $id;
    public $pointId;
    public $userId;
    public $action;
    public $item;
    public $datetime;
    public $message;

    public function getASClassName() {
        return 'com.hypermethod.eLearning3000.Webinar.vo.ChatMessageVO';
    }
}