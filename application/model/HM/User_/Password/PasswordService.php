<?php
class HM_User_Password_PasswordService extends HM_Service_Abstract
{

  public function getChangePasswordLastDate($userId)
  {
      $userId = (int) $userId;
      
      $res = $this->getOne($this->fetchAll(array('user_id = ?' => $userId), array('change_date DESC'), 1));
      if(!$res){
          return false;
      }
      return $res->change_date;
      
  }

  public function getLastPasswords($userId, $amount)
  {
      $userId = (int) $userId;
      $res = $this->fetchAll(array('user_id = ?' => $userId), array('change_date DESC'), $amount);
      return $res;
  }
  

  
  
    
}