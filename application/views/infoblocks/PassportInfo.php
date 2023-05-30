<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_PassportInfo extends HM_View_Infoblock_ScreenForm
{                                          
  protected $id = 'passport_info';
  protected $class = 'scrollable';
  
  public function passportInfo($title = null, $attribs = null, $options = null)
  {
    
	$this->_userService = $this->getService('User');		
	$user = $this->_userService->getCurrentUser();		
	
	$serviceContainer = Zend_Registry::get('serviceContainer');
    $this->id = strtolower(substr(str_replace('HM_View_Infoblock_', '', get_class($this)), 0, 1)).substr(str_replace('HM_View_Infoblock_', '', get_class($this)), 1);
    ###########################	
	$dtBirth = DateTime::createFromFormat('Y-m-d 00:00:00.000', $user->BirthDate);
	$msgYellow = false;
	$msgRed = false;
	
	if(!$dtBirth){
		#$content = 'День рождения не указан';		
	} else {
		$delta = time() - $dtBirth->getTimestamp();
		$dtCurrent = new DateTime();
		$interval = $dtBirth->diff($dtCurrent);
		$age = $interval->format('%y');
		if($age < 20 || 22 < $age){
			#$content = 'Возраст не подходящий';
		} else {		
			$select = $this->_userService->getSelect();
			$select->from('student_passport',array('IssuedDate'));
			$select->where($this->_userService->quoteInto('mid_external = ?', $user->mid_external));		
			$select->order(array('IssuedDate DESC'));
			$pasInfo = $select->query()->fetchObject();
			if(empty($pasInfo)){
				#$content = 'Паспортные данные отсутствуют';
			} else {
				$dtBirth->setTime(00,00,00);
				$dtPassport = DateTime::createFromFormat('Y-m-d 00:00:00.000', $pasInfo->IssuedDate);
				if(!$dtPassport){
					#$content = 'Не указана дата выдачи паспорта';
				} else {
					$interval = $dtBirth->diff($dtPassport);
					$delta = $interval->format('%y');
					if($delta > 20){
						#$content = 'Паспорт уже выдан и актуальный.';
					} else {						
						$dtPassport->modify('+6 year');  # крайняя дата получения паспорта
						
						if($dtCurrent > $dtPassport){ # поезд уехал. Время получения паспорта истекло.
							$msgRed = 'Уважаемый студент!<br>Напоминаем что  срок вашего паспорта истек '.$dtPassport->format('d.m.Y').'.<br>Вам необходимо поменять паспорт и сообщить новые данные в деканат!!!';
						} else {
							$str = 'Уважаемый студент!<br>Напоминаем что '.$dtPassport->format('d.m.Y').' у вас заканчивается срок действия паспорта.<br>Вам необходимо поменять паспорт и сообщить новые данные в деканат!!!';
							$dtPassport->modify('-2 month');	
							if($dtCurrent > $dtPassport){ # осталось менее 2 месяцев до даты окончания срока паспорта. Можно уже трубить студенту
								$msgYellow = $str;
							}
						}						
					}					
				}				
			}		
		}		
	} 
	if($msgRed){
		$title = 'Замена паспорта';
		$content = '<p style="color:red; font-size: 19px;">'.$msgRed.'</p>';
	} elseif($msgYellow) {
		$title = 'Замена паспорта';
		$content = '<p style="color:red; font-size: 19px;">'.$msgYellow.'</p>';
	} else {
		$title = '&nbsp;';
		$content = '
			<style>
				#passportInfo { display:none; }
			</style>';		
	}
	
	##########################
    unset($attribs['param']);
    if ($title == null) return $content;
    
    return parent::screenForm($title, $content, $attribs);
    
  }
}