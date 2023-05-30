<?php
class HM_Activity_Resource_Type_ForumModel extends HM_Activity_Resource_ResourceModel
{
    public function getDefaultName()
    {
        return sprintf(_('Форум %s в курсе %s'), '"' . $this->activity_name . '"', '"' . $this->subject_name . '"'); // точнее, тема форуа
    }    
    public function getDefaultNameTranslate()
    {
        return sprintf(_('Forum %s on the training course %s'), '"' . $this->activity_name . '"', '"' . $this->subject_name . '"'); // точнее, тема форуа
    }    
    public function getUrl()
    {
        return sprintf('/forum/subject/subject/%s/%s/?activity_resource_id=%s', $this->subject_id, $this->activity_id, $this->resource_id);
    }    
}