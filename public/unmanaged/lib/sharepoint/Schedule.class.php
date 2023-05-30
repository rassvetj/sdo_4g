<?php

class SharePointSchedule {
    
    protected $attributes = array();
    
    function __construct($attributes) {
        $this->attributes = $attributes;    
    }
    
    protected function _getRecurrence() {
        $item = false;
        if ($this->attributes['rebild']) {
            $item['fRecurrence'] = 'True';
            $item['EventType']   = '0';
            
            $item['RecurrenceData'] = '<recurrence><rule>firstDayOfWeek>mo</firstDayOfWeek>';
            switch($this->attributes['rebild']) {                        
                case 1:
                    $item['RecurrenceData'] .= '<repeat><daily dayFrequency="1" /></repeat>';
                    //$item['RecurrenceData'] .= '<windowEnd>'.htmlspecialchars($this->attributes['end']).'</windowEnd>';
                    break;
                case 2:
                    $item['RecurrenceData'] .= '<repeat><weekly '.date('S', strtotime($this->attributes['begin'])).'="True" weekFrequency="1" /></repeat>';
                    $item['RecurrenceData'] .= '<windowEnd>'.htmlspecialchars($this->attributes['end']).'</windowEnd>';
                    break;
                case 3:
                    $item['RecurrenceData'] .= '<repeat><weekly '.date('S', strtotime($this->attributes['begin'])).'="True" weekFrequency="2" /></repeat>';
                    $item['RecurrenceData'] .= '<windowEnd>'.htmlspecialchars($this->attributes['end']).'</windowEnd>';
                    break;
                case 4:
                    $item['RecurrenceData'] .= '<repeat><monthly day="'.date('d', strtotime($this->attributes['begin'])).'" monthFrequency="1" /></repeat>';
                    $item['RecurrenceData'] .= '<windowEnd>'.htmlspecialchars($this->attributes['end']).'</windowEnd>';
                    break;
            }
            $item['RecurrenceData'] .= '</rule></recurrence>';
        }        
        
        return $item;
    }
    
    function create($scheduleId) {
        
        if (!defined('SHAREPOINT_ENABLE') || !SHAREPOINT_ENABLE) return false;
        
        if ($scheduleId && is_array($this->attributes) && count($this->attributes)) {
            if ($lists = new SharePointLists()) {
                // prepare attributes
                $url = $GLOBALS['sitepath'].'schedule.php4?c=go&mode_frames=1&sheid='.$scheduleId;
                
                $item = array();
                $item['Title']       = htmlspecialchars($this->attributes['title']);
                $item['Description'] = htmlspecialchars($this->attributes['description']);
                $item['EventDate']   = htmlspecialchars($this->attributes['begin']);
                $item['EndDate']     = htmlspecialchars($this->attributes['end']);                
                $item['WebPage']     = $url; 

                if ($recurrency = $this->_getRecurrence()) {
                    foreach($recurrency as $key => $value) {
                        $item[$key] = $value;
                    }
                }
                
                // create
                $res = $lists->addListItems(SHAREPOINT_LIST_CALENDAR_ID, array($item));
                
print htmlspecialchars($lists->__getLastRequest()) ."\n"; 
print htmlspecialchars($lists->__getLastResponse())."\n"; 
                
                if (isset($res[0]['ows_ID'])) {
                    if ($res[0]['ows_ID']) {
                        sql("UPDATE schedule 
                             SET 
                                sharepointId = '".$res[0]['ows_ID']."' 
                             WHERE 
                                SHEID = '".(int) $scheduleId."'");
                    }
                    return $res[0]['ows_ID']; 
                }
            }
        }
        return false;
    }
    
    function update($scheduleId) {
        
        if (!defined('SHAREPOINT_ENABLE') || !SHAREPOINT_ENABLE) return false;
        
        $sharepointId = (int) getField('schedule', 'sharepointId', 'SHEID', (int) $scheduleId);
        
        if ($sharepointId) {
            if ($lists = new SharePointLists()) {
                // prepare attributes
                
                $url = $GLOBALS['sitepath'].'schedule.php4?c=go&amp;mode_frames=1&amp;sheid='.$scheduleId;
                
                $item = array();
                $item['Title']       = htmlspecialchars($this->attributes['title']);
                $item['Description'] = htmlspecialchars($this->attributes['description']);
                $item['EventDate']   = htmlspecialchars($this->attributes['begin']);
                $item['EndDate']     = htmlspecialchars($this->attributes['end']);
                $item['ID']          = $sharepointId;
                $item['WebPage']     = $url;

                if ($recurrency = $this->_getRecurrence()) {
                    foreach($recurrency as $key => $value) {
                        $item[$key] = $value;
                    }
                }
                
                // update
                $res = $lists->updateListItems(SHAREPOINT_LIST_CALENDAR_ID, array($item));
                
                if (isset($res[0]['ows_ID'])) {
                    return $res[0]['ows_ID'];
                }
                
            }
        }
        
        return false;
    }
    
    function delete($scheduleId) {
                
        if (!defined('SHAREPOINT_ENABLE') || !SHAREPOINT_ENABLE) return false;
        
        $sharepointId = (int) getField('schedule', 'sharepointId', 'SHEID', (int) $scheduleId);
        if ($sharepointId) {
            if ($lists = new SharePointLists()) {
                return $lists->deleteListItems(SHAREPOINT_LIST_CALENDAR_ID, array(array('ID' => $sharepointId)));
            }
        }
        
        return false;
    }
    
}

?>