<?php
class HM_Htmlpage_HtmlpageService extends HM_Service_Abstract
{
    public function delete($id){

        $page = $this->getOne($this->find($id));
        parent::delete($id);
        
        // файл удаляется здесь, а генерится в 1main.php при первом последующем запуске
        $file = HM_Htmlpage_HtmlpageModel::getActionsPath();
        if (file_exists($file)) unlink($file);
        
        if(!$this->countAll($this->quoteInto('group_id = ?', $page->group_id))){
            $this->getService('HtmlpageGroup')->delete($page->group_id);
        }
    }
    
    public function insert($data, $objectiveNodeId, $position)
    {
        $file = HM_Htmlpage_HtmlpageModel::getActionsPath();
        if (file_exists($file)) unlink($file);
    
        return parent::insert($data, $objectiveNodeId, $position);
    }
    
    public function update($data)
    {
        $file = HM_Htmlpage_HtmlpageModel::getActionsPath();
        if (file_exists($file)) unlink($file);
    
        return parent::update($data);
    }    

}