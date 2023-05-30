<?php
class HM_User extends HM_Object {
    public function getName()
    {
    	return $this->LastName.' '.$this->FirstName;
    }
}