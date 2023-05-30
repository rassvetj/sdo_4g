<?php
class Webinar_Chat_MessageVO extends Webinar_VO {
    public $_explicitType = 'com.hypermethod.eLearning3000.Webinar.vo.ChatMessageVO';

    public $id;
    public $pointId;
    public $userId;
    public $message;
    public $datetime;    

    public function getASClassName() {
        return 'com.hypermethod.eLearning3000.Webinar.vo.ChatMessageVO';
    }
}