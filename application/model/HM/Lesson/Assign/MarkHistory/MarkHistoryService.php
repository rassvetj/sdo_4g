<?php
class HM_Lesson_Assign_MarkHistory_MarkHistoryService extends HM_Service_Abstract
{
    private $_markHistoryCache = false; 
    
    // не фиксируем дублирующиеся оценки
    public function insert($data)
    {
        $collection = $this->fetchAll(array(
            'MID = ?' => $data['MID'],
            'SSID = ?' => $data['SSID'],
        ), 'updated DESC');

        if (count($collection)) {
            $mark = $collection->current();
            if ($mark->mark == $data['mark']) return true;
        }
        return parent::insert($data);
    }
    
    public function hasMarkHistory($subjectId, $lessonId, $userId)
    {
        if ($this->_markHistoryCache === false) {
            $this->_markHistoryCache = array();
            
// как-то очень неоптимально через сервисный слой..            
//             $collection = $this->getService('Lesson')->fetchAllDependenceJoinInner('LessonAssign', $this->quoteInto(array(
//                 'self.CID = ? AND ',        
//                 'LessonAssign.MID = ?',        
//             ), array(
//                 $subjectId,        
//                 $userId,        
//             )));
//             if (count($collection)) {
//                 $ssids = $collection->getList('SSID');
//                 $collection = $this->fetchAll(array('SSID IN (?)' => $ssids));
//             }

            $select = $this->getService('Lesson')->getSelect()
                ->from(array('s' => 'schedule'), array('SHEID'))
                ->join(array('si' => 'scheduleID'), 's.SHEID = si.SHEID', array())
                ->join(array('smh' => 'schedule_marks_history'), 'si.SSID = smh.SSID', array(new Zend_Db_Expr('COUNT(mark)')))
                ->where('s.CID = ?', $subjectId)
                ->where('si.MID = ?', $userId)
                ->group(array('s.SHEID'))
                ->having(new Zend_Db_Expr('COUNT(mark) > 1'));
            
            if ($rowset = $select->query()->fetchAll()) {
        	    foreach ($rowset as $row) {
        	        $this->_markHistoryCache[$row['SHEID']] = true;   
        	    }   
            }         
        }
        return isset($this->_markHistoryCache[$lessonId]);
    }
}