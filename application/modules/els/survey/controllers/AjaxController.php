<?php

class Survey_AjaxController extends HM_Controller_Action
{
	
	protected $_mailTo = 'club@rgsu.net';
	#protected $_mailTo = 'HramovSV@rgsu.net';
	
    public function saveAction()
    {
        #$this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        #Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
		$form = new HM_Form_Base();
		$user = $this->getService('User')->getCurrentUser();
		/*
		$return = array(
            'code' => 0,
            'message' => _('Заполните все поля')
        );
		*/
		
		$request = $this->getRequest();
        if ($request->isPost() || $request->isGet()) {
			if ($form->isValid($request->getParams())) {				
				
				//$bd = DateTime::createFromFormat('d.m.Y', $request->getParam('BirthDate', ''));
				
				$type_id 		= intval($request->getParam('type_id', NULL));
				$fields_type 	= intval($request->getParam('fields_type', NULL));				
				$answerList 	= $this->getService('SurveyAnswers')->getAnswerList($fields_type);				
				$otherAnswers	= HM_Survey_Answers_AnswersModel::otherAnswers();				
				$result 		= array();
				$allParams 		= $request->getParams();
				foreach($allParams as $name_field => $v){					
					$variants = $answerList[$name_field];
					if($variants === NULL){ # не является полем вопроса
						continue;
					} elseif(empty($variants)){ # текстовое поле
						$result[$name_field] = $v;
					} elseif(!empty($variants)) { # поле radio
						if(in_array($v, $otherAnswers)){
							$result[$name_field] = $allParams[$name_field.'_other'];
						} else {
							$result[$name_field] = $answerList[$name_field][$v];
						}
					}
				}

				$data = array(
					'mid_external'						=> $user->mid_external,
					'type'								=> $type_id,
					'data'								=> json_encode($result),					
					'DateCreated' 						=>  new Zend_Db_Expr("NOW()"),					
				);
				
				
				try {																				
					$isInsert = $this->getService('Survey')->insert($data);					
					#$isInsert = false;
					if($isInsert){
						$this->sendEmail($result);
						
						$return = array(
							'code' => 1,
							'message' => _('Спасибо за Ваш ответ!')
						);						
					} else {
						$return = array(
							'code' => 0,
							'message' => _('Ошибка сохранения. Попробуйте позже.')
						);
					}
				} catch (Exception $e) {
					$return = array(
						'code' => 0,
						'message' => _('Ошибка сохранения. Попробуйте позже.')
					);
				}
			}
			
			echo $this->view->notifications(array(array(
				'type' => $return['code'] != 1 ? HM_Notification_NotificationModel::TYPE_ERROR : HM_Notification_NotificationModel::TYPE_SUCCESS,
				'message' => $return['message']
			)), array('html' => true));			
			
		}
		if($return['code'] != 1){
			echo $form->render();	
		}
		
    }
	
	public function sendEmail($data){
		if(empty($data)){ return false; }
		$numRow = 1;
		
		# избавиться от этого
		$nameFields = array(
			'BirthDate' 					=> 'Дата рождения',
			'address_residence' 			=> 'Адрес фактического проживания',
			'Phone' 						=> 'Телефон',
			'EMail' 						=> 'Электронная почта',
			'vk' 							=> 'vk.com',
			'instagram' 					=> 'instagram.com',
			'facebook' 						=> 'facebook.com',
			'twitter' 						=> 'twitter.com',
			'course' 						=> 'Курс',
			'year_graduation' 				=> 'Год окончания',
			'faculty' 						=> 'Факультет',
			'specialty' 					=> 'Специальность ',
			'education_level' 				=> 'Уровень образования',
			'plan_after_graduation' 		=> 'Что Вы планируете делать после получения диплома?',
			'education_after_graduation' 	=> 'Планируете ли Вы продолжить обучение?',
			'is_working' 					=> 'Работаете ли Вы в данный момент?',
			'work_on_specialty' 			=> 'Планируете ли Вы в дальнейшем работать по специальности, полученной в РГСУ?',
			'target_set' 					=> 'Обучение по целевому набору?',
			'is_target_employment' 			=> 'Трудоустройство по результатам целевого обучения?',
			'actual_work_place_company' 	=> 'Место работы фактическое: Компания',
			'actual_work_place_address' 	=> 'Место работы фактическое: Адрес',
			'actual_work_place_phone' 		=> 'Место работы фактическое: Телефон',
			'actual_work_place_position' 	=> 'Место работы фактическое: Должность',
			'planned_work_place_company' 	=> 'Место работы планируемое: Компания',
			'planned_work_place_address' 	=> 'Место работы планируемое: Адрес',
			'planned_work_place_phone' 		=> 'Место работы планируемое: Телефон',
			'planned_work_place_position' 	=> 'Место работы планируемое: Должность',
			'is_invalid' 					=> 'Имеете ли Вы инвалидность?',
			'invalid_degree' 				=> 'Степень инвалидности ',
			'is_ready_join_club' 			=> 'Вы готовы вступить в Клуб выпускников РГСУ (vk.com/rgsuclub, instagram.com/rgsuclub, facebook.com/rgsuclub)?',
			'see_in_club' 					=> 'Что Вы хотели бы видеть в Клубе выпускников РГСУ?',
			'criteria_quality_training' 	=> 'Что, по Вашему мнению, необходимо РГСУ для повышения качества подготовки специалистов?',			
		);
		
		$content  = '<p>На сайте СДО студент '.$data['LastName'].' '.$data['FirstName'].' '.$data['Patronymic'].' заполнил(а) анкету выпускного курса.</p><br>';
		$content .= '<p>Актуальная выгрузка по всем резуьтатам во вложении</p>';
		$content .= '<table style="border: 1px solid black; border-collapse: collapse;">';		
		$content .= '<tr><td style="border: 1px solid black; padding: 3px;">'.$numRow.'</td><td style="border: 1px solid black; padding: 3px;">ФИО</td><td style="border: 1px solid black; padding: 3px;">'.$data['LastName'].' '.$data['FirstName'].' '.$data['Patronymic'].'</td></tr>';
		foreach($data as $key => $val){
			if(!isset($nameFields[$key])){ continue; }
			$numRow++;
			$content .= '<tr><td style="border: 1px solid black; padding: 3px;">'.$numRow.'</td><td style="border: 1px solid black; padding: 3px;">'.$nameFields[$key].'</td><td style="border: 1px solid black; padding: 3px;">'.$val.'</td></tr>';
		}
		$content .= '</table>';
		#$content .= '<hr><p>C любовью, Ваш РГСУ.</p><p style="font-size:12px;line-height:16px;">Call-центр: +7 (495) 748-67-67  |  Приемная комиссия: +7 (495) 748-67-77<br/><a href="http://rgsu.net/">Сайт РГСУ</a>  |  <a href="http://vk.com/rgsu_official">РГСУ Вконтакте</a>  |  <a href="https://twitter.com/RGSU_official">Twitter РГСУ</a>  |  <a href="https://www.facebook.com/rgsu.official">Facebook РГСУ</a></p>';
		
		$mail = new Zend_Mail(Zend_Registry::get('config')->charset);
		$mail->addTo($this->_mailTo);
		$mail->setSubject('Анкета студента выпускного курса с СДО');
		$mail->setType(Zend_Mime::MULTIPART_RELATED);
		$mail->setFromToDefaultFrom();
		$mail->setBodyHtml($content, Zend_Registry::get('config')->charset);

		$attachment_content = $this->createResultFile();
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mime_type = $finfo->buffer($attachment_content);
		$attachment = new Zend_Mime_Part($attachment_content);					
		$attachment->type = $mime_type;
		$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
		$attachment->encoding = Zend_Mime::ENCODING_BASE64;						
		$attachment->filename = 'all_'.date('d-m-Y').'.xls';
		$mail->addAttachment($attachment);
		
		try {
			$mail->send();						
			return true;
		} catch (Zend_Mail_Exception $e) {			
			return false;
		}
	}
	
	
	public function createResultFile(){
		$res  = $this->getService('Survey')->fetchAll($this->quoteInto('type = ?', HM_Survey_SurveyModel::TYPE_VPO));		
		$data = array();
		if(!empty($res)){
			foreach($res as $i){ $data[] = json_decode($i->data, true); }	
		}		
		$this->view->data = $data;		
		$content = $this->view->render('ajax/xls.tpl');
		return $content;		
	}

}