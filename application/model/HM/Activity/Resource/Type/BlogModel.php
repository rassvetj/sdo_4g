<?php
class HM_Activity_Resource_Type_BlogModel extends HM_Activity_Resource_ResourceModel
{
    public function getDefaultName()
    {
        return sprintf(_('Блог по курсу %s'), '"' . $this->subject_name . '"');
    }
    
    public function getUrl()
    {
        return Zend_Registry::get('view')->url(array(
            'module' => 'blog',        
            'controller' => 'index',        
            'action' => 'index',        
            'subject' => 'subject',        
            'subject_id' => $this->subject_id,        
            'activity_resource_id' => $this->resource_id,        
            'resource_id' => $this->resource_id,        
        ));
    }    
}