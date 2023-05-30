<?php
class Internships_SendController extends HM_Controller_Action
{
	
  
    public function indexAction()
    {
		
		
		
		
		$current_user				= $this->getService('User')->getCurrentUser();
		$request 					= $this->getRequest();
		
		$fio		= $request->getParam('fio', false);
		$phone		= $request->getParam('phone', false);
		$email		= $request->getParam('email', false);
		
		# тип заявки может быть в get-параметре. Приоритет в POST-данных
		$type = (int)$request->getPost('type', false);
		if(empty($type)){
			$type = (int)$request->getParam('type', false);	
		}
		
		$type_list = HM_Internships_InternshipsModel::getTypeListAllow();
		
		if(!array_key_exists($type, $type_list)){
			echo json_encode(array('error' => 1, 'message' => _('Срок подачи заявки истек')));
			die;
		}
		
		
		
		$degree		= $request->getParam('degree', 		array()); # уровень. Код - это код языка.
		
		
		if(empty($fio)){
			echo json_encode(array('error' => 1, 'message' => _('Укажите ФИО')));
			die;
		}
		
		if(empty($phone)){
			echo json_encode(array('error' => 1, 'message' => _('Укажите Ваш номер телефона')));
			die;
		}
		
		if(empty($email)){
			echo json_encode(array('error' => 1, 'message' => _('Укажите Ваш email')));
			die;
		}
		
		if(empty($degree)){
			echo json_encode(array('error' => 1, 'message' => _('Выберите язык и укажите уровень знания')));
			die;
		}
		
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			echo json_encode(array('error' => 1, 'message' => _('Введен некорректный email')));
			die;
		} 	
		
		
		$serviceInternships = $this->getService('Internships');		
		$degree_prepare  	= $serviceInternships->prepareDegree($degree);		
		$degree_as_string_db = $serviceInternships->convertDegreeToString($degree_prepare);
		
		$data = array(			
			'type'		=> $type,
			'MID'		=> (int)$current_user->MID,
			'fio' 		=> $fio,
			'phone' 	=> $phone,
			'email' 	=> $email,			
			'languages'	=> $degree_as_string_db,			
		);
		if( !$serviceInternships->add($data) ){
			echo json_encode(array('error' => 1, 'message' => _('Не удалось сохранить данные')));
			die;
		}
		
		$data['languages_data'] = $degree_prepare;
		$data['country'] 		= HM_Internships_InternshipsModel::getTypeName($type);
		
		$is_send = $this->send($data);
		
		if(!$is_send){
			echo json_encode(array('error' => 1, 'message' => _('Не удалось отправить Ваш запрос')));
			die;
		}
		
		echo json_encode(array('message' => _('Ваш запрос отправлен')));
		die;
	}
	
	
	private function send($data)
	{
		
		$mail_to 	= HM_Internships_InternshipsModel::getMailTo();
		$validator	= new Zend_Validate_EmailAddress();
		
		if (!strlen($mail_to) || !$validator->isValid($mail_to)){
			return false;
		}
		
		$theme_name	= HM_Internships_InternshipsModel::getTheme($data['type']);
		
		$languages = '';
		foreach($data['languages_data'] as $i){
			$languages .= ''.$i['language_name'].': '.$i['degree_name'].'<br />';
		}
		$languages .= '';
		
		
		$text = '<table style="border: 1px solid #bfd9e6; border-collapse: collapse;">'
					.'<tr>'
						.'<td style="font-weight: bold; border: 1px solid #bfd9e6; padding: 5px;">Грант на стажировку в</td>'
						.'<td style="border: 1px solid #bfd9e6; padding: 5px;">'.$data['country'].'</td>'
					.'</tr>'
					.'<tr>'		
						.'<td style="font-weight: bold; border: 1px solid #bfd9e6; padding: 5px;">ФИО</td>'
						.'<td style="border: 1px solid #bfd9e6; padding: 5px;">'.$data['fio'].'</td>'
					.'</tr>'
					.'<tr>'
						.'<td style="font-weight: bold; border: 1px solid #bfd9e6; padding: 5px;">Email</td>'
						.'<td style="border: 1px solid #bfd9e6; padding: 5px;">'
							.'<a href="mailto:'.$data['email'].'">'.$data['email'].'</a>'
							.'<a href="mailto:student-email-begin_'.$data['email'].'_student-email-end" style="color: white;" >.</a>'
							.'<a href="mailto:student-name-begin_'.$data['fio'].'_student-name-end" style="color: white;" >.</a>'
						.'</td>'						
					.'</tr>'
					.'<tr>'
						.'<td style="font-weight: bold; border: 1px solid #bfd9e6; padding: 5px;">Контактный номер телефона</td>'
						.'<td style="border: 1px solid #bfd9e6; padding: 5px;">'.$data['phone'].'</td>'
					.'</tr>'
					.'<tr>'
						.'<td style="vertical-align: top; font-weight: bold; border: 1px solid #bfd9e6; padding: 5px;">Владение иностранными языками</td>'
						.'<td style="border: 1px solid #bfd9e6; padding: 5px;">'.$languages.'</td>'
					.'</tr>'
				.'</table>';
		
		
		
		$mail = new Zend_Mail(Zend_Registry::get('config')->charset);
		
		$mail->setFromToDefaultFrom();
		$mail->addTo($mail_to);
		$mail->setSubject($theme_name);
		$mail->setType(Zend_Mime::MULTIPART_RELATED);			
		$mail->setBodyHtml($text, Zend_Registry::get('config')->charset);
		
		try {				
			$mail->send();
        } catch (Zend_Mail_Exception $e) {                
			 return false;
		}		
		return true;
		
	}
	
	
	
}




