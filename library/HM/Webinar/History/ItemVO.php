<?php
class HM_Webinar_History_ItemVO extends HM_Webinar_VO {
    public $_explicitType = 'com.hypermethod.eLearning3000.Webinar.vo.HistoryItemVO';

    public $id;
    public $pointId;
    public $userId;
    public $action;
    public $item;
    public $datetime;

    public function getASClassName() {
        return 'com.hypermethod.eLearning3000.Webinar.vo.HistoryItemVO';
    }
}