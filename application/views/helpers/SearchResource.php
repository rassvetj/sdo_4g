<?php
class HM_View_Helper_SearchResource extends HM_View_Helper_Abstract
{
    
    public function searchResource($resourceModel, $count)
    {
        
        $this->view->resourceModel = $resourceModel;
        $this->view->count = $count;
        
        return $this->view->render('searchResource.tpl');
        
        
        
    }
    
    
    
    
    
    
}