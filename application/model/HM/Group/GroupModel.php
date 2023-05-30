<?php

class HM_Group_GroupModel extends HM_Model_Abstract
{
    public function getUsers()
    {
        $result = array();

        if (isset($this->users)) {
            return $this->users;
        }

        return $result;
    }
    
    public function getName(){
        return $this->name;    
    }
}