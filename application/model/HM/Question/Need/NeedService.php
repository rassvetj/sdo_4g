<?php
class HM_Question_Need_NeedService extends HM_Service_Abstract
{
    
    
    
    
    public function necessary($kodId, $testId){

        $val = $this->fetchAll(array('kod = ?' => $kodId, 'tid = ?' => $testId));
        
        if(count($val) === 0){
            $this->insert(array('kod' => $kodId, 'tid' => $testId));
            return true;
        }
        return false;
        
    }
      
    public function unnecessary($kodId, $testId){

        $val = $this->fetchAll(array('kod = ?' => $kodId, 'tid = ?' => $testId));

        if(count($val) > 0){
            
            $where1 = $this->getMapper()->getTable()->getAdapter()->quoteInto('kod = ?', $kodId);
            $where2 = $this->getMapper()->getTable()->getAdapter()->quoteInto('tid = ?' , $testId);
            $this->deleteBy(array($where1, $where2));
            
          return true;
        }
        return false;
        
    }
    

}