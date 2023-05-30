<?php
class Webinar_Plan_ItemVO extends Webinar_VO {
    public $_explicitType = 'com.hypermethod.eLearning3000.Webinar.vo.PlanItemVO';

    public $id;
    public $parentId;
    public $href;
    public $title;
    public $pointId;

    public function getASClassName() {
        return 'com.hypermethod.eLearning3000.Webinar.vo.PlanItemVO';
    }
}