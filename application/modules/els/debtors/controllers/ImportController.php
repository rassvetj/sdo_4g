<?php
class Debtors_ImportController extends HM_Controller_Action
{
    private $_importService = null;
    protected $_importManagerClass = 'HM_Debtors_Import_Manager';

    public function indexAction()
    {
        $source = $this->_getParam('source', false);

        if (!$source || !method_exists($this, $source)) {
            throw new HM_Exception(sprintf(_('Источник %s не найден.'), $source));
        }

        call_user_func(array($this, $source));
        
        $this->view->form = false;
        if ($this->_importService->needToUploadFile()) {
            $this->_valid = false;
            $form = $this->_importService->getForm();
            if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
                if ($form->file->isUploaded()) {
                    $form->file->receive();
                    if ($form->file->isReceived()) {
                        $this->_importService->setFileName($form->file->getFileName());
                        $this->_valid = true;						
                    }
                }
            } else {
                $this->view->form = $form;
            }
        }

        try {
            $class = $this->_importManagerClass;
            $this->_importManager = $importManager = new $class();
            if ($this->_valid) {				
                $importManager->init($this->_importService->fetchAll());
            }
        } catch(HM_Exception $e) {
            $this->_flashMessenger->addMessage(array('message' => $e->getMessage(), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
            $this->_redirectToIndex();
        }
        $this->view->importManager = $importManager;
        $this->view->source = $source;

    }
    
    public function csv()
    {
        $this->getService('Unmanaged')->setHeader(_('Импортировать должников из CSV (продление сессий)'));
        $this->_importService = $this->getService('DebtorsCsv');		
    }

    public function processAction()
    {
        //$this->_helper->viewRenderer->setNoRender(true);
		
		$source = $this->_getParam('source', false);

        if (!$source || !method_exists($this, $source)) {
            throw new HM_Exception(sprintf(_('Источник %s не найден.'), $source));
        }

        call_user_func(array($this, $source));

        $importManager = new HM_Debtors_Import_Manager();
        
		if ($importManager->restoreFromCache()) {			
            $importManager->init(array());
        } else {			
            $importManager->init($this->_importService->fetchAll());
        }

        if (!$importManager->getCount()) {
            $this->_flashMessenger->addMessage(_('Новые записи не найдены'));
        }
		
        $importManager->import();
		
        if ($importManager->getUpdatesCount() ) {
            $this->_flashMessenger->addMessage(sprintf(_('Были продлены %d записи(ей)'), $importManager->getUpdatesCount()));
        }
		
		if ($importManager->getGraduatedDebtorsCount() ) {
            $this->_flashMessenger->addMessage(sprintf(_('Были переведены из прошедших и продлены %d записи(ей)'), $importManager->getGraduatedDebtorsCount()));
        }
		
		if ($importManager->getGraduatedDebtorsEndedCount() ) {
            $this->_flashMessenger->addMessage(sprintf(_('Был обновлен итоговый балл в прошедших обучение: %d записи(ей)'), $importManager->getGraduatedDebtorsEndedCount()));
        }
		
        
        $this->_redirector->gotoSimple('index', 'import', 'debtors', array('source' => 'csv'));
		
    }
	
	
	/**
	 * данные их кэша выгружаем в файл
	*/
	public function tocsvAction(){
		
		$this->_helper->getHelper('layout')->disableLayout();        
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();		
        $this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
		
		$report_type = $this->_getParam('type', false);
		
		
		//добавялем BOM
        echo "\xEF\xBB\xBF";
		
		if(!$report_type){
			echo 'Неверные данные.';
			die;
		}
		
		$importManager = new HM_Debtors_Import_Manager();
		$importManager->restoreFromCache();
		
		# Следующие записи будут переведены из "прошедших обучение" в активное и продлены
		if($report_type == 'grad'){ 
			$filename 	 = 'Переведены из "прошедших обучение" в активное и продлены от '.date('d.m.Y H:i');
			$description = 'Переведены из "прошедших обучение" в активное и продлены от '.date('d.m.Y H:i');
			$title = array(				
				_('ID сессии'),
				_('Сессия'),
				_('Фамилия студента'),
				_('Продлить до'),
				_('Семестр'),				
			);
			$outputData = array();
			foreach($importManager->getGraduatedDebtors() as $graduate) {
				$outputData[] = array(
					$graduate['subject']->external_id,
					$graduate['subject']->name,
					$graduate['destination']->fio,
					date('d.m.Y', strtotime($graduate['destination']->time_ended_debtor)),
					$graduate['destination']->semester,					
				);
			}	
		# Кофликтные ситуации
		} elseif($report_type == 'conflict'){ 		
			$filename 	 = 'Кофликтные ситуации от '.date('d.m.Y H:i');
			$description = 'Кофликтные ситуации от '.date('d.m.Y H:i');
			$title = array(									
				_('ID сессии'),
				_('Сессия'),
				_('ID студента'),
				_('ФИО'),				
				_('Группа'),				
				_('ID преподавателя'),				
				_('ФИО преподавателя'),				
				_('Текущий балл в СДО'),				
				_('Дата продления'),				
				_('Семестр'),				
			);
			$outputData = array();			
			foreach($importManager->getConflicts() as $conflict) {
				$outputData[] = array(
					$conflict['destination']->session_external_id,
					$conflict['source']['session_name'],
					$conflict['destination']->mid_external,
					$conflict['source']['fio'],
					$conflict['source']['groups'],
					$conflict['source']['teacher_mid_external'],
					$conflict['source']['teacher_fio'],     
					$conflict['source']['mark'],				
					date('d.m.Y', strtotime($conflict['destination']->time_ended_debtor)), 
					$conflict['destination']->semester,
				);
			}
		# Данные записи НЕ будут обновлены
		} elseif($report_type == 'notfound'){
		
			$filename 	 = 'Данные записи НЕ будут обновлены от '.date('d.m.Y H:i');
			$description = 'Данные записи НЕ будут обновлены от '.date('d.m.Y H:i');
			$title = array(									
				_('Причина'),
				_('ID студента'),
				_('ФИО'),
				_('Группы студента'),				
				_('ID сессии'),	
				_('Дисциплина'),					
				_('Специальность'),				
				_('Дата начала'),					
				_('Дата окончания'),				
				_('Продлить до'),				
				_('Продлить до 2'),				
				_('Возможные сессии'),				
				_('Группа'),				
				_('Семестр'),				
			);
			$outputData = array();	
			
			foreach($importManager->getNotFound() as $nf) {
				
				$reason = '';
				$reason .= ($nf->notFoundUser)?('нет пользователя,'):('');
				$reason .= ($nf->notFoundSubject)?('нет сессии,'):('');
				$reason .= ($nf->isGraduated)?('сдано,'):('');
				$reason .= ($nf->isNotAssigned)?('не назначен,'):('');
				$reason = trim($reason, ',');
				
				$dateBegin = (!empty($nf->dateBegin))?(date('d.m.Y', strtotime($nf->dateBegin))):('');
				$dateEnd = (!empty($nf->dateEnd))?(date('d.m.Y', strtotime($nf->dateEnd))):('');
				$time_ended_debtor = (!empty($nf->time_ended_debtor))?(date('d.m.Y', strtotime($nf->time_ended_debtor))):('');
				$time_ended_debtor_2 = (!empty($nf->time_ended_debtor_2))?(date('d.m.Y', strtotime($nf->time_ended_debtor_2))):('');
				
				$groups = (count($nf->groups))?(implode(', ',$nf->groups)):('');
				
				if($nf->sessionFounded){
					
					$count = 1;
					
					
						
					foreach($nf->sessionFounded as $i){
						
						$subjectLink  = (empty($i['session_id_external']))?('нет'):($i['session_id_external']);
						$subjectLink .= ' - ( '.$_SERVER['SERVER_NAME'].$this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card' , 'subject_id' =>$i['CID']), null, true).' ) '.$i['session_name'];
						$subjectLink .= (!empty($i['session_ended']))?(' (до '.date('d.m.Y', strtotime($i['session_ended'])).')'):('');
						if($count == 1){						
							$outputData[] = array(
								$reason,
								$nf->mid_external,
								$nf->fio,
								$groups,
								$nf->session_external_id,
								$nf->name,
								$nf->specialty,
								$dateBegin,
								$dateEnd,
								$time_ended_debtor,
								$time_ended_debtor_2,
								$subjectLink,
								$i['groups'],
								$nf->semester,									
							);
						} else {
							$outputData[] = array(
								'',
								$nf->mid_external,
								'',
								'',
								$nf->session_external_id,
								'',
								'',
								'',
								'',
								'',
								$subjectLink,
								$i['groups'],
								$nf->semester,
							);					
						}	
						$count++;
					}
					
				} else {										
					$outputData[] = array(
						$reason,
						$nf->mid_external,
						$nf->fio,
						$groups,
						$nf->session_external_id,
						$nf->name,
						$nf->specialty,
						$dateBegin,
						$dateEnd,
						$time_ended_debtor,
						$time_ended_debtor_2,
						'',
						'',
						$nf->semester,

					);					
				}
			}			
		# Будут обновлены следующие записи
		} elseif($report_type == 'updated'){
		
			$filename 	 = 'Будут обновлены следующие записи от '.date('d.m.Y H:i');
			$description = 'Будут обновлены следующие записи от '.date('d.m.Y H:i');
			$title = array(								
				_('Сессия'),
				_('ФИО'),
				_('Уже продлена до'),				
				_('Продлить до'),
				_('Уже продлена до 2'),
				_('Продлить до 2'),
				_('Семестр'),							
			);
			$outputData = array();	
			
			foreach($importManager->getUpdates() as $update) {
				$time_ended_debtor_old 	 = (!empty($update['source']['time_ended_debtor']))?(date('d.m.Y', strtotime($update['source']['time_ended_debtor']))):('');
				$time_ended_debtor_old_2 = (!empty($update['source']['time_ended_debtor_2']))?(date('d.m.Y', strtotime($update['source']['time_ended_debtor_2']))):('');
				
				$time_ended_debtor_new 	 = (!empty($update['destination']->time_ended_debtor))?(date('d.m.Y', strtotime($update['destination']->time_ended_debtor))):('');
				$time_ended_debtor_new_2 = (!empty($update['destination']->time_ended_debtor_2))?(date('d.m.Y', strtotime($update['destination']->time_ended_debtor_2))):('');
				$outputData[] = array(
					$update['source']['session_name'],
					$update['source']['fio'],
					$time_ended_debtor_old,					
					$time_ended_debtor_new,
					$time_ended_debtor_old_2,
					$time_ended_debtor_new_2,
					$update['destination']->semester,
				);
			}
		} elseif($report_type == 'gradend'){
			$filename 	 = 'Будет обновлен итоговый балл в прошедших обучение без продления от '.date('d.m.Y H:i');
			$description = 'Будет обновлен итоговый балл в прошедших обучение без продления от '.date('d.m.Y H:i');
			$title = array(			
				_('ID сессии'),
				_('Сессия'),
				_('ФИО'),
				_('Старый итоговый балл'),
				_('Новый итоговый балл'),
				_('Семестр'),							
			);
			$outputData = array();	
			
			foreach($importManager->getGraduatedDebtorsEnded() as $g) {				
				$outputData[] = array(
					$g['subject']->external_id,
					$g['subject']->name,
					$g['destination']->fio,
					$g['marks']['old'],
					$g['marks']['new'],
					$g['destination']->semester,
				);
			}
		} elseif($report_type == 'manyball'){
			$filename 	 = 'Уже продлены и баллов больше 65 от '.date('d.m.Y H:i');
			$description = 'Уже продлены и баллов больше 65 от '.date('d.m.Y H:i');
			$title = array(			
				_('ID сессии'),
				_('Сессия'),
				_('ID студента'),
				_('ФИО'),
				_('ID преподавателя'),
				_('ФИО преподавателя'),
				_('Текущий балл в СДО'),
				_('Продлена до'),
				_('Семестр'),
				_('Группа'),
				_('Классификатор'),		
			);
			$outputData = array();
			foreach($importManager->getAlredyExtended() as $extended){
				if($extended['source']['mark'] < 65){ continue; }
				$time_ended_debtor = ($extended['source']['time_ended_debtor']) ? (date('d.m.Y', strtotime($extended['source']['time_ended_debtor'])) ) : ('');
				$outputData[] = array(				
					$extended['destination']->session_external_id,
					$extended['source']['session_name'],
					$extended['destination']->mid_external,
					$extended['source']['fio'],                           
					$extended['source']['teacher_mid_external'],
					$extended['source']['teacher_fio'],     
					$extended['source']['mark'],				
					$time_ended_debtor,
					$extended['destination']->semester,
					$extended['source']['groups'],
					$extended['source']['classifiers'],
				);
			}
		} else {
			echo 'Неверные данные.';
			die;			
		}
		
		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'.csv');
		echo $description;
		echo "\r\n";
		echo implode(';', $title);
        echo "\r\n";
		foreach($outputData as $row){            
			echo implode(';', $row);
            echo "\r\n";
        }	
		
	}
	
	
	public function getExampleFileAction(){
		$this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();		
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
        $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename=debtors.csv');
		echo "\xEF\xBB\xBF";
		echo implode(';', HM_Debtors_Csv_CsvAdapter::getColumnNameList());
		echo "\r\n";
		echo "123;Иванов Иван Иванович;111111111;Производственная практика;Экономика (бакалавр);09.02.2015;10.04.2015;4;01.07.2016;09.08.2016;02.09.2016;09.10.2017;78,77;77;\r\n";
		echo "123;Петрова Пепа Петровна;222222222;Производственная практика;Экономика (бакалавр);09.02.2015;10.04.2015;4;01.07.2016;09.08.2016;02.09.2016;09.10.2017;77;78;\r\n";
	}
	

	
	
}