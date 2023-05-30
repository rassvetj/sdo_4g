<?php

class HM_Course_Item_Resource_ResourceModel extends HM_Course_Item_ItemModel{

    public function getExecuteUrl(){
        return Zend_Registry::get('view')->url(
            array(
                'module' => 'resource',
                'controller' => 'index',
                'action' => 'view',
                'resource_id' => $this->vol2
             )
        );

    }

}