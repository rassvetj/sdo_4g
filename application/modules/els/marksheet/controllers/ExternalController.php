<?php
class Marksheet_ExternalController extends HM_Controller_Action
{
   # TODO
   #сделать перевод в прошедшее студентов.
	
	public function listAction()
    {
		$serviceUser 	= $this->getService('User');
		
		$this->view->setHeader(_('Ведомости'));
		
		$user			= $this->getService('User')->getCurrentUser();
		
		$select			= $serviceUser->getSelect();
		
		$fields = array(
			'id'				=> 'm.marksheet_id',
			'number'			=> 'm.external_id',
			'semester'			=> 'm.semester',
			'attempt'			=> 'm.attempt',
			'form_control'		=> 'm.form_control',
			'group_external_id'	=> 'm.group_external_id',
			'date_issue'		=> 'm.date_issue',
			'study_base'		=> 'm.study_base',
			'form_study'		=> 'm.form_study',
			'tutor'				=> 'm.tutor',
			'dean'				=> 'm.dean',
			'discipline'		=> 'm.discipline',
			'group_name'		=> 'sg.name',
		);
		
		$select->from(array('m' => 'marksheet_info'), $fields);
		$select->joinLeft(array('sg' => 'study_groups'), 'sg.id_external = m.group_external_id', array());		
		$select->where($serviceUser->quoteInto('m.commission_members LIKE ?', '%'.$user->mid_external.'%'));
		$select->where($serviceUser->quoteInto('m.attempt = ?', 3));
		
		$this->view->marksheet = $select->query()->fetchAll();
	}
	
	public function viewAction()
	{
		$marksheet_id 	= (int)$this->_request->getParam('marksheet_id', false);
		$user 			= $this->getService('User')->getCurrentUser();
		
		if(empty($marksheet_id)){
			$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не задана ведомость')));
			$this->_redirector->gotoSimple('list', 'external', 'marksheet');
		}
		
		$marksheet = $this->getService('Marksheet')->getById($marksheet_id);
		
		if(empty($marksheet)){
			$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Ведомость не найдена')));
			$this->_redirector->gotoSimple('list', 'external', 'marksheet');
		}
		
		$this->view->setHeader(_('Ведомость') . ' №' . $marksheet->external_id );
		
		$subject 	= $this->getService('Subject')->getByCode($marksheet->subject_external_id);
		$students 	= $this->prepareStudents($marksheet->students);		
		$students 	= $this->joinMarks($students, $subject);
		$students 	= $this->joinAdditional($students);
		
		$marksheet->group_name 			= $this->getService('StudyGroup')->getNameByCode($marksheet->group_external_id);
		$marksheet->commission_chairman	= $this->prepareCommission($marksheet->commission_chairman, true);
		
		$confirms						= $this->getService('SubjectMarkConfirm')->getBySubject($subject->subid);
		$marksheet->commission_members	= $this->prepareCommission($marksheet->commission_members);
		$marksheet->commission_members	= $this->joinConfirms($marksheet->commission_members, $confirms);
		
		$marksheet->files				= $this->getService('FilesMarksheet')->getSubjectMarksheets($subject->subid, $marksheet->external_id); 
		
		$this->view->all_confirmed	= $this->isAllConfirmed($marksheet->commission_members);		
		$this->view->is_chairman 	= ($marksheet->commission_chairman->MID == $user->MID) ? true : false;
		$this->view->marksheet 		= $marksheet;
		$this->view->students 		= $students;
		$this->view->current_user	= $user;
	}
	
	public function recalculateMarkAction()
	{
		$marksheet_id 	= (int)$this->_request->getParam('marksheet_id', false);
		$user 			= $this->getService('User')->getCurrentUser();
		
		if(empty($marksheet_id)){
			$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не задана ведомость')));
			$this->_redirector->gotoSimple('list', 'external', 'marksheet');
			die;
		}
		
		$marksheet = $this->getService('Marksheet')->getById($marksheet_id);		
		if(empty($marksheet)){
			$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Ведомость не найдена')));
			$this->_redirector->gotoSimple('list', 'external', 'marksheet');
			die;
		}
		
		$marksheet->commission_chairman	= $this->prepareCommission($marksheet->commission_chairman, true);
		if($marksheet->commission_chairman->MID != $user->MID){
			$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Пересчитать балл может только председатель комиссии')));
			$this->_redirector->gotoSimple('view', 'external', 'marksheet', array('marksheet_id' => $marksheet_id));
			die;
		}
		
		$subject = $this->getService('Subject')->getByCode($marksheet->subject_external_id);
		if(!$subject){
			$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Сессия не определена')));
			$this->_redirector->gotoSimple('list', 'external', 'marksheet');
			die;
		}
		
		$marksheet_files = $this->getService('FilesMarksheet')->getSubjectMarksheets($subject->subid, $marksheet->external_id); 		
		if(!empty($marksheet_files)){
			$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Нельзя менять оценку после формирования ведомости')));
			$this->_redirector->gotoSimple('view', 'external', 'marksheet', array('marksheet_id' => $marksheet_id));
			die;
		}
		
		$students = $this->prepareStudents($marksheet->students);
		if(!$students){
			$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Студенты не найдены')));
			$this->_redirector->gotoSimple('list', 'external', 'marksheet');
			die;
		}
		
		$this->getService('Subject')->updateLandmark($subject->subid);
		
		foreach($students as $student){
			$cid = (int)$subject->subid;
			$mid = (int)$student->MID;
			if(empty($cid) || empty($mid)){
				continue;
			}
			$this->getService('Subject')->setScore($cid, $mid);
		}
		
		$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Балл пересчитан')));
		$this->_redirector->gotoSimple('view', 'external', 'marksheet', array('marksheet_id' => $marksheet_id));
		die;		
	}
	
	# TODO
	# Оценки могут задавать или тьютор или члены комиссии все.
	public function setMarkAction()
	{
		$this->_helper->getHelper('layout')->disableLayout();        
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		$this->getHelper('viewRenderer')->setNoRender();
		
		$user = $this->getService('User')->getCurrentUser();
		
		if(!$user){
			echo Zend_Json::encode(array('error' => 'Авторизуйтесь'));
			die;
		}
		
		$student_id = (int)$this->_request->getParam('student_id', false);
		if(empty($student_id)){
			echo Zend_Json::encode(array('error' => 'Не выбран студент'));
			die;
		}
		
		$mark_current	= $this->prepareMark($this->_request->getParam('mark_current', false));
		$mark_landmark	= $this->prepareMark($this->_request->getParam('mark_landmark', false));
		if($mark_current === false && $mark_landmark === false){
			echo Zend_Json::encode(array('error' => 'Укажите балл'));
			die;
		}
		$mark_current	= HM_Subject_Mark_MarkModel::filterMarkCurrent($mark_current);
		$mark_landmark	= HM_Subject_Mark_MarkModel::filterMarkLandmark($mark_landmark);
		
		$marksheet_id = (int)$this->_request->getParam('marksheet_id', false);
		if(empty($marksheet_id)){
			echo Zend_Json::encode(array('error' => 'Не указана вдомость'));
			die;
		}
		
		$marksheet = $this->getService('Marksheet')->getById($marksheet_id);
		if(empty($marksheet)){
			echo Zend_Json::encode(array('error' => 'Не найдена вдомость'));
			die;
		}
		
		$subject = $this->getService('Subject')->getByCode($marksheet->subject_external_id);
		if(empty($subject)){
			echo Zend_Json::encode(array('error' => 'Не найдена сессия для текущей ведомости по коду '.$marksheet->subject_external_id));
			die;
		}
		
		$marksheet_files = $this->getService('FilesMarksheet')->getSubjectMarksheets($subject->subid, $marksheet->external_id); 
		
		if(!empty($marksheet_files)){
			echo Zend_Json::encode(array('error' => 'Нельзя менять оценку после формирования ведомости'));
			die;
		}
		
		$subject_id = $subject->subid;		
		$old_data	= $this->getService('SubjectMark')->getRow($subject_id, $student_id);
		
		if($mark_current === false){
			$mark_current = $old_data->mark_current;
		}
		if($mark_landmark === false){
			$mark_landmark = $old_data->mark_landmark;
		}
		
		$mark = $this->getMark($mark_current, $mark_landmark);
		
		if($mark_current == $old_data->mark_current && $mark_landmark == $old_data->mark_landmark && $mark == $old_data->mark){
			echo Zend_Json::encode(array('error' => 'Новый балл совпадает со старым'));
			die;
		}
		
		
		$new_data	= array(
			'cid' 			=> $subject_id,
            'mid' 			=> $student_id,           
			'need_to_1c' 	=> 1,
            'confirmed' 	=> HM_Subject_Mark_MarkModel::MARK_CONFIRMED,
			'mark' 			=> $mark,					
		); 
		
		# ограничить максимум балл для каждого параметра, получть текстовую оценку
		if($mark_current !== false){
			$new_data['mark_current'] = $mark_current;
		}
		
		if($mark_landmark !== false){
			$new_data['mark_landmark'] = $mark_landmark;
		}
		
		if(empty($old_data)){
			$result = $this->getService('SubjectMark')->insert($new_data);
		} else {
			$result = $this->getService('SubjectMark')->updateWhere($new_data, array('cid = ?' => $subject_id, 'mid = ?' => $student_id));
		}
		
		
		if(!$result){
			echo Zend_Json::encode(array('error' => 'Не удалось сохранть оценку'));
			die;
		}
		
		$collection 	   = $this->getService('Subject')->getActiveLessons($subject_id);
		$max_rating_medium = $this->getService('Lesson')->getMaxBallMediumRating($subject_id, $collection);
		$max_rating_medium = ($max_rating_medium > 80) ? 80 : $max_rating_medium;
		
		if($this->getService('Lesson')->isPassMediumRating($max_rating_medium, $new_data['mark_current'], false)){
			$five_scale_mark = HM_Subject_Mark_MarkModel::getFiveScaleMark($new_data['mark']);
		} else {
			$five_scale_mark = HM_Subject_Mark_MarkModel::getFiveScaleMark($new_data['mark_current']);
		}
		
		echo Zend_Json::encode(array(	'message' 		=> 'Оценка сохранена',
										'mark_current' 	=> empty($new_data['mark_current'])  ? 0 : $new_data['mark_current'],
										'mark_landmark' => empty($new_data['mark_landmark']) ? 0 : $new_data['mark_landmark'],
										'mark' 			=> $new_data['mark'], 
										'ball' 			=> HM_Subject_Mark_MarkModel::getTextFiveScaleMark($five_scale_mark, $subject->exam_type), 
		));
		die;		
	}	
	
	
	# TODO права доступа на закрытие - только председатель комиссии.
	public function graduateAction()
	{
		$this->_helper->getHelper('layout')->disableLayout();        
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		$this->getHelper('viewRenderer')->setNoRender();
		
		$user = $this->getService('User')->getCurrentUser();
		
		if(!$user){
			$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Авторизуйтесь')));
			$this->_redirect('/');
			die;
		}
		
		$marksheet_id = (int)$this->_request->getParam('marksheet_id', false);
		if(empty($marksheet_id)){
			$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не указана вдомость')));
			$this->_redirect('/');
			die;
		}
		
		$marksheet = $this->getService('Marksheet')->getById($marksheet_id);
		if(empty($marksheet)){
			$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не найдена вдомость')));
			$this->_redirect('/');
			die;
		}
		
		$subject = $this->getService('Subject')->getByCode($marksheet->subject_external_id);
		if(empty($subject)){
			$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не найдена сессия для ведомости по коду').' '.$marksheet->subject_external_id));
			$this->_redirect('/');
			die;
		}
		
		$exam_types 					= HM_Subject_SubjectModel::getExamTypes();
		$subject->exam_type_name 		= $exam_types[$subject->exam_type];
		
		$marksheet->group_name			= $this->getService('StudyGroup')->getNameByCode($marksheet->group_external_id);
		$marksheet->commission_chairman	= $this->prepareCommission($marksheet->commission_chairman, true);
		
		$confirms						= $this->getService('SubjectMarkConfirm')->getBySubject($subject->subid);
		$marksheet->commission_members	= $this->prepareCommission($marksheet->commission_members);
		$marksheet->commission_members	= $this->joinConfirms($marksheet->commission_members, $confirms);
		
		if($marksheet->commission_chairman->MID != $user->MID){
			$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Вы не являетесь председателем комиссии')));
			$this->_redirect('/');
			die;
		}
		
		$students						= $this->prepareStudents($marksheet->students);
		
		if(empty($students)){
			$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Нет ни одного студента')));
			$this->_redirect('/');
			die;
		}
		
		
		$students 						= $this->joinMarks($students, $subject);
		$students 						= $this->joinAdditional($students);
		
		$serviceSubject = $this->getService('Subject');
		foreach($students as $student){
			$serviceSubject->assignGraduated($subject->subid, $student->MID);
		}
		
		
		
		$isMainModule	= $serviceSubject->isMainModule($subject->subid);
		if($isMainModule){
			$moduleSubjects = $serviceSubject->getModuleSubjects($subject->module_code, $subject->semester);
			foreach($moduleSubjects as $moduleSubject){
				if($subject->subid == $moduleSubject->subid){ continue; }
				
				foreach($students as $student){
					$serviceSubject->assignGraduated($moduleSubject->subid, $student->MID);
				}
			}
		}
		
		
		
		$this->view->subject			= $subject;
		$this->view->marksheet			= $marksheet;
		$this->view->students 			= $students;
		
		
		# программа сессии
		$subject_programm_ids = $this->getService('ProgrammEvent')->getSubjectProgramms($subject->subid);
		if(!empty($subject_programm_ids)){
			$subject_programms = $this->getService('Programm')->fetchAll($this->getService('Programm')->quoteInto(
				"programm_id IN (?) AND (name LIKE 'КЛН-%' OR name LIKE 'МИН-%' OR name LIKE 'ОШ-%' OR name LIKE 'ПАВ-%')", $subject_programm_ids)
			);
		}
		
		$this->view->isProgrammFilial = false;
		if(count($subject_programms) > 0){
			$this->view->isProgrammFilial = true;
		}
		
		$content = $this->view->render('/external/export/pdf.tpl');
		
		#echo $content;
		#die;
		
		try {
			$output = $this->generateFile($content);
		} catch (Exception $e) {
			echo 'Не удалось сформировать файл. ',  $e->getMessage(), "\n";
			die;
		}
		
		$doc_name = date('d.m.Y').'-'.$subject->name.'.pdf';
		
		$inserted = $this->getService('FilesMarksheet')->addFileFromBinary(
			$output,
			$doc_name, 
			$subject->subid,
			array(
				'author_id' 			=> $user->MID,
				'ext'					=> 'pdf',				
				'group_id'				=> $this->getService('StudyGroup')->getByCode($marksheet->group_external_id)->group_id,
				'marksheet_external_id'	=> $marksheet->external_id,
			)
		);
		
		$this->_helper->redirector->gotoSimple('index', 'get', 'marksheet', array('file_id' => $inserted->file_id));
		die;
		
	}
	
	
	public function confirmMarkAction()
	{
		$this->_helper->getHelper('layout')->disableLayout();        
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		$this->getHelper('viewRenderer')->setNoRender();
		
		$user = $this->getService('User')->getCurrentUser();
		
		if(!$user){
			$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Авторизуйтесь')));
			$this->_redirect('/');
			die;
		}
		
		$marksheet_id = (int)$this->_request->getParam('marksheet_id', false);
		if(empty($marksheet_id)){
			$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не указана вдомость')));
			$this->_redirect('/');
			die;
		}
		
		$marksheet = $this->getService('Marksheet')->getById($marksheet_id);
		if(empty($marksheet)){
			$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не найдена вдомость')));
			$this->_redirect('/');
			die;
		}
		
		$subject = $this->getService('Subject')->getByCode($marksheet->subject_external_id);
		if(empty($subject)){
			$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не найдена сессия для ведомости по коду').' '.$marksheet->subject_external_id));
			$this->_redirect('/');
			die;
		}
		
		$is_confirm = $this->getService('SubjectMarkConfirm')->save($subject->subid, $marksheet->external_id);
		if(!$is_confirm){
			$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Не удалось подтвердить')));
			$this->_redirect('/');
			die;
		}
		
		$this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_SUCCESS, 'message' => _('Вы успешно подтвердили оценки')));
		$this->_helper->redirector->gotoSimple('view', 'external', 'marksheet', array('marksheet_id' => $marksheet_id));
		die;		
	}
	
	
	
	public function prepareStudents($str_externals)
	{
		if(empty($str_externals)){
			return false;
		}
		$codes = explode(',', $str_externals);
		$codes = array_filter($codes);
		if(empty($codes)){
			return false;
		}
		return $this->getService('User')->getByIdExternal($codes);
	}
	
	public function prepareCommission($mid_extarnals, $is_single = false)
	{
		$mid_extarnals = explode(',', $mid_extarnals);
		$mid_extarnals = array_filter($mid_extarnals);
		if(empty($mid_extarnals)){ return false; }
		$users = $this->getService('User')->getByIdExternal($mid_extarnals);
		if(empty($users)){ return false; }
		
		if($is_single){
			return $this->getService('User')->getOne($users);
		}
		return $users;
	}
	
	public function prepareMark($mark)
	{
		if($mark === false){ return $mark; }
		$mark = str_replace(',', '.', $mark);
		$mark = floatval($mark);
		return round($mark);
	}
	
	private function joinMarks($students, $subject)
	{
		if(empty($students)){
			return $students;
		}
		
		$serviceMark	= $this->getService('SubjectMark');
		$marks 			= $serviceMark->fetchAll($serviceMark->quoteInto(array('cid = ?', ' AND mid IN (?)'), array($subject->subid, $students->getList('MID'))));
		
		if(empty($marks)){
			return $students;
		}
		
		$marks = $this->prepareMarks($marks);
		
		foreach($students as $student){
			# у каждого студента должна быть оценка. Поэтому заполняем пустыми оценками.
			$student->marks = $this->setMarksDefault($subject->exam_type);
		}
		
		$collection 	   = $this->getService('Subject')->getActiveLessons($subject->subid);
		$max_rating_medium = $this->getService('Lesson')->getMaxBallMediumRating($subject->subid, $collection);
		$max_rating_medium = ($max_rating_medium > 80) ? 80 : $max_rating_medium;					
		
		foreach($marks as $mark_info){
			foreach($students as $student){
				if($student->MID == $mark_info->mid){
					$mark_info->mark 			= $this->getMark($mark_info->mark_current, $mark_info->mark_landmark);
					
					if($this->getService('Lesson')->isPassMediumRating($max_rating_medium, $mark_info->mark_current, false)){
						$mark_info->five_scale_mark = HM_Subject_Mark_MarkModel::getFiveScaleMark($mark_info->mark_current + $mark_info->mark_landmark);						
					} else {
						$mark_info->five_scale_mark = HM_Subject_Mark_MarkModel::getFiveScaleMark($mark_info->mark_current);
					}
					
					$mark_info->ball 			= HM_Subject_Mark_MarkModel::getTextFiveScaleMark($mark_info->five_scale_mark, $subject->exam_type);
					
					$student->marks = $mark_info;
					break;
				}
			}
		}
		return $students;
	}
	
	private function joinAdditional($students)
	{
		if(!$students){ return $students; }
		
		$numbers = $this->getService('RecordCard')->getRecordbookNumbers($students->getList('MID'));
		
		if(empty($numbers)){
			return $students;
		}
		
		foreach($students as $student){
			$student->recordBookNumber = $numbers[$student->MID];
		}
		
		return $students;
	}
	
	private function joinConfirms($members, $confirms)
	{
		if(empty($confirms)){
			return $members;
		}
		
		foreach($members as $member){
			foreach($confirms as $confirm){
				if($member->MID == $confirm->tutor_id){
					$member->confirm = $confirm;
				}
			}
		}
		
		return $members;
	}
	
	
	
	
	
	private function generateFile($content)
	{
		require_once("dompdf/dompdf_config.inc.php");
		$content = '<html ><meta http-equiv="content-type" content="text/html; charset=utf-8" />'.$content.'</html>';				
		$dompdf = new DOMPDF();
			
		$customPaper = array(0,0,580,790);		
		$dompdf->set_paper($customPaper,'landscape');
		
		$dompdf->load_html($content);
		$dompdf->render();		
		return $dompdf->output();
	}
	
	public function isAllConfirmed($members)
	{
		foreach($members as $member){
			if(empty($member->confirm)){ return false; }
		}
		return true;
	}
	
	private function setMarksDefault($subject_exam_type)
	{
		$data = array(
 			'mark_current'	=> 0, 
			'mark_landmark' => 0, 
		);
		$data['mark'] 				= $this->getMark($data['mark_current'], $data['mark_landmark']);
		$data['five_scale_mark'] 	= HM_Subject_Mark_MarkModel::getFiveScaleMark($data['mark']);
		$data['ball']				= HM_Subject_Mark_MarkModel::getTextFiveScaleMark($data['five_scale_mark'], $subject_exam_type);
		
		$model = new HM_Subject_Mark_MarkModel($data);
		return $model; 
	}
	
	private function getMark($mark_current, $mark_landmark)
	{
		#if(empty($mark_landmark)){
		#	return 0;
		#}
		return $mark_current + $mark_landmark;
	}
	
	private function prepareMarks($marks)
	{
		if(empty($marks)){ return $marks; }
		foreach($marks as $mark){
			$mark->mark_current		= empty($mark->mark_current) 	? 0 : $mark->mark_current;
			$mark->mark_landmark 	= empty($mark->mark_landmark) 	? 0 : $mark->mark_landmark;
		}
		return $marks;
	}
	
    
}

