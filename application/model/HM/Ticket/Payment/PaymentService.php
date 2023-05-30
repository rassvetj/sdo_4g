<?php
class HM_Ticket_Payment_PaymentService extends HM_Service_Abstract
{
	const CACHE_NAME = 'HM_Ticket_Payment_PaymentService';
	
	private $_tree = null;
	
	public function getUserPayments($mid_external){
		$collection = $this->fetchAll($this->quoteInto('mid_external = ?', $mid_external));	
		if(!$collection){ return array(); }
		
		$data = array();
		foreach($collection as $i){
			$data[$i->year][] = array(
				'sum' 			=> $i->sum,
				'date_payment' 	=> $i->date_payment,
				'file_id' 		=> $i->file_id,				
			);
		}
		return $data;
	}
	
	
	/**
	 * выводит список всех файлов студентов с ftp
     * GUID -> years -> files 	 
	 */
	public function getPaymentsTree($reset_cache = false)
	{
		if($reset_cache){ $this->clearCache(); }		
		$this->restoreFromCache();		
		if(!empty($this->_tree)){ return $this->_tree; }		 
		
		$service_ftp = $this->getService('FilesFtp');		
		$host 	  = HM_Ticket_Payment_PaymentModel::FTP__HOST;
		$login 	  = HM_Ticket_Payment_PaymentModel::FTP__LOGIN;
		$password = HM_Ticket_Payment_PaymentModel::FTP__PWD;
		$folder   = HM_Ticket_Payment_PaymentModel::FTP__DIR;
		
		if(!$service_ftp->setConnected($host, $login, $password)) {
			$this->_helper->getHelper('FlashMessenger')->addMessage(
				array('type' => HM_Notification_NotificationModel::TYPE_ERROR,
					  'message' => _('Не удалось подключиться к хранилищу файлов'))
			);
			$this->_redirect('/');
		}
		$this->_tree = $service_ftp->getList($folder);
		$this->saveToCache();
		
		return $this->_tree;
	}
	
	
	public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(                
				 '_tree' 	=> $this->_tree,				 
            ),
            self::CACHE_NAME
        );
    }

    public function clearCache()
    {
        return Zend_Registry::get('cache')->remove(self::CACHE_NAME);
    }

    public function restoreFromCache()
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {            
			$this->_tree  	= $actions['_tree'];            
            return true;
        }
        return false;
    }
	
	
		
}