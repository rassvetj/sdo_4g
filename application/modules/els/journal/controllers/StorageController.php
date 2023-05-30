<?php
class Journal_StorageController extends HM_Controller_Action_Subject
{
   
	public function saveAction()
	{
		$this->getHelper('viewRenderer')->setNoRender();
		
		$lessonId 	 = $this->_getParam('lesson_id', 0);
		$subjectId 	 = $this->_getParam('subject_id', 0);
		$currentId 	 = $this->getService('User')->getCurrentUserId();
		$request  	 = $this->getRequest();
		$postData 	 = $request->getPost();
		$days        = $request->getPost('day');
		$newDayId    = false;
		$insertData = array();
		$updateData = array();
		$modifiedDays   = array();
		$modifiedUsers  = array();
		$exsistingItems = array();
		
		if(empty($postData)){
			$this->_flashMessenger->addMessage(array(
					'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
					'message' 	=> _('Нет данных для изменения')
			));
			if ($this->_getParam('referer_redirect')) {
				$this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
			}
			
			$this->_redirector->gotoSimple('extended', 'laboratory', 'journal',
				array(
					'lesson_id' 	=> $lessonId,
					'subject_id' 	=> $subjectId,
				)
			);
			die;
		}
		
		$lesson = $this->getService('Lesson')->getLesson($lessonId);
		if(!$lesson){
			$this->_flashMessenger->addMessage(array(
					'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
					'message' 	=> _('Занятие не найдено')
			));
			if ($this->_getParam('referer_redirect')) {
				$this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
			}
			
			$this->_redirector->gotoSimple('extended', 'laboratory', 'journal',
				array(
					'lesson_id' 	=> $lessonId,
					'subject_id' 	=> $subjectId,
				)
			);
			die;
		}
		
		if(!empty($days)){
			foreach($days as $dayId => $dayCaption){
				$data = array(
					'date_lesson' => date('Y-m-d', strtotime($dayCaption)),
				);
				if($dayId == 'new'){
					$data['lesson_id']   = $lessonId;
					$data['date_create'] = new Zend_Db_Expr('GETDATE()');
					$data['author_id']   = $currentId;
					$newDay = $this->getService('LessonJournal')->insert($data);
					if(empty($newDay->journal_id)){
						$this->_flashMessenger->addMessage(array(
							'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
							'message' 	=> _('Не удалось создать новую колонку')
						));
						continue;
					}
					$newDayId = $newDay->journal_id;
					$this->_flashMessenger->addMessage(_('Новая колонка добавлена'));
					continue;
				}
				$modifiedDays[$dayId] = $dayId;
				$data['date_update'] = new Zend_Db_Expr('GETDATE()');
				
				if(!$this->getService('LessonJournal')->updateWhere($data, array('journal_id = ?' => $dayId))){
					$this->_flashMessenger->addMessage(array(
						'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
						'message' 	=> _('Не удалось изменить заголовок колонки на') . ' "' . $dayCaption . '"',
					));
				}
			}	
		}
		
		foreach($postData as $itemType => $items){
			if($itemType == 'day'){ continue; }
			foreach($items as $rawParam => $itemValue){
				$params = explode('_', $rawParam);
				$userId = intval($params[0]);
				$dayId  = $params[1];
				
				$modifiedUsers[$userId] = $userId;
				
				if($dayId == 'new'){
					$insertData[$rawParam][$itemType]    = $itemValue;
					$insertData[$rawParam]['journal_id'] = $newDayId;
					$insertData[$rawParam]['MID']        = $userId;
					continue;
				}
				
				$modifiedDays[$dayId] = $dayId;
				
				$updateData[$rawParam][$itemType]    = $itemValue;
				$updateData[$rawParam]['MID']        = $userId;
				$updateData[$rawParam]['journal_id'] = $dayId;
			}
		}
		
		if(!empty($modifiedDays) && !empty($modifiedUsers)){
			$rawExsistingItems = $this->getService('LessonJournalResult')->fetchAll($this->getService('LessonJournalResult')->quoteInto(array('journal_id IN (?) ', ' AND MID IN (?)'), array($modifiedDays, $modifiedUsers)));
		}
		
		if(count($rawExsistingItems)){
			foreach($rawExsistingItems as $item){
				$key = $item->MID . '_' . $item->journal_id;
				$exsistingItems[$key] = $key; # Если нужно, то сюда можно добавить сам элемент. Например, для сравнения новых и старых данные перед обновлением.
			}
		}
		
		# переброска данных в добавление, если колонка есть, но старых данных по студенту нет
		foreach($updateData as $key => $item){
			if(!array_key_exists($key, $exsistingItems)){
				$insertData[$key] = $item;
				unset($updateData[$key]);
			}
		}
		
		if(!empty($updateData)){
			foreach($updateData as $data){				
				$data['date_update']    = new Zend_Db_Expr('GETDATE()');
				$data['last_author_id'] = $currentId;
				
				$where = array('journal_id = ?' => intval($data['journal_id']), 'MID = ?' => intval($data['MID']));
				
				if(!$this->getService('LessonJournalResult')->updateWhere($data, $where)){
					$this->_flashMessenger->addMessage(array(
						'type' 		=> HM_Notification_NotificationModel::TYPE_ERROR,
						'message' 	=> _('Не удалось изменить данные по студенту') . ' #' . $data['MID'],
					));
				}
			}
		}
		
		if(!empty($insertData)){
			foreach($insertData as $data){
				$data['author_id']   = $currentId;
				$data['lesson_id']   = $lessonId;
				$data['date_create'] = new Zend_Db_Expr('GETDATE()');
				$this->getService('LessonJournalResult')->insert($data);
			}
			$this->_flashMessenger->addMessage(_('Данные успешно добавлены'));
		}
		
		if(!empty($modifiedUsers)){
			foreach($modifiedUsers as $userId){
				$this->getService('LessonJournalResult')->recalculateMark($userId, $lesson);				
				if($subjectId){
					$this->getService('Subject')->setScore($subjectId, $userId);
				}
			}			
		}
		
		if ($this->_getParam('referer_redirect')) {
			$this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
		}

		$this->_redirector->gotoSimple('extended', 'laboratory', 'journal',
			array(
				'lesson_id' 	=> $lessonId,
				'subject_id' 	=> $subjectId,
			)
		);
		die;
	}
	
	
	
	
	public function deleteDayAction()
	{
		# проверить права доступа - если организатор обучения - то может.
		# если преподаватель, то только если он назначен на это занятие. Сделать после реализации назначения на занятие.
		$this->getHelper('viewRenderer')->setNoRender();
		
		$lessonId 		= $this->_getParam('lesson_id', 0);
		$subjectId 		= $this->_getParam('subject_id', 0);
		$day_id 		= $this->_getParam('day_id', 0);
		$currentId 		= $this->getService('User')->getCurrentUserId();
		$serviceJ		= $this->getService('LessonJournal');
		$serviceJResult = $this->getService('LessonJournalResult');
		$serviceLAssign = $this->getService('LessonAssign');
		
		$serviceJ->updateWhere(array('is_hidden' => HM_Lesson_Journal_JournalModel::STATUS_HIDDEN_YES), array('journal_id = ?' => intval($day_id)));
		# берем всех студентов и для них находим балл за занятие.
		
		$userData = $this->getService('LessonJournalResult')->getJournalResult($lessonId);
		
		if(!empty($userData)){
			$lesson = $this->getService('Lesson')->getLesson($lessonId);
			foreach($userData as $user_id => $row){
				$isUpdate = $serviceJResult->recalculateMark($user_id, $lesson, null);
			}
		}
		
		$this->_flashMessenger->addMessage(_('Данные успешно удалены.'));
		
		if ($this->_getParam('referer_redirect')) {
			$this->_redirector->gotoUrl($_SERVER['HTTP_REFERER']);
		}		

		$this->_redirector->gotoSimple('extended', 'laboratory', 'journal',
			array(
				'lesson_id' 	=> $lessonId,
				'subject_id' 	=> $subjectId,						
			)
		);
		die;
	}
}