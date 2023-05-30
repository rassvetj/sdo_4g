<?php
class HM_StudyCard_StudyCardService extends HM_Service_Abstract
{
	public function getIndexSelect() {
		$select = $this->getSelect();
		$select->from(
			array(
				//'us' => 'UopInfoStud'
				'us' => 'study_cards'
			),
			array(
				//'UopInfoID'		=> 'us.UopInfoID',
				'UopInfoID'		=> 'us.study_card_id',
				'Disciplina'	=> 'us.Disciplina',
				'Type'      	=> 'us.Type',
				'Mark' 			=> 'us.Mark',				
				'Ball'      	=> 'us.Ball',
				'NumPop'   		=> 'us.NumPop',		
				'StudyCode'		=> 'us.StudyCode',				
				'DocNum'    	=> 'us.DocNum',						
				//'Date'      	=> 'CONVERT(VARCHAR, us.Date, 104)',				
				'Date'      	=> 'us.Date',				
				'Vid'       	=> 'us.Vid',
				'Teacher'     	=> 'us.Teacher',								
				'Hours'     	=> 'us.Hours',				
				'Company'     	=> 'us.Company',				
				'Position'     	=> 'us.Position',				
				'Manager'     	=> 'us.Manager',				
			)
		);
		
		$select->order('Date DESC');
		
		return $select;
	}
	
	
	public function getDisciplins($mid_external){
		if($mid_external === false){
			return false;
		}
		
		$select = $this->getSelect();
		$select->from('study_cards',			
			array(	
				'study_card_id',
				'StudyCode',
				'DocNum',
				'Date',
				'NumPop',
				'Type',
				'Ball',
				'Vid',
				'Disciplina',
				'Mark',
				'Hours',
				'Teacher',
				'Company',
				'Position',
				'Manager',
				'Semester'
				)
		);		
		$select->where('StudyCode = ?', $mid_external);
		$select->order('Semester ASC');
		$res = $select->query()->fetchAll();
		
		if(!count($res)){
			return false;
		}
		return $res;		
	}
	
	public function getDisciplinsFrom1C($mid_external)
	{
		$mid_external = trim($mid_external);
		if(empty($mid_external)){
			return false;
		}
		
		$data       = array();
		$config     = Zend_Registry::get('config')->soap->student_recordbook;
		$soapClient = new Zend_Soap_Client();
		$params     = array('StudyCode' => $mid_external);
		
		$soapClient->setWsdl($config->wsdl);
		$soapClient->setHttpLogin($config->login);
		$soapClient->setHttpPassword($config->password);
		
		try {
			$response = $soapClient->Get_study_cards($params);
		} catch (Exception $e) {			
			error_log($e);
			return false;
		} 
		
		$items = $response->return->package_study_cards->study_cards;
		if(empty($items)){
			return false;
		}
		
		if(is_object($items)){
			$data[] = array(
				'StudyCode'      => $items->StudyCode,
				'DocNum'         => $items->DocNum,
				'Date'           => $items->Date,
				'NumPop'         => $items->NumPop,
				'Type'           => $items->Type,
				'Ball'           => $items->Ball,
				'Mark'           => $items->Mark,
				'Vid'            => $items->Vid,
				'Disciplina'     => $items->Disciplina,
				'Hours'          => $items->Hours,
				'Teacher'        => $items->Teacher,
				'Company'        => $items->Company,
				'Position'       => $items->Position,
				'Manager'        => $items->Manager,
				'DisciplineCode' => $items->DisciplineCode,
				'Semester'       => (int)$items->Semester,
			);
			return $data;
		}
		
		foreach($items as $item){
			$data[] = array(
				'StudyCode'      => $item->StudyCode,
				'DocNum'         => $item->DocNum,
				'Date'           => $item->Date,
				'NumPop'         => $item->NumPop,
				'Type'           => $item->Type,
				'Ball'           => $item->Ball,
				'Mark'           => $item->Mark,
				'Vid'            => $item->Vid,
				'Disciplina'     => $item->Disciplina,
				'Hours'          => $item->Hours,
				'Teacher'        => $item->Teacher,
				'Company'        => $item->Company,
				'Position'       => $item->Position,
				'Manager'        => $item->Manager,
				'DisciplineCode' => $item->DisciplineCode,
				'Semester'       => (int)$item->Semester,
			);			
		}
		
		usort($data, function($a, $b){
			return ($a['Semester'] - $b['Semester']);
		});
		
		return $data;
	}
	
	
}