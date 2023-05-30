<?php
class HM_Metadata_Group_GroupModel extends HM_Model_Abstract
{
    public function getRoles()
    {
        $roles = array();
        if (strlen($this->roles)) {
            $roles = explode('~|~', $this->roles);
        }
        return $roles;
    }

    public function getItems()
    {
        if (isset($this->items) && count($this->items)) {
            return $this->items;
        }
        return false;
    }
}