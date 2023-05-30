<?php
class HM_Activity_Resource_Type_ChatModel extends HM_Activity_Resource_ResourceModel
{
    public function getDefaultName()
    {
        return sprintf(_('Чат %s в курсе %s'), '"' . $this->activity_name . '"', '"' . $this->subject_name . '"'); // точнее, канал чата
    }
    
    public function getUrl()
    {
        return Zend_Registry::get('view')->url(array(
            'module' => 'chat',        
            'controller' => 'index',        
            'action' => 'index',        
            'subject' => 'subject',        
            'subject_id' => $this->subject_id,        
            'channel_id' => $this->activity_id,        
            'activity_resource_id' => $this->resource_id,        
            'resource_id' => null,      
            'revision_id' => null,      
        ));
    }
}