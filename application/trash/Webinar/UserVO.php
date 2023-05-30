<?php
class Webinar_UserVO extends Webinar_VO {
    public $_explicitType = 'com.hypermethod.eLearning3000.Webinar.vo.PersonVO';

    public $id;
    public $lastName;
    public $firstName;
    public $middleName;
    public $role;
    public $current = false;
    public $status = 'offline';

    public function getASClassName() {
        return 'com.hypermethod.eLearning3000.Webinar.vo.PersonVO';
    }
}