<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_CardStudent extends HM_View_Infoblock_ScreenForm
{                                          
  protected $id = 'card';
  protected $class = 'scrollable';
  
  public function cardStudent($title = null, $attribs = null, $options = null)
  {
    $isHide = true;
	if($isHide){
		$title = '&nbsp;';
		$content = '
			<style>
				#cardStudent { display:none; }
			</style>';			
	} else {
		
		
	
	$this->_userService = $this->getService('User');		
	$user = $this->_userService->getCurrentUser();		
	$len = strlen($user->mid_external);
	if($len != 6 ){ //--только студент может иметь 6-значный код
		return false; 
	}
	
	$serviceContainer = Zend_Registry::get('serviceContainer');
    $this->id = strtolower(substr(str_replace('HM_View_Infoblock_', '', get_class($this)), 0, 1)).substr(str_replace('HM_View_Infoblock_', '', get_class($this)), 1);
    
	/*
    $this->id .= "_".$attribs['param'];
    $select= $serviceContainer->getService('Info')->getOne($serviceContainer->getService('Info')->find($attribs['param']));
    if(!isset($attribs['id'])){
      $attribs['id'] = $this->id;
    }
	*/

	
	//$this->_cardstudentService = $this->getService('Cardstudent');	
	$this->_studyCardService = $this->getService('StudyCard');	

	$select_data = $this->_studyCardService->getIndexSelect();
		
	$where = $this->_studyCardService->quoteInto(
		array('StudyCode=?'),
		array(				
			$this->getService('User')->getCurrentUser()->mid_external,
		)
	);
	$select_data->where($where);

	//--Т.к. это тестовая БД, то данные тут старые. Ручной коннект был к рабочей БД, и => там были актуальные записи.
	if ($items = $select_data->query()->fetchAll()) {	
		
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		
		$content= "
		<table class='cardstudent-widget'>
			<thead>
				<tr>
					<td>"._("Название дисциплины")."</td>
					<td>"._("Оценка")."</td>
					<td>"._("Попытка")."</td>
					<td>"._("Номер документа")."</td>
					<td>"._("Дата документа")."</td>
				</tr>
			</thead>";
		
		foreach ($items as $i) {			
			$content.= "<tr>
				<td>"._($i['Disciplina'])." (".$i['Type'].")</td>				
				<td>"._($i['Mark'])."</td>
				<td>"._($i['NumPop'])."</td>
				<td>"._($i['DocNum'])."</td>
				<td>".date('d.m.Y', strtotime($i['Date']))."</td>
			</tr>";
        }
		$content.= "</table>";
	}
	else {
		$content='нет данных';		
	}
	
	
	}
	
	
    unset($attribs['param']);
    if ($title == null) return $content;
    
    return parent::screenForm($title, $content, $attribs);
    
  }
}