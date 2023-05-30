<?php

class DisabledPeople_AjaxController extends HM_Controller_Action
{
    
	protected $_emailTo  = 'MamzinaKR@rgsu.net';
	
	public function confirmedAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
	    exit;
    }
	
	
	
	
	public function saveSurveyAction()
    {
        $this->getHelper('viewRenderer')->setNoRender();
     	
		$form = new HM_Form_Survey();
		$user = $this->getService('User')->getCurrentUser();
		$fio  = $user->LastName.' '.$user->FirstName.' '.$user->Patronymic;
		$request = $this->getRequest();
        if ($request->isPost() || $request->isGet()) {
			if ($form->isValid($request->getParams())) {				
				
				$bd = DateTime::createFromFormat('d.m.Y', $request->getParam('BirthDate', ''));
				
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
					if($isInsert){
						$questionsList 	= $this->getService('SurveyQuestions')->fetchAll($this->quoteInto('type = ?', HM_Survey_SurveyModel::TYPE_FIELDS_DISABLED_PEOPLE))->getList('code', 'name');
						$emailResult 	= array();
						foreach($questionsList as $code => $name){
							$emailResult[$name] = $result[$code];
						}						
						$this->sendEmail($fio, $emailResult);
						
						$return = array(
							'code' => 1,
							'message' => _('Спасибо за Ваш ответ!')
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
			echo "<script>
					$('input[name=\"dp7\"]').change(function(){
						if($(this).val() == '78'){ 
							$('#dp7_other').show();
						} else {
							$('#dp7_other').hide();
						}			
					});
					
					if($('input[name=\"dp7\"]:checked').val() == '78'){
						$('#dp7_other').show();
					}	

					$('input[name=\"dp10\"]').change(function(){
						if($(this).val() == '99'){ 
							$('#dp10_other').show();
						} else {
							$('#dp10_other').hide();
						}			
					});
					
					if($('input[name=\"dp10\"]:checked').val() == '99'){
						$('#dp10_other').show();
					}						
				</script>";
		}
		
    }
	
	protected function sendEmail($fio, $result){
		if(empty($result)){ return false; }		
		$validator = new Zend_Validate_EmailAddress();		
		if (!strlen($this->_emailTo) || !$validator->isValid($this->_emailTo)) { return false; }
		$mail = new Zend_Mail(Zend_Registry::get('config')->charset);
		$messageText = '';
		$messageText .= '<b>Студент:</b> '.$fio.'<br><br>';
		$messageText .= '<table style="border: 1px solid black; border-collapse: collapse;">';
		$messageText .= '<tr><td style="border: 1px solid black;"><b>Вопрос</b></td><td style="border: 1px solid black;"><b>Ответ</b></td>';
		foreach($result as $question => $answer){
			$messageText .= '<tr><td style="border: 1px solid black;">'.$question.'</td><td style="border: 1px solid black;">'.$answer.'</td>';			
		}
		$messageText .= '</table>';
		
		#$messageText .= '<hr style="border: none;border-bottom: 1px dotted #ccc;">
		#<p>C любовью, Ваш РГСУ.</p><p style="font-size:12px;line-height:16px;">Call-центр: +7 (495) 748-67-67  |  Приемная комиссия: +7 (495) 748-67-77<br/><a href="http://rgsu.net/">Сайт РГСУ</a>  |  <a href="http://vk.com/rgsu_official">РГСУ Вконтакте</a>  |  <a href="https://twitter.com/RGSU_official">Twitter РГСУ</a>  |  <a href="https://www.facebook.com/rgsu.official">Facebook РГСУ</a></p>';

		$mail->setSubject('Результаты анкетирования в разделе ОВЗ');	
		$mail->setFromToDefaultFrom(); 
		$mail->addTo($this->_emailTo);
		$mail->setType(Zend_Mime::MULTIPART_RELATED);
		$mail->setBodyHtml($messageText, Zend_Registry::get('config')->charset);
		try {
			$mail->send();
			return true;
		} catch (Zend_Mail_Exception $e) {                
			#echo $e->getMessage();
			return false;
		}
		
	}

}