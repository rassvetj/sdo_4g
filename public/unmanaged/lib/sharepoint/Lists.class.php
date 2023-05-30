<?php

class SharePointLists {
    
    protected $lists;
    protected $collection = array();
    
    function __construct() {
        stream_wrapper_unregister('http');
        stream_wrapper_register('http', 'SharePointNTLMStream') or die("Failed to register protocol");
        
        $this->lists = new SharePointNTLMSoapClient(SHAREPOINT_HOST.SHAREPOINT_LISTS_WSDL, 
            array(
                'trace' => SHAREPOINT_SOAP_TRACE,
                'exceptions' => SHAREPOINT_SOAP_EXCEPTIONS,
                'soap_version' => SHAREPOINT_SOAP_VERSION
            )
        );                
    }
    
    function __getFunctions() {
        return $this->lists->__getFunctions();
    }
    
    function __getLastRequest() {
        return $this->lists->__getLastRequest();
    }
    
    function __getLastResponse() {
        return $this->lists->__getLastResponse();
    }
    
    function __destruct() {
        stream_wrapper_restore('http');
    }
    
    function getListCollection() {
        $collection = array();
        if ($this->lists) {
            try {
                $res = $this->lists->GetListCollection();
                if (isset($res->GetListCollectionResult->any) && strlen($res->GetListCollectionResult->any)) {
                    if ($xml = simplexml_load_string(SharePointXml::removeNS($res->GetListCollectionResult->any))) {
                        foreach($xml->children() as $list) {
                            $item = array();
                            foreach($list->attributes() as $name => $value) {
                                $item[$name] = SharePointXml::convertStringFrom($value);
                            }
                            if (is_array($item) && count($item)) {
                                $collection[$item['ID']] = $item;
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                throw $e;
            }
            
        }
        
        return $collection;
    }
    
    function getListAndView($listName) {
        $return = array();
        if ($this->lists) {
            try {
                $params = new stdClass();
                $params->listName = $listName;
                
                $res = $this->lists->GetListAndView($params);
                if (isset($res->GetListAndViewResult->any) && strlen($res->GetListAndViewResult->any)) {
                    if ($xml = simplexml_load_string(SharePointXml::removeNS($res->GetListAndViewResult->any), "SimpleXMLElement", LIBXML_NOCDATA)) {
                        foreach($xml->List->attributes() as $name => $value) {
                            $return['list'][$name] = SharePointXml::convertStringFrom($value);
                        }
                        
                        foreach($xml->View->attributes() as $name => $value) {
                            $return['view'][$name] = SharePointXml::convertStringFrom($value);
                        }
                    }
                }
                
            } catch (Exception $e) {
                throw $e;
            }
        }
        return $return;
    }
    
    function getListViewName($listName) {
        $params = $this->getListAndView($listName);
        if (isset($params['view']['Name'])) {
            return $params['view']['Name'];
        }        
    }
    
    function getListItems($listName) {
        $items = array();
        
        if ($this->lists) {
            try {
                $params = new stdClass();
                $params->listName = $listName;
                
                $res = $this->lists->GetListItems($params);
                if (isset($res->GetListItemsResult->any) && strlen($res->GetListItemsResult->any)) {
                    if ($xml = simplexml_load_string(SharePointXml::removeNS($res->GetListItemsResult->any))) {
                        foreach($xml->data->children() as $i) {                            
                            $item = array();
                            foreach($i->attributes() as $name => $value) {
                                $item[$name] = SharePointXml::convertStringFrom($value);
                            }
                            if (is_array($item) && count($item)) {
                                $items[$item['ows_ID']] = $item;
                            }
                        }
                    }
                }
                
            } catch (Exception $e) {
                throw $e;
            }
        }
        
        return $items;
    }
    
    private function _updateListItems($listName, $items, $action) {
        if ($this->lists && is_array($items) && count($items)) {
            try {
                $params = new stdClass();
                $params->listName = SharePointXml::convertStringTo($listName);
                
                $message = '<Batch OnError="Continue" PreCalc="TRUE" ListVersion="0" ViewName="'.$this->getListViewName($listName).'">';
                
                $counter = 1;
                foreach($items as $item) {
                    $message .= '<Method ID="'.$counter.'" Cmd="'.$action.'">';
                    if (strtolower($action) == 'new') $message .= '<Field Name="ID">New</Field>';
                    foreach($item as $key => $value) {
                        if ($key == 'RecurrenceData') {
                            $message .= '<Field Name="'.htmlspecialchars($key).'">'.$value.'</Field>';                            
                        } else {
                            $message .= '<Field Name="'.htmlspecialchars($key).'">'.htmlspecialchars($value).'</Field>';
                        }
                    }
                    $message .= '</Method>';
                    $counter ++;
                }
                
                $message .= '</Batch>';
                
                $params->updates->any = SharePointXml::convertStringTo($message);
                                
                $res = $this->lists->UpdateListItems($params);

                if (isset($res->UpdateListItemsResult->any) && strlen($res->UpdateListItemsResult->any)) {
                    if ($xml = simplexml_load_string(SharePointXml::removeNS($res->UpdateListItemsResult->any))) {
                        $ret = array();
                        
                        foreach($xml->Result as $result) {
                            $item = array();
                            
                            $item['ErrorCode'] = SharePointXml::convertStringFrom($result->ErrorCode);
                            foreach($result->row->attributes() as $name => $value) {
                                $item[$name] = SharePointXml::convertStringFrom($value);
                            }
                            
                            $ret[] = $item;
                        }
                        
                        return $ret;
                    }
                }
                
            } catch (Exception $e) {
                throw $e;
            }
        }
        
        return false;
    }
    
    /**
     * create item in the list
     * ID attribute isn't required
     *
     * @param string $listName
     * @param array $items
     * @return array
     */
    function addListItems($listName, $items) {
        return $this->_updateListItems($listName, $items, 'New');
    }

    /**
     * update items in the list
     * ID attribute is required
     *
     * @param string $listName
     * @param array $items
     * @return array
     */
    function updateListItems($listName, $items) {
        return $this->_updateListItems($listName, $items, 'Update');
    }

    /**
     * delete items from the list
     * ID attribute is required
     *
     * @param string $listName
     * @param array $items
     * @return array
     */
    function deleteListItems($listName, $items) {
        return $this->_updateListItems($listName, $items, 'Delete');
    }
    
}

?>