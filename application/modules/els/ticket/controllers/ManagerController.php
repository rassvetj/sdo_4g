<?php
class Ticket_ManagerController extends HM_Controller_Action
{
    protected $_ticketService = null;
    
    public function init()
    {	
		$this->_ticketService = $this->getService('Ticket');				
		parent::init();
    }
    
    
    public function indexAction()
    {	
		$this->view->setHeader(_('Оплаты студентов'));
		
	}
	
	public function getTreeAction()
	{
		$this->_helper->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();		
		$this->view->tree = $this->prepareTree($this->getService('TicketPayment')->getPaymentsTree());		
		echo $this->view->render('manager/tree.tpl');		
		die;
	}
	
	# сброс кэша и получение актуальных данных
	public function refreshTreeAction()
	{		
		$this->_helper->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
		
		$reset_cache = true;
		$this->view->tree = $this->prepareTree($this->getService('TicketPayment')->getPaymentsTree($reset_cache));
		
		echo $this->view->render('manager/tree.tpl');		
		die;
	}
	
	private function prepareTree($raw)
	{
		$mid_external_s = array_keys($raw);
		if(empty($mid_external_s)){ return false; }
		foreach($mid_external_s as $key => $v){ $mid_external_s[$key] = (string)$v; }		
		$criteria = $this->getService('User')->quoteInto('mid_external IN (?)', $mid_external_s );
		$users 	  = $this->getService('User')->fetchAll($criteria);
		
		$data = array();
		
		
		# получаем все записи о загруженных файлах
		$res = $this->getService('FilesFtp')->fetchAll();
		$files_info = array();
		foreach($res as $i){ $files_info[$i->file_id] = $i; }
		
		
		foreach($users as $u){
			$fio = $u->LastName.' '.$u->FirstName.' '.$u->Patronymic;
			$key = $fio.'~'.mid_external;
			$data[$key] = array(
				'fio' 	   	   => $fio,
				'mid_external' => $u->mid_external,
				'user_id'	   => $u->MID,
				#'ftp_data' 	   => $raw[$u->mid_external],
			);
			
			$periods 	  = array();
			$period_empty = _('Без периода');
			# если периода нет, то будет "без периода"
			foreach($raw[$u->mid_external] as $folder_name => $i){
				$is_file = array_key_exists('last_mod', $i) ? true : false;
				
				# файл не попал в папку периода.
				if($is_file){
					$file_id = (int)pathinfo($folder_name, PATHINFO_FILENAME);
					
					$periods[$period_empty][$file_id] = array(
						'name' 			=> $files_info[$file_id]->name,
						'size' 		    => $this->getService('FilesFtp')->formatSizeUnits($files_info[$file_id]->file_size),
						'date_uploaded' => $files_info[$file_id]->date_uploaded,
					);
					continue;
				}
				
				
				
				foreach($i as $file_name => $j){
					$file_id = (int)pathinfo($file_name, PATHINFO_FILENAME);
					
					$periods[$folder_name][$file_id] = array(
						'name' 			=> $files_info[$file_id]->name,
						'size' 		    => $this->getService('FilesFtp')->formatSizeUnits($files_info[$file_id]->file_size),
						'date_uploaded' => $files_info[$file_id]->date_uploaded,
					);
				}
			}
			
			$data[$key]['periods'] = $periods;
			
		}
		ksort($data);
		
		return $data;
	}

}