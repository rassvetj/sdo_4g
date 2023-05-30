<?php
require_once APPLICATION_PATH .  '/views/helpers/Score.php';

class HM_View_Helper_MarkSheetTableTutor extends HM_View_Helper_Abstract
{
    public function markSheetTableTutor($persons, $schedules, $scores, $mode = 'page', $subjectId = null, $dataRatingMedium = false, $dataRatingTotal = false, $additional = NULL)
    {
        
		$this->view->moduleData = $additional['moduleData'];
		$this->view->persons   	= $persons;
        $this->view->schedules 	= $schedules;
        $this->view->scores    	= $scores;
        $this->view->dataRatingTotal    = $dataRatingTotal;  # Рубежный рейтинг
        $this->view->dataRatingMedium   = $dataRatingMedium; # Итоговый текущий рейтинг
        $this->view->subject = Zend_Registry::get('serviceContainer')->getService('Subject')->getOne(
            Zend_Registry::get('serviceContainer')->getService('Subject')->find($subjectId)
        );
		
		$this->view->isShowGraduateAction = false;
		$messages = array();
		
		
		# Для ДОТ ничего не завершаем.
		#if(	Zend_Registry::get('serviceContainer')->getService('Subject')->isDOT($subjectId)	){			
			# колонка формирования индивидуальной ведомости доступна только для продленных сессий.
			#$this->view->isShowSingleVedomost = false;
			
			# показывать ли пункт меню "Завершить курс" если нет непроверенных действий со стороны студентов и у всех есть оценка.
			#$this->view->isShowGraduateAction = false;
		#} else {

			#var_dump($additional['filter_user_ids'], $studentIDs);
			$currentUserId	= Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId();
			$subjectService = Zend_Registry::get('serviceContainer')->getService('Subject');
			
			$studentIDs 	= $subjectService->getAvailableStudents($currentUserId, $subjectId);
			
			# отбор студентов по выбраной группе
			if(!empty($additional['filter_user_ids']) && is_array($additional['filter_user_ids'])){
				if($studentIDs === false){ # доступны все
					$studentIDs = $additional['filter_user_ids'];
				} else {
					$studentIDs = array_intersect($studentIDs, $additional['filter_user_ids']);
				}
			}		
			
			#$this->view->isShowSingleVedomost =	( empty($this->view->subject->time_ended_debt) && empty($this->view->subject->time_ended_debt_2) ) ? false : true;
			# от индивидуальных направлений ушли.
			$this->view->isShowSingleVedomost =	false;
			
			$hasNewActionStudent = $subjectService->isNewActionStudent($subjectId, $studentIDs);
			$allStudentsHasMark  = $subjectService->isAllStudentsHasMark($subject_id);
			
			
			$mesage_list = HM_Marksheet_MarksheetModel::getMesages();
			
			if($hasNewActionStudent){ $messages[] = $mesage_list[HM_Marksheet_MarksheetModel::M_HAS_NEW_ACTION_STUDENT]; } 	  #_('В занятиях есть сообщения стуеднтов, на которые Вы не ответили'); }
			if(!$allStudentsHasMark){ $messages[] = $mesage_list[HM_Marksheet_MarksheetModel::M_NOT_ALL_STUDENTS_HAS_MARK]; } #_('Не всем студентам выставлена оценка'); }
			
			$this->view->messages = $messages;
			
			# теперь завершить курс можно и для продленных сессий.
			# от индивидуальных направлений ушли.
			if(
				#empty($this->view->subject->time_ended_debt)
				#&&
				#empty($this->view->subject->time_ended_debt_2) 
				#&&
				!$hasNewActionStudent
				&&
				$allStudentsHasMark
			){
				$this->view->isShowGraduateAction = true;			
			}
		#}
		
        switch ($mode){
        	case 'page'				: return $this->view->render('marksheettable-tutor.tpl'); 				break;
        	case 'page-dot'			: return $this->view->render('marksheettable-tutor-dot.tpl');			break;
        	case 'page-module-main'	: return $this->view->render('marksheettable-tutor-module-main.tpl'); 	break;
        	case 'page-module-slave': return $this->view->render('marksheettable-tutor-module-slave.tpl'); 	break;
        	case 'print'			: return $this->view->render('marksheettable-print.tpl'); 				break;
        	case 'export'			: return $this->view->render('marksheettable-export.tpl'); 				break;
        }
    }
}