<?php
class HM_Webinar_Files_FilesService extends HM_Service_Abstract
{
    
    /**
     * Получаем файлы для занятия
     * @param unknown_type $pointId
     * @return string
     */
    public function getFilesForLesson($pointId)
    {
    
        $lesson = $this->getOne($this->getService('Lesson')->find($pointId));
        if(!$lesson)
            return false;

        $webinarId = $lesson->getModuleId();
        $result = $this->getFiles($webinarId);
        
        if(!$result)
            return false;
        $list = array();
        $count = 0;
        foreach ($result as $value){
            $object = new HM_Webinar_Plan_ItemVO();
            
            $object->id       = $value->file_id;
            $object->parentId = 0;
            $object->title    = iconv(Zend_registry::get('config')->charset, 'UTF-8',$value->name);
            $object->href     = ( strpos($value->path,'youtube_http://')===0 ) ? $value->path : $value->getUrl();
            $object->num      = $count++;
            $object->pointId  = $pointId;
            
           $list[] = $object; 
        }
            
        return $list;            
    }
    
    public function getFilesForWebinar($webinarId)
    {
        $result = $this->getFiles($webinarId);
        
        if(!$result)
            return false;
        $list = array();
        $count = 0;
        foreach ($result as $value){
            $object = new HM_Webinar_Plan_ItemVO();

            $object->id       = $value->file_id;
            $object->parentId = 0;
            $object->title    = iconv(Zend_registry::get('config')->charset, 'UTF-8',$value->name);
            $object->href     = ( strpos($value->path,'youtube_http://')===0 ) ? $value->path : $value->getUrl();
            $object->num      = $count++;
            $object->pointId  = $webinarId;
            
           $list[] = $object; 
        }
        return $list;            
    }
    
    
    
    
    /**
     * Получаем файлы для вебинара
     * @param unknown_type $webinarId
     */
    public function getFiles($webinarId)
    {
        $select = $this->getSelect();
        $select->from(array('t1' => 'files'), 't1.*')
                ->joinInner(array('t2' => 'webinar_files'), 't2.file_id = t1.file_id', array())
                ->where('t2.webinar_id = ?', $webinarId)
                ->order('t2.num');

        $ret = array();

        $smt = $select->query();
        if ($smt) {
            $ret = $smt->fetchAll();
        }

        return new HM_Collection($ret, 'HM_Files_FilesModel');

        //$res = $this->getService('Files')->fetchAllDependenceJoinInner('Webinar', 'Webinar.webinar_id = ' . (int) $webinarId, 'Webinar.num');
        //return $res;
    }

}