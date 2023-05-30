<?php
//require_once APPLICATION_PATH . '/modules/els/ticket/forms/TicketForm.php';
class RecordCard_PrintController extends HM_Controller_Action
{
    //protected $_techsupportService = null;
    //protected $_supportRequestId  = 0;    
    
    public function init()
    {
        //$this->_supportRequestId = (int) $this->_getParam('support_request_id', 0);
        //$this->_techsupportService = $this->getService('Techsupport');
        //$this->_ticketService = $this->getService('Ticket');  
        $this->_recService = $this->getService('RecordCard');  
		
		//$this->_helper->layout()->disableLayout(); 
        //this->_helper->viewRenderer->setNoRender(true);
		
        parent::init();
    }
    
    
    public function indexAction()
    {
        $config = Zend_Registry::get('config');
		$this->view->setHeader(_('Учетная карточка'));
		
		
		$user = $this->getService('User')->getCurrentUser();
		
		$select = $this->_recService->getIndexSelect();
		
		$where = $this->_recService->quoteInto(
			array('StudyCode=?'),
			array(
				str_replace(' ', '', $user->mid_external),
			)
		);
				
		$select->where($where);
		$select->order(array('DateTake', 'Course'));
			
		if ($items = $select->query()->fetchAll()) {			
				
			$content = '<html>
							<head>
								<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />								
								<style>
								table.cardstudent td{padding:5px;}
									table.cardstudent thead td, table.cardstudent td.f_course { font-weight:bold; font-size: 14px;}
									table.cardstudent td.f_cap { width: 150px; font-style: italic; }
									table.cardstudent td{ .min-width: 35%; width: 180px; }
									table.cardstudent { width: 900px; margin: 0 auto; border-collapse: collapse; font-size: 12px; margin-bottom: 25px; }
									table.cardstudent tbody td { border: 1px solid black; }
									table.cardstudent tbody td.f_status { color: rgb(50, 89, 189); font-weight: 600; }
									table.cardstudent tbody td.f_order { font-style: normal; text-decoration: underline; font-weight: 600; text-align: right; }
									table.cardstudent tbody td.f_take{ text-align: right; color: rgb(50, 89, 189); font-weight: 600; }
								</style>
							</head>
							<body onload="window.print()";>';	
			
			foreach ($items as $row) {
				$DistLearning = ($row['DistLearning'] == 0) ? (_('Нет')) : (_('Да'));
				$SvsuFinance = ($row['SvsuFinance'] == 0) ? (_('Нет')) : (_('Да'));
				
				
				$content.= '<table class="cardstudent">';
			  
					$content.= '<thead>';
						$content.= '<tr>';					
							$content.= '<td colspan=2>'.$row['TypeOrder'].' '.$row['Reason'].' </td>';
							$content.= '<td colspan=2>№&nbsp;'.$row['Code'].' от '.$row['DateFrom'].'</td>';		
						$content.= '</tr>';		
					$content.= '</thead>';
					
					$content.= '<tbody>';					  
						$content.= '<tr>';
							$content.= '<td class="f_cap">Состояние:</td>';		  
							$content.= '<td class="f_status">'.$row['StatusStud'].'</td>';		  
							$content.= '<td class="f_cap">Курс:</td>';		  
							$content.= '<td class="f_course">'.$row['Course'].'</td>';		  
						$content.= '</tr>';
						
						$content.= '<tr>';
							$content.= '<td class="f_cap">Дата вступления в силу:</td>';		  
							$content.= '<td class="f_take">'.$row['DateTake'].'</td>';		  
							$content.= '<td class="f_cap">Основа обучения:</td>';		  
							$content.= '<td>'.$row['Based'].'</td>';		  
						$content.= '</tr>';
						  
						$content.= '<tr>';
							$content.= '<td class="f_cap">Учебный год:</td>';		  
							$content.= '<td>'.$row['YearStudy'].'</td>';		  
							$content.= '<td class="f_cap">Вид программы обучения:</td>';		  
							$content.= '<td>'.$row['TypeProgram'].'</td>';		  
						$content.= '</tr>';
						
						$content.= '<tr>';
							$content.= '<td class="f_cap">Факультет:</td>';		  
							$content.= '<td>'.$row['Faculty'].'</td>';		  
							$content.= '<td class="f_cap">Учебный план:</td>';		  
							$content.= '<td>'.$row['Curriculum'].'</td>';		  
						$content.= '</tr>';
						  
						$content.= '<tr>';
							$content.= '<td class="f_cap">Специальность:</td>';		  
							$content.= '<td>'.$row['Speciality'].'</td>';		  
							$content.= '<td class="f_cap">Дистанционное обучение:</td>';	
							$content.= '<td>'.$DistLearning.'</td>';		
						$content.= '</tr>';
						
						$content.= '<tr>';
							$content.= '<td class="f_cap">Специализация:</td>';		  
							$content.= '<td>'.$row['Specialization'].'</td>';		  
							$content.= '<td class="f_cap">Финансирование за СВСУ:</td>';		  				
							$content.= '<td>'.$SvsuFinance.'</td>';		  				
						$content.= '</tr>';
						
						$content.= '<tr>';
							$content.= '<td class="f_cap">Форма обучения:</td>';		  
							$content.= '<td>'.$row['Form'].'</td>';		  
							$content.= '<td class="f_cap">Примечание:</td>';		  
							$content.= '<td>'.$row['Note'].'</td>';		  
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
			
				$content .= '</body>';
			$content .= '</head>';			
			
		}
		else {			
			$content='нет данных';	
		}
		
		echo $content;
		
		$this->_helper->viewRenderer->setNoRender(true);
		exit();
		
    }
	
	
	public function preDispatch(){
        //$this->_helper->layout()->disableLayout(); 
        //$this->_helper->viewRenderer->setNoRender(true);
    }
	
	
	
	
	
	
    
    
}