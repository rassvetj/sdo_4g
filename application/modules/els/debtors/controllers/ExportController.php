<?php
class Debtors_ExportController extends HM_Controller_Action
{
	
	public function csvAction(){		
		
		$this->_helper->getHelper('layout')->disableLayout();        
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();		
        
		$report_type = $this->_getParam('type', false);
		
		switch ($report_type) {
			case 'graduated':
				echo $this->getGraduate();
				break;
			case 'conflict':
				echo $this->getConflict();
				break;
			case 'update-assign-tutor':
				echo $this->getUpdateAssignTutor();
				break;
			case 'not-found':
				echo $this->getNotFound();
				break;
			case 'incorrect':
				echo $this->getIncorrect();
				break;	
			case 'not-assign':
				echo $this->getNotAssign();
				break;
			case 'not-changed':
				echo $this->getNotChanged();
				break;	
			case 'graduate-passed':
				echo $this->getGraduatePassed();
				break;	
			case 'updated':
				echo $this->getUpdated();
				break;	
			case 'assign-tutor-subject':
				echo $this->getAssignTutorSubject();
				break;	
			case 'assign-tutor-group':
				echo $this->getAssignTutorGroup();
				break;	
			case 'tutor-not-found':
				echo $this->getTutorNotFound();
				break;	
			default:
				die('Неверные данные.');
		}
	}
	
	
	# Не найдены тьюторы в БД
	public function getTutorNotFound(){
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
		//добавялем BOM
        $data = "\xEF\xBB\xBF";
		$outputData 	= array();			
		$reasonList 	= HM_Debtors_DebtorsModel::getReasonList();			
		$importManager 	= new HM_Debtors_Import_Manager();
		$importManager->restoreFromCache();
		$filename 	 = 'Не найдены тьюторы от '.date('d.m.Y H:i');
		$description = 'Не найдены тьюторы от '.date('d.m.Y H:i');
		$title = array(						
			_('ID тьютора'),			
		);
		foreach($importManager->getTutorNotFound() as $i) {
			$outputData[] = array(
				$i['tutor_mid_external'],				
			);
		}
		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'.csv');
		$data .= $description;
		$data .= "\r\n";
		$data .= implode(';', $title);
		$data .= "\r\n";
		foreach($outputData as $row){            
			$data .= implode(';', $row);
			$data .= "\r\n";
		}
		return $data;	
		
	}
	
	
	
	# назначает тьютора на группу в сессии.
	public function getAssignTutorGroup(){
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
		//добавялем BOM
        $data = "\xEF\xBB\xBF";
		$outputData 	= array();			
		$reasonList 	= HM_Debtors_DebtorsModel::getReasonList();			
		$importManager 	= new HM_Debtors_Import_Manager();
		$importManager->restoreFromCache();
		$filename 	 = 'Тьюторы, назначенные на группу в сессии от '.date('d.m.Y H:i');
		$description = 'Тьюторы, назначенные на группу в сессии от '.date('d.m.Y H:i');
		$title = array(			
			_('Тьютор'),
			_('ID сессии'),
			_('Сессия'),
			_('Группа'),
			_('Из-за студента'),
		);
		foreach($importManager->getTutorsAssignGroups() as $i) {
			$outputData[] = array(
				$i['tutor_fio'],
				$i['subject_external_id'],
				$i['subject_name'],
				$i['group_name'],
				$i['student_fio'],
			);
		}
		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'.csv');
		$data .= $description;
		$data .= "\r\n";
		$data .= implode(';', $title);
		$data .= "\r\n";
		foreach($outputData as $row){            
			$data .= implode(';', $row);
			$data .= "\r\n";
		}
		return $data;		
	}
	
	# Получаем тьюторов, которые будут назначены на сессию.
	# !!!!!! Пологаем, что при назнаении тьютора на сессию у него нет назначений на конкретного студента и группу, поэтому при назначении их не проверяем. При откреплении эти данные должны удаляться из БД!
	public function getAssignTutorSubject(){
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
		//добавялем BOM
        $data = "\xEF\xBB\xBF";
		$outputData 	= array();			
		$reasonList 	= HM_Debtors_DebtorsModel::getReasonList();			
		$importManager 	= new HM_Debtors_Import_Manager();
		$importManager->restoreFromCache();
		$filename 	 = 'Тьюторы, назначенные на сессию от '.date('d.m.Y H:i');
		$description = 'Тьюторы, назначенные на сессию от '.date('d.m.Y H:i');
		$title = array(			
			_('Тьютор'),
			_('ID сессии'),				
			_('Сессия'),
			_('Дата продления'),
			_('Дата продления 2'),			
		);
		foreach($importManager->getTutorsAssignSubjects() as $i) {
			$date_debt 		= ($i['date_debt']) 	? (date('d.m.Y', strtotime($i['date_debt'])) ) 	: ('');
			$date_debt_2 	= ($i['date_debt_2'])	? (date('d.m.Y', strtotime($i['date_debt_2'])) )	: ('');
			
			$outputData[] = array(
				$i['tutor_fio'],
				$i['subject_external_id'],
				$i['subject_name'],
				$date_debt,
				$date_debt_2,			
			);
		}
		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'.csv');
		$data .= $description;
		$data .= "\r\n";
		$data .= implode(';', $title);
		$data .= "\r\n";
		foreach($outputData as $row){            
			$data .= implode(';', $row);
			$data .= "\r\n";
		}
		return $data;
		
	}
	
	
	
	
	# Стандартное продление со сбросом попыток.
	public function getUpdated(){
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
		//добавялем BOM
        $data = "\xEF\xBB\xBF";
		$outputData 	= array();			
		$reasonList 	= HM_Debtors_DebtorsModel::getReasonList();			
		$importManager 	= new HM_Debtors_Import_Manager();
		$importManager->restoreFromCache();
		$filename 	 = 'Стандартное продление от '.date('d.m.Y H:i');
		$description = 'Стандартное продление от '.date('d.m.Y H:i');
		$title = array(
			_('ID сессии'),
			_('Сессия'),
			_('ID студента'),
			_('ФИО'),
			_('Уже продлена до'),				
			_('Продлить до'),
			_('Уже продлена до 2'),
			_('Продлить до 2'),
			_('Семестр'),
			_('Контроль'),
		);
		foreach($importManager->getUpdateData() as $i) {
			
			$old_time_ended_debtor 		= ($i->old_time_ended_debtor) 	? date('d.m.Y', strtotime($i->old_time_ended_debtor)) 	: 'Не продлена';
			$time_ended_debtor 			= ($i->time_ended_debtor) 		? date('d.m.Y', strtotime($i->time_ended_debtor)) 		: 'Нет';
			
			$old_time_ended_debtor_2 	= ($i->old_time_ended_debtor_2)	? date('d.m.Y', strtotime($i->old_time_ended_debtor_2))	: 'Не продлена';
			$time_ended_debtor_2 		= ($i->time_ended_debtor_2) 	? date('d.m.Y', strtotime($i->time_ended_debtor_2)) 	: 'Нет';
			
			$reason_fail  = '';
			foreach($i->reasonFail as $val){
				$reason_fail .= ' '.$val['message'];
					if(!empty($val['lessons'])){
						$reason_fail .= ' в занятииях #'.implode(', ', $val['lessons']);
					}		
				$reason_fail .= ',';
			}				
			
			$outputData[] = array(
				$i->session_external_id,
				$i->name,
				$i->mid_external,
				$i->fio,
				$old_time_ended_debtor,
				$time_ended_debtor,
				$old_time_ended_debtor_2,
				$time_ended_debtor_2,
				$i->semester,
				$i->exam_type_name,
				trim($reason_fail, ','),
			);
		}
		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'.csv');
		$data .= $description;
		$data .= "\r\n";
		$data .= implode(';', $title);
		$data .= "\r\n";
		foreach($outputData as $row){            
			$data .= implode(';', $row);
			$data .= "\r\n";
		}
		return $data;
	}
	
	# прошедшие обучения, которые набрали проходной порог в 65%. Будут назначены на сессию без продления и сбросов попыток.
	public function getGraduatePassed(){
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
		//добавялем BOM
        $data = "\xEF\xBB\xBF";
		$outputData 	= array();			
		$reasonList 	= HM_Debtors_DebtorsModel::getReasonList();			
		$importManager 	= new HM_Debtors_Import_Manager();
		$importManager->restoreFromCache();
		$filename 	 = 'Прошедшие обучения, набрали более 65% от '.date('d.m.Y H:i');
		$description = 'Прошедшие обучения, набрали более 65%  от '.date('d.m.Y H:i');
		$title = array(
			_('ID студента'),
			_('ФИО'),
			_('ID сессии'),
			_('Сессия'),
			_('Старый итоговый балл (Итоговый текущий рейтинг/Рубежный рейтинг)'),
			_('Новый итоговый балл  (Итоговый текущий рейтинг/Рубежный рейтинг)'),
			_('Семестр'),
			_('Контроль'),
			_('Причина'),
		);

		foreach($importManager->getGraduatedDebtorsEnded() as $i) {
			$old_mark = ($i->isDO) ? $i->old_mark 	: $i->old_mark_current.'/'.$i->old_mark_landmark;
			$new_mark = ($i->isDO) ? $i->mark 		: $i->mark_current.'/'.$i->mark_landmark;
			
			$outputData[] = array(
				$i->mid_external,
				$i->fio,
				$i->session_external_id,
				$i->name,
				$old_mark,
				$new_mark,							
				$i->semester,				
				$i->exam_type_name,				
				$reasonList[$i->conflict_reason_code],
			);
		}
		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'.csv');
		$data .= $description;
		$data .= "\r\n";
		$data .= implode(';', $title);
		$data .= "\r\n";
		foreach($outputData as $row){            
			$data .= implode(';', $row);
			$data .= "\r\n";
		}
		return $data;	
	}
	
	# даты продления не изменились с данными в БД. НЕ будут продлены
	public function getNotChanged(){
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
		//добавялем BOM
        $data = "\xEF\xBB\xBF";
		$outputData 	= array();			
		$reasonList 	= HM_Debtors_DebtorsModel::getReasonList();			
		$importManager 	= new HM_Debtors_Import_Manager();
		$importManager->restoreFromCache();
		$filename 	 = 'Данные не изменились от '.date('d.m.Y H:i');
		$description = 'Данные не изменились от '.date('d.m.Y H:i');
		$title = array(
			_('ID студента'),
			_('ФИО'),
			_('ID сессии'),
			_('Сессия'),
			_('Специальность'),
			_('Семестр'),
			_('Контроль'),
			_('Дата продления'),
			_('Дата продления 2'),
			_('Причина'),					
		);				
		foreach($importManager->getAlredyExtended() as $i) {
			
			$time_ended_debtor 		= ($i->time_ended_debtor) 	? (date('d.m.Y', strtotime($i->time_ended_debtor)) ) 	: ('');
			$time_ended_debtor_2 	= ($i->time_ended_debtor_2)	? (date('d.m.Y', strtotime($i->time_ended_debtor_2)) )	: ('');
			
			$outputData[] = array(
				$i->mid_external,
				$i->fio,
				$i->session_external_id,
				$i->name,
				$i->specialty,				
				$i->semester,
				$i->exam_type_name,
				$time_ended_debtor,
				$time_ended_debtor_2,				
				$reasonList[$i->conflict_reason_code],
			);
		}
		
		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'.csv');
		$data .= $description;
		$data .= "\r\n";
		$data .= implode(';', $title);
		$data .= "\r\n";
		foreach($outputData as $row){            
			$data .= implode(';', $row);
			$data .= "\r\n";
		}
		return $data;	
	}
	
	
	# никогда не обучался на данной сессии. Не будет продлен или назначен.
	public function getNotAssign(){
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
		//добавялем BOM
        $data = "\xEF\xBB\xBF";
		$outputData 	= array();			
		$reasonList 	= HM_Debtors_DebtorsModel::getReasonList();			
		$importManager 	= new HM_Debtors_Import_Manager();
		$importManager->restoreFromCache();
		$filename 	 = 'Никогда не обучался на данной сессии от '.date('d.m.Y H:i');
		$description = 'Никогда не обучался на данной сессии от '.date('d.m.Y H:i');
		$title = array(
			_('ID студента'),
			_('ФИО'),
			_('ID сессии'),
			_('Сессия'),
			_('Специальность'),
			_('Семестр'),
			_('Контроль'),
			_('Дата продления'),
			_('Дата продления 2'),						
		);				
		foreach($importManager->getNotAssign() as $i) {
			$time_ended_debtor 		= ($i->time_ended_debtor) 	? (date('d.m.Y', strtotime($i->time_ended_debtor)) ) 	: ('');
			$time_ended_debtor_2 	= ($i->time_ended_debtor_2)	? (date('d.m.Y', strtotime($i->time_ended_debtor_2)) )	: ('');
			
			$outputData[] = array(
				$i->mid_external,
				$i->fio,
				$i->session_external_id,
				$i->name,
				$i->specialty,				
				$i->semester,
				$i->exam_type_name,
				$time_ended_debtor,
				$time_ended_debtor_2,								
			);
		}
		
		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'.csv');
		$data .= $description;
		$data .= "\r\n";
		$data .= implode(';', $title);
		$data .= "\r\n";
		foreach($outputData as $row){            
			$data .= implode(';', $row);
			$data .= "\r\n";
		}
		return $data;
		
	}
	
	# некорректные данные - проблема с датами.
	public function getIncorrect(){
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
		//добавялем BOM
        $data = "\xEF\xBB\xBF";
		$outputData 	= array();			
		$reasonList 	= HM_Debtors_DebtorsModel::getReasonList();			
		$importManager 	= new HM_Debtors_Import_Manager();
		$importManager->restoreFromCache();
		$filename 	 = 'Некорректные данные от '.date('d.m.Y H:i');
		$description = 'Некорректные данные от '.date('d.m.Y H:i');
		$title = array(
			_('ID студента'),
			_('ФИО'),
			_('ID сессии'),
			_('Сессия'),
			_('Специальность'),
			_('Семестр'),
			_('Контроль'),
			_('Дата продления'),
			_('Дата продления 2'),
			_('Причина'),					
		);				
		foreach($importManager->getIncorrectData() as $i) {
			
			$time_ended_debtor 		= ($i->time_ended_debtor) 	? (date('d.m.Y', strtotime($i->time_ended_debtor)) ) 	: ('');
			$time_ended_debtor_2 	= ($i->time_ended_debtor_2)	? (date('d.m.Y', strtotime($i->time_ended_debtor_2)) )	: ('');
			
			$outputData[] = array(
				$i->mid_external,
				$i->fio,
				$i->session_external_id,
				$i->name,
				$i->specialty,				
				$i->semester,
				$i->exam_type_name,
				$time_ended_debtor,
				$time_ended_debtor_2,				
				$reasonList[$i->conflict_reason_code],
			);
		}
		
		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'.csv');
		$data .= $description;
		$data .= "\r\n";
		$data .= implode(';', $title);
		$data .= "\r\n";
		foreach($outputData as $row){            
			$data .= implode(';', $row);
			$data .= "\r\n";
		}
		return $data;		
		
	}
	
	# Не найден студент или сессия в БД
	public function getNotFound(){
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
		//добавялем BOM
        $data = "\xEF\xBB\xBF";
		$outputData 	= array();			
		$reasonList 	= HM_Debtors_DebtorsModel::getReasonList();			
		$importManager 	= new HM_Debtors_Import_Manager();
		$importManager->restoreFromCache();
		$filename 	 = 'Не найден студент или сессия от '.date('d.m.Y H:i');
		$description = 'Не найден студент или сессия от '.date('d.m.Y H:i');
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
			_('Контроль'),
		);
		
		foreach($importManager->getNotFoundDebtors() as $i) {
			$reasons = '';
			if(is_array($i->conflict_reason_code)){
				foreach($i->conflict_reason_code as $code){
					$reasons .= $reasonList[$code].', ';
				}			
				$reasons = trim($reasons, ', ');
			}	
			$groups 				= (!empty($i->groups))		? implode(', ', $i->groups) 							: 'Нет';
			$dateBegin 				= ($i->dateBegin) 			? (date('d.m.Y', strtotime($i->dateBegin)) ) 	 		: ('');
			$dateEnd 				= ($i->dateEnd) 			? (date('d.m.Y', strtotime($i->dateEnd)) ) 	 			: ('');
			$time_ended_debtor 		= ($i->time_ended_debtor) 	? (date('d.m.Y', strtotime($i->time_ended_debtor)) ) 	: ('');
			$time_ended_debtor_2 	= ($i->time_ended_debtor_2) ? (date('d.m.Y', strtotime($i->time_ended_debtor_2)) )	: ('');
			
			$subjectLink  = '';
			$subjectGroup = '';
			if(!empty($i->supposedSubjects)){
				$subjectGroup 			= (!empty($firstSupposedSubjects['groups'])) ? implode(', ', $firstSupposedSubjects['groups']) : '';
				$firstSupposedSubjects 	= reset($i->supposedSubjects);				
				$subjectLink  			= ($firstSupposedSubjects['external_id']) ? $firstSupposedSubjects['external_id'] : 'нет';
				$subjectLink 		   .= ' - ( '.$_SERVER['SERVER_NAME'].$this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card' , 'subject_id' =>$firstSupposedSubjects['id']), null, true).' ) '.$firstSupposedSubjects['name'];
				$subjectLink 		   .= (!empty($firstSupposedSubjects['session_ended'])) ? ' (до '.date('d.m.Y', strtotime($firstSupposedSubjects['session_ended'])).')' : '';
			}
			
			$outputData[] = array(	
				$reasons,
				$i->mid_external,
				$i->fio,
				$groups,
				$i->session_external_id,
				$i->name,
				$i->specialty,
				$dateBegin,
				$dateEnd,
				$time_ended_debtor,
				$time_ended_debtor_2,
				$subjectLink,
				$subjectGroup,
				$i->semester,
				$i->exam_type_name,
			);	
			
			
			if(!empty($i->supposedSubjects)){
				$numRow = 0;
				foreach($i->supposedSubjects as $subj){
					$numRow++;	
					if($numRow == 1){ continue; }
					
					$subjectGroup  = (!empty($subj['groups'])) 	? implode(', ', $subj['groups']) 	: '';
					$subjectLink   = ($subj['external_id']) 	? $subj['external_id'] 				: 'нет';
					$subjectLink  .= ' - ( '.$_SERVER['SERVER_NAME'].$this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card' , 'subject_id' =>$subj['id']), null, true).' ) '.$subj['name'];
					$subjectLink  .= (!empty($subj['session_ended'])) ? ' (до '.date('d.m.Y', strtotime($subj['session_ended'])).')' : '';
					
					
					$outputData[] = array(
						'',
						$i->mid_external,
						'',
						'',
						$i->session_external_id,
						'',
						'',
						'',
						'',
						'',
						'',
						$subjectLink,
						$subjectGroup,
						$i->semester,
						$i->exam_type_name,
					);
				}				
			}
		}		
		
		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'.csv');
		$data .= $description;
		$data .= "\r\n";
		$data .= implode(';', $title);
		$data .= "\r\n";
		foreach($outputData as $row){            
			$data .= implode(';', $row);
			$data .= "\r\n";
		}
		return $data;
	}
	
	
	# изменения в назначении тьторов - обновление дат продления.
	public function getUpdateAssignTutor(){
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
		//добавялем BOM
        $data = "\xEF\xBB\xBF";
		$outputData 	= array();			
		$importManager 	= new HM_Debtors_Import_Manager();
		$importManager->restoreFromCache();
		
		$filename 	 = 'Изменения в назначении тьторов  от '.date('d.m.Y H:i');
		$description = 'Изменения в назначении тьторов  от '.date('d.m.Y H:i');
		$title = array(									
			_('ID тьютора'),	
			_('ФИО тьютора'),
			_('ID сессии'),
			_('Сессия'),
			_('Старая дата продления'),
			_('Новая дата продления'),
			_('Старая дата продления 2'),
			_('Новая дата продления 2'),
		);
		foreach($importManager->getTutorsUpdateSubjects() as $i) {
			
			$old_date_debt = ($i['old_date_debt']) 				? (date('d.m.Y', strtotime($i['old_date_debt'])) ) : ('');
			$old_date_debt = (!isset($i['new_date_debt'])) 		? 'Без изменений' : $old_date_debt;
			
			$new_date_debt = ($i['new_date_debt']) 				? (date('d.m.Y', strtotime($i['new_date_debt'])) ) : ('');
			$new_date_debt = (!isset($i['new_date_debt'])) 		? '-' : $new_date_debt;
			
			
			$old_date_debt_2 = ($i['old_date_debt_2']) 			? (date('d.m.Y', strtotime($i['old_date_debt_2'])) ) : ('');
			$old_date_debt_2 = (!isset($i['new_date_debt_2'])) 	? 'Без изменений' : $old_date_debt_2;
			
			$new_date_debt_2 = ($i['new_date_debt_2']) 			? (date('d.m.Y', strtotime($i['new_date_debt_2'])) ) : ('');
			$new_date_debt_2 = (!isset($i['new_date_debt_2'])) 	? '-' : $new_date_debt_2;
			
			$outputData[] = array(				
				$i['tutor_mid_external'],
				$i['tutor_fio'],
				$i['subject_external_id'],
				$i['subject_name'],
				$old_date_debt,
				$new_date_debt,
				$old_date_debt_2,
				$new_date_debt_2,
			);
		}
		
		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'.csv');
		$data .= $description;
		$data .= "\r\n";
		$data .= implode(';', $title);
		$data .= "\r\n";
		foreach($outputData as $row){            
			$data .= implode(';', $row);
			$data .= "\r\n";
		}
		return $data;
	
		
	}
	
	# Кофликтные ситуации
	# нужны ли тут актуальные тьюторы сессии или тьюторы, дочтупные данному студенту или нет?
	public function getConflict(){
		
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
		//добавялем BOM
        $data = "\xEF\xBB\xBF";
		$importManager 	= new HM_Debtors_Import_Manager();
		$outputData 	= array();	
		$reasonList 	= HM_Debtors_DebtorsModel::getReasonList();	
		$importManager->restoreFromCache();
		
		$filename 	 = 'Кофликтные ситуации от '.date('d.m.Y H:i');
		$description = 'Кофликтные ситуации от '.date('d.m.Y H:i');
		$title = array(									
			_('ID сессии'),
			_('Сессия'),
			_('ID студента'),
			_('ФИО'),				
			_('Группа'),				
			_('ID тьютора'),				
			_('ФИО тьютора'),
			_('ID тьютора 2'),				
			_('ФИО тьютора 2'),
			_('Текущий балл в СДО'),				
			_('Дата первого продления'),				
			_('Дата второго продления'),				
			_('Семестр'),
			_('Контроль'),			
			_('Причина'),
			_('Тьютор в СДО'),
		);
			

		foreach($importManager->getConflictData() as $i) {
						
			$groups 	= (!empty($i->groups)) 	? implode(', ', $i->groups)					: '' ;
			$tutor_id	= (!empty($i->tutor)) 	? implode(', ', array_keys($i->tutor)) 		: '' ;
			$tutor  	= (!empty($i->tutor)) 	? implode(', ', $i->tutor) 					: '' ;
			
			$tutor_id_2	= (!empty($i->tutor_2)) ? implode(', ', array_keys($i->tutor_2))	: '' ;
			$tutor_2  	= (!empty($i->tutor_2)) ? implode(', ', $i->tutor_2) 				: '' ;
			
			#$current_ball = ($i->isDO) ? $i->old_mark : $i->old_mark_current.'/'.$i->old_mark_landmark;
			$current_ball = ($i->isDO) ? $i->old_mark : $i->mark_current.'/'.$i->mark_landmark.'.';
			
			$old_time_ended_debtor = ($i->old_time_ended_debtor) ? date('d.m.Y', strtotime($i->old_time_ended_debtor)) : 'Не продлена';
			$old_time_ended_debtor_2 = ($i->old_time_ended_debtor_2) ? date('d.m.Y', strtotime($i->old_time_ended_debtor_2)) : 'Не продлена';
			
			$outputData[] = array(
				$i->session_external_id,
				$i->name,
				$i->mid_external,
				$i->fio,
				$groups,
				$tutor_id,
				$tutor,
				$tutor_id_2,
				$tutor_2,
				$current_ball,
				$old_time_ended_debtor,
				$old_time_ended_debtor_2,
				$i->semester,
				$i->exam_type_name,
				$reasonList[$i->conflict_reason_code],
				!empty($i->tutors) ? implode(', ', $i->tutors) : '',
			);
		}
		
		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'.csv');
		$data .= $description;
		$data .= "\r\n";
		$data .= implode(';', $title);
        $data .= "\r\n";
		foreach($outputData as $row){            
			$data .= implode(';', $row);
            $data .= "\r\n";
        }
		return $data;
	}
	
	
	
	# Завершили обучение, но не набрали проходной балл. Назначаем и продляем. getGraduatedDebtors	
	public function getGraduate(){
		
		$this->getResponse()->setHeader('Content-type', 'application/octetstream; charset=UTF-8');
		//добавялем BOM
        $data = "\xEF\xBB\xBF";
		$importManager = new HM_Debtors_Import_Manager();
		$importManager->restoreFromCache();
		$filename 	 = 'Переведены из "прошедших обучение" в активное и продлены от '.date('d.m.Y H:i');
		$description = 'Переведены из "прошедших обучение" в активное и продлены от '.date('d.m.Y H:i');
		$title = array(				
			_('ID сессии'),
			_('Сессия'),
			_('Фамилия студента'),
			_('Продлить до'),
			_('Продлить до 2'),
			_('Семестр'),				
			_('Контроль'),
			_('Дополнительно'),				
		);
		$outputData = array();
		foreach($importManager->getGraduatedDebtors() as $i) {
			$reason = ($i->isDO)	?	('Итоговый балл: '.$i->mark.' из 100. Минимум '.$i->mark_needed)	:	('Рубежный рейтинг: '.$i->mark_landmark.' из '.$i->mark_landmark_max.'. Минимум '.$i->mark_landmark_needed);
			
			
			$reason_fail  = '';
			foreach($i->reasonFail as $val){
				$reason_fail .= ' '.$val['message'];
					if(!empty($val['lessons'])){
						$reason_fail .= ' в занятииях #'.implode(', ', $val['lessons']);
					}		
				$reason_fail .= ',';
			}
			
			
			$outputData[] = array(
				$i->session_external_id,
				$i->name,
				$i->fio,
				date('d.m.Y', strtotime($i->time_ended_debtor)),
				date('d.m.Y', strtotime($i->time_ended_debtor_2)),
				$i->semester,
				$i->exam_type_name,
				$reason,
				$reason_fail,
			);
		}

		$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'.csv');
		$data .= $description;
		$data .= "\r\n";
		$data .= implode(';', $title);
        $data .= "\r\n";
		foreach($outputData as $row){            
			$data .= implode(';', $row);
            $data .= "\r\n";
        }
		return $data;		
	}
	
}