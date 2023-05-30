<?php
class HM_Webinar_Xml 
{	
	
	protected $_pointId;
	protected $_xml;
	protected $_filelists = array();	
	protected $_offline = false;
	protected $_intervals = array();
	
	public function __construct($pointId)
	{
		$this->_pointId = (int) $pointId;
		$this->_xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
	}
	
	public function setOffline($flag) {
		$this->_offline = $flag;
	}
	
	protected function _initOutline()
	{
		$this->_xml .= "<outline>\n";
		$items = Webinar_Files_Service::getInstance()->getFiles($this->_pointId);
		if (count($items)) {
			foreach($items as $item) {
				if ($this->_offline) {
					$item->href = basename($item->href);
				}
				$this->_xml .= "<item id=\"{$item->id}\" pointId=\"{$this->_pointId}\" parentId=\"0\" href=\"".htmlspecialchars($item->href)."\"><![CDATA[".$item->title."]]></item>\n";
			}
		}
        $this->_xml .= "</outline>\n";
	}
	
	protected function _initHistory()
	{
        $this->_xml .= "<history>\n";
        $items = Webinar_History_Service::getInstance()->getList($this->_pointId);
        if (count($items)) {
        	$isRecord = false;
        	$interval = array();
            foreach($items as $item) {
            	if ($item->action == 'record start') {
            		$isRecord = true;
            		$interval['start'] = $item->datetime;
            		$this->_filelists[] = $item->item;            		
            	}
            	if ($isRecord) {
            		if (in_array($item->action, array('record start', 'record stop'))) {
                        $this->_xml .= "<item id=\"{$item->id}\" pointId=\"{$this->_pointId}\" userId=\"{$item->userId}\" action=\"{$item->action}\" datetime=\"{$item->datetime}\"/>\n";            			
            		} else {
                        $this->_xml .= "<item id=\"{$item->id}\" pointId=\"{$this->_pointId}\" userId=\"{$item->userId}\" item=\"".htmlspecialchars($item->item)."\" action=\"{$item->action}\" datetime=\"{$item->datetime}\"/>\n";
            		}
            	}
                if ($item->action == 'record stop') {
                    $isRecord = false;
                    $interval['stop'] = $item->datetime;
                    $this->_intervals[] = $interval;
                    $interval = array();
                }
            }
        }
        $this->_xml .= "</history>\n";		
	}
	
	protected function _initChat()
	{
		$this->_xml .= "<chat>\n";
		if (count($this->_intervals)) {
			foreach($this->_intervals as $interval) {
		        $items = Webinar_Chat_Service::getInstance()->getListByInterval($this->_pointId, $interval['start'], $interval['stop']);
		        if (count($items)) {
		            foreach($items as $item) {
		                $this->_xml .= "<message id=\"{$item->id}\" pointId=\"{$this->_pointId}\" userId=\"{$item->userId}\" datetime=\"{$item->datetime}\"><![CDATA[{$item->message}]]></message>\n";
		            }
		        }
				
			}
		}
		
		$this->_xml .= "</chat>\n";
	}
	
	protected function _initBroadcast()
	{
		$this->_xml .= "<broadcast>\n";
        $items = array();
        if (is_array($this->_filelists) && count($this->_filelists)) {
            foreach($this->_filelists as $item) {
            	if (strlen($item)) {
            		$files = Webinar_Service::getInstance()->getFiles($item);
            		if (is_array($files) && count($files)) {
            			foreach($files as $file) {
            				if ($this->_offline) {
            					$file = basename($file);
            				}
                            $items[$file] = $file;
            				//$this->_xml .= "<item href=\"$file\" />";
            			}
            		}
            	}
            }
        }
        if (is_array($items) && count($items)) {
            foreach($items as $item) {
                $this->_xml .= "<item href=\"$item\" />";
            }
        }
        $this->_xml .= "</broadcast>\n";  
	}
	
	protected function _initUsers()
	{
		$this->_xml .= "<users>\n";
		$items = Webinar_Service::getInstance()->getUserList($this->_pointId);
		if (count($items)) {
			foreach($items as $item) {
				$this->_xml .= "<user id=\"".$item->MID."\" lastName=\"".htmlspecialchars($item->LastName)."\" firstName=\"".htmlspecialchars($item->FirstName)."\" middleName=\"".htmlspecialchars($item->Patronymic)."\"/>";
			}
		}
		$this->_xml .= "</users>";
	}
	
	protected function _init()
	{
		$this->_xml .= "<webinar pointId=\"{$this->_pointId}\">\n";
		$this->_initOutline();
		$this->_initHistory();
		$this->_initChat();
		$this->_initBroadcast();
		$this->_initUsers();
		$this->_xml .= "</webinar>\n";
	}
	
	public function get()
	{
		$this->_init();
	    return iconv(Zend_Registry::get('config')->common->charset, Zend_Registry::get('config')->webinar->charset, $this->_xml);
	}
	
	
}