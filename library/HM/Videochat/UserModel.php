<?php
class HM_Videochat_UserModel extends HM_Videochat_VOModel {
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