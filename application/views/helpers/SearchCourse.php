<?php
class HM_View_Helper_SearchCourse extends HM_View_Helper_Abstract
{
    
    public function searchCourse($courseModel, $count)
    {
        
        $this->view->courseModel = $courseModel;
        $this->view->count = $count;
        
        return $this->view->render('searchCourse.tpl');
        
        
        
    }
    
    
    
    
    
    
}