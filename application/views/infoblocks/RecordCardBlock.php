<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_RecordCardBlock extends HM_View_Infoblock_ScreenForm
{                                          
  protected $id = 'record_card';
  protected $class = 'scrollable';
  
  public function recordCardBlock($title = null, $attribs = null, $options = null)
  {
    
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
	//$this->_studyCardService = $this->getService('StudyCard');
		$this->_recService = $this->getService('RecordCard');  
		$config = Zend_Registry::get('config');
		
		$this->view->headLink()->appendStylesheet($config->url->base.'css/rgsu_style.css');
		
		$user = $this->getService('User')->getCurrentUser();
		
		$select = $this->_recService->getIndexSelect();
		
		$where = $this->_recService->quoteInto(
			array('StudyCode=?'),
			array(
				str_replace(' ', '', $user->mid_external),
			)
		);
				
		$select->where($where);
		$select->order('r.DateTake');
			
		
		if ($items = $select->query()->fetchAll()) {
										
			foreach ($items as $row) {
				$DistLearning = ($row['DistLearning'] == 0) ? (_('Нет')) : (_('Да'));
				$SvsuFinance = ($row['SvsuFinance'] == 0) ? (_('Нет')) : (_('Да'));
				
				
				$content.= '<table class="cardstudent cardstudent-widget">';
			  
					$content.= '<thead>';
						$content.= '<tr>';					
							$content.= '<td colspan=2>'._($row['TypeOrder']).' '._($row['Reason']).' </td>';
							$content.= '<td colspan=2>№&nbsp;'._($row['Code']).' от '.$row['DateFrom'].'</td>';		
						$content.= '</tr>';		
					$content.= '</thead>';
					
					$content.= '<tbody>';					  
						$content.= '<tr>';
							$content.= '<td class="f_cap">'._('Состояние').':</td>';		  
							$content.= '<td class="f_status">'._($row['StatusStud']).'</td>';		  
							$content.= '<td class="f_cap">'._('Курс').':</td>';		  
							$content.= '<td class="f_course">'._($row['Course']).'</td>';		  
						$content.= '</tr>';
						
						$content.= '<tr>';
							$content.= '<td class="f_cap">'._('Дата вступления в силу').':</td>';		  
							$content.= '<td class="f_take">'._($row['DateTake']).'</td>';		  
							$content.= '<td class="f_cap">'._('Основа обучения').':</td>';		  
							$content.= '<td>'._($row['Based']).'</td>';		  
						$content.= '</tr>';
						  
						$content.= '<tr>';
							$content.= '<td class="f_cap">'._('Учебный год').':</td>';		  
							$content.= '<td>'._($row['YearStudy']).'</td>';		  
							$content.= '<td class="f_cap">'._('Вид программы обучения').':</td>';		  
							$content.= '<td>'._($row['TypeProgram']).'</td>';		  
						$content.= '</tr>';
						
						$content.= '<tr>';
							$content.= '<td class="f_cap">'._('Факультет').':</td>';		  
							$content.= '<td>'._($row['Faculty']).'</td>';		  
							$content.= '<td class="f_cap">'._('Учебный план').':</td>';		  
							$content.= '<td>'._($row['Curriculum']).'</td>';		  
						$content.= '</tr>';
						  
						$content.= '<tr>';
							$content.= '<td class="f_cap">'._('Специальность').':</td>';		  
							$content.= '<td>'._($row['Speciality']).'</td>';		  
							$content.= '<td class="f_cap">'._('Дистанционное обучение').':</td>';	
							$content.= '<td>'._($DistLearning).'</td>';		
						$content.= '</tr>';
						
						$content.= '<tr>';
							$content.= '<td class="f_cap">'._('Специализация').':</td>';		  
							$content.= '<td>'._($row['Specialization']).'</td>';		  
							$content.= '<td class="f_cap">'._('Финансирование за СВСУ').':</td>';		  				
							$content.= '<td>'._($SvsuFinance).'</td>';		  				
						$content.= '</tr>';
						
						$content.= '<tr>';
							$content.= '<td class="f_cap">'._('Форма обучения').':</td>';		  
							$content.= '<td>'._($row['Form']).'</td>';		  
							$content.= '<td class="f_cap">'._('Примечание').':</td>';		  
							$content.= '<td>'._($row['Note']).'</td>';		  
						$content.= '</tr>';
						
						$content.= '<tr>';
							$content.= '<td class="f_cap">&nbsp;</td>';		  
							$content.= '<td>&nbsp;</td>';		  
							$content.= '<td class="f_order">Приказ:</td>';		  
							$content.= '<td>'.$row['OrderNote'].'</td>';		  
						$content.= '</tr>';
					$content.= '</tbody>';					  
				
				$content .= '</table>';
			}
			
			$content .= '<div class="report-area" ><input type="submit" name="button" target="_blank" onclick="window.open(\'/record-card/print\'); return false;" value="Распечатать" class="ui-button ui-widget ui-state-default ui-corner-all" role="button" aria-disabled="false"></div>';
			
			
		}
		else {			
			$content='нет данных';	
		}
		
		//echo $content;	
		
	
        
	/*
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
					<td>Название дисциплины</td>
					<td>Оценка</td>
					<td>Попытка</td>
					<td>Номер документа</td>
					<td>Дата документы</td>
				</tr>
			</thead>";
		
		foreach ($items as $i) {			
			$content.= "<tr>
				<td>".$i['Disciplina']." (".$i['Type'].")</td>
				<td>".$i['Ball']."</td>
				<td>".$i['NumPop']."</td>
				<td>".$i['DocNum']."</td>
				<td>".$i['Date']."</td>
			</tr>";
        }
		$content.= "</table>";
	}
	else {
		$content='нет данных';		
	}
	*/

    unset($attribs['param']);
    if ($title == null) return $content;
    
    return parent::screenForm($title, $content, $attribs);
    
  }
}