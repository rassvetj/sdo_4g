<?php
class HM_Activity_Resource_Type_WikiModel extends HM_Activity_Resource_ResourceModel
{
    public function getDefaultName()
    {
        return sprintf(_('Wiki в курсе %s'), '"' . $this->subject_name . '"');
    }
    
    public function getUrl()
    {
        return Zend_Registry::get('view')->url(array(
            'module' => 'wiki',        
            'controller' => 'index',        
            'action' => 'index',        
            'subject' => 'subject',        
            'subject_id' => $this->subject_id,       
            'activity_resource_id' => $this->resource_id,        
            'resource_id' => $this->resource_id,      
        ));
    }    
}