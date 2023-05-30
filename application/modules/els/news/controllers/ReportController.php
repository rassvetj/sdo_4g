<?php
class News_ReportController extends HM_Controller_Action_Activity
{
    public function checkAction()
    {
        $subjectId              = (int) $this->_getParam('subject_id', 0);
		$list_landmark_control	= array();	# все найденные Рубежный контроль
		$list_practical_task	= array();	# все найденные Практическое задание
		$incorrect_landmarks    = array(); # неверно заполненные РубежныйКонтроль
		$incorrect_tasks        = array(); # неверно заполненные Задание
		$incorrect_links        = array(); # неверно заполненные ссылки
		$news_count             = 0;
		$correct_landmarks      = array();
		$correct_tasks          = array();
		$user_id                = $this->getService('User')->getCurrentUserId();
			
		if(!$subjectId){
			$this->view->error = _('Не задан номер сессии');
			echo $this->view->render('report/check.tpl');
			die;
		}
        
		$subject = $this->getService('Subject')->getByid($subjectId);
		
		if(!$subject){
			$this->view->error = _('Не найдена сессия');
			echo $this->view->render('report/check.tpl');
			die;
		}
		
		$lessons      = $this->getService('Lesson')->getBySubject($subjectId);
		
		if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TUTOR))) {
			$allowLessons = $this->getService('LessonAssignTutor')->getAssignSubject($user_id, $subjectId);
			if(count($allowLessons)){
				$allowLessonIds = $allowLessons->getList('LID');
				foreach($lessons as $key => $lesson){
					if(!in_array($lesson->SHEID, $allowLessonIds)){
						$lessons->offsetUnset($key);
					}
				}
			} 
		}
		
		
		foreach($lessons as $lesson){
			$landmarkNumber = $lesson->getLandmarkNumber();
			$taskNumber     = $lesson->getNumberTask();
			$list_landmark_control[$landmarkNumber] = $landmarkNumber;
			$list_practical_task[$taskNumber]       = $taskNumber;
		}
		
		$list_landmark_control = array_filter($list_landmark_control);
		$list_practical_task   = array_filter($list_practical_task);
		ksort($list_landmark_control);
		ksort($list_practical_task);
		
		$incorrect_landmarks = $list_landmark_control;
		$incorrect_tasks     = $list_practical_task;
		
		$base_subject        = $this->getService('Subject')->getByid($subject->base_id);
		
		if(
			mb_stripos($base_subject->name, 'очка '              ) === false
			&&
			mb_stripos($base_subject->name, 'классическая заочка') === false
		){
			$this->view->news_count     = (int)$subject->zet;
			$this->view->news_max_count = (int)$subject->zet;
			echo $this->view->render('report/check.tpl');
			die;
		}
		
		$news = $this->getService('News')->getBySubject($subjectId);
		if(!count($news)){
			$this->view->news_count     = 0;
			$this->view->news_max_count = count($list_landmark_control) + count($list_practical_task);
			echo $this->view->render('report/check.tpl');
			die;
		}
		
		# Ищем любую внутреннюю ссылку
		if(
			mb_stripos($base_subject->name, '! ГИА (2018-2019) ОЧКА универс. Курс') !== false
			||
			mb_stripos($base_subject->name, '! Практика') !== false
		){ 
			foreach($news as $item){
				if($item->isHasInnerLink()){ $news_count++; }
			}
			
			# Все недостающие зеты будут считаться как "не заполнен".
			for($number = 1; $number <= $subject->zet; $number++) {
				if($number > $news_count){
					$incorrect_links[$number] = $number;
				}
			}
			
			$this->view->incorrect_links = $incorrect_links;
			$this->view->news_count      = $news_count;
			$this->view->news_max_count = (int)$subject->zet;
			echo $this->view->render('report/check.tpl');
			die;
		}
		
		# не нужно учитывать Практическое задание
		if(mb_stripos($base_subject->name, 'Классическая заочка') !== false){
			foreach($news as $item){
				foreach($list_landmark_control as $number){
					if(isset($correct_landmarks[$number])){ continue; }
					if($item->isHasInnerLink() && $item->isHasModuleNumberLandmark($number)){ 
						$news_count++;
						$correct_landmarks[$number] = $number;
						unset($incorrect_landmarks[$number]);
						continue; 
					}					
				}
			}
			
			$this->view->incorrect_landmarks = $incorrect_landmarks;
			$this->view->news_count          = $news_count;
			$this->view->news_max_count      = count($list_landmark_control);
			echo $this->view->render('report/check.tpl');
			die;
		}
		
		
		foreach($news as $item){
			foreach($list_landmark_control as $number){
				if(isset($correct_landmarks[$number])){ continue; }
				if($item->isHasInnerLink() && $item->isHasModuleNumberLandmark($number)){ 
					$news_count++;
					$correct_landmarks[$number] = $number;
					unset($incorrect_landmarks[$number]);
					continue; 
				}
			}
			
			foreach($list_practical_task as $number){
				if(isset($correct_tasks[$number])){ continue; }
				if($item->isHasInnerLink() && $item->isHasModuleNumberTask($number)){
					$news_count++;
					$correct_tasks[$number] = $number;
					unset($incorrect_tasks[$number]);
					continue;
				}
			}
		}
		
		$this->view->incorrect_landmarks = $incorrect_landmarks;
		$this->view->incorrect_tasks     = $incorrect_tasks;
		$this->view->news_count          = $news_count;
		$this->view->news_max_count      = count($list_landmark_control) + count($list_practical_task);
		echo $this->view->render('report/check.tpl');
		die;
    }

    
}