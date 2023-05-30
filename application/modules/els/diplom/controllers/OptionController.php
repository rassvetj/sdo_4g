<?php
class Diplom_OptionController extends HM_Controller_Action
{
	
	public function confirmAction()
	{	
		$this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();
		
		$user_id = $this->getService('User')->getCurrentUserId();
		$type    = HM_StudentNotification_Agreement_AgreementModel::TYPE_INFO_DIPLOM;
		
		if(empty($user_id)){
			echo json_encode(array('error' => 'Авторизуйтесь'));
			die;
		}
		
		$request			= $this->getRequest();
		$params				= $request->getParams();

		if(!array_key_exists('q_1', $params)){
			echo json_encode(array('error' => 'Выберите ответ в вопросе №1'));
			die;
		}

		if(!array_key_exists('q_2', $params)){
			echo json_encode(array('error' => 'Выберите ответ в вопросе №2'));
			die;
		}

		if(!array_key_exists('q_3', $params)){
			echo json_encode(array('error' => 'Выберите ответ в вопросе №3'));
			die;
		}

		if(!array_key_exists('q_4', $params)){
			echo json_encode(array('error' => 'Выберите ответ в вопросе №4'));
			die;
		}

		if(!array_key_exists('q_5', $params)){
			echo json_encode(array('error' => 'Выберите ответ в вопросе №5'));
			die;
		}

		$show_optional_disciplines       = $params['q_1']=='1' ? 1 : 0;
		$show_education_form             = $params['q_2']=='1' ? 1 : 0;
		$show_combination_education_form = $params['q_3']=='1' ? 1 : 0;      	
      	$show_fast_learning              = $params['q_4']=='1' ? 1 : 0; 
      	$show_another_university         = $params['q_5']=='1' ? 1 : 0;

		$data = array(
			'mid'                             => (int)$user_id,
      		'show_optional_disciplines'       => (int)$show_optional_disciplines,
      		'show_education_form'             => (int)$show_education_form,
      		'show_combination_education_form' => (int)$show_combination_education_form,
      		'show_fast_learning'              => (int)$show_fast_learning,
      		'show_another_university'         => (int)$show_another_university,
		);
		$newItem = $this->getService('DiplomOption')->add($data);
		if(empty($newItem)){
			echo json_encode(array('error' => 'Не удалось сохранить данные'));
			die;
		}

		$serviceAgreement = $this->getService('StudentNotificationAgreement');
		if( !$serviceAgreement->hasAgreement($user_id, $type) ){
			$serviceAgreement->add($user_id, $type);
		}

		echo json_encode(array('message' => 'Информация сохранена', 'hide_popup' => 1));
		die;
	}
	
}