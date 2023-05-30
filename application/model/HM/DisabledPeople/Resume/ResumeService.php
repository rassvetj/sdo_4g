<?php
require_once APPLICATION_PATH . '/../library/tcpdf/tcpdf.php';
require_once APPLICATION_PATH . '/../library/phpqrcode/qrlib.php';
require_once APPLICATION_PATH . '/../library/phpqrcode/qrconfig.php';

class HM_DisabledPeople_Resume_ResumeService extends HM_Service_Abstract
{
	/**
	 * получаем шаблон в зивисимости от типа резюме и входных данных
	 * return HTML
	*/
	public function generateTemplate($type, $data){
		$host = new Zend_View_Helper_ServerUrl;
		$html = '';
		$html .= '	<table style="padding-left: 10px">
						<tr>	
							<td style="width:40%"><img src="'.'http://'.$host->getHost().'/'.$data['userImg'].'"></td>						
							<td style="width:60%">
								<p>'.$data['job_vacancy'].'</p>
								<p>'.$data['fio'].'</p>
							</td>
						</tr>
					</table>					
					<table style="padding-left: 10px">
						<tr><td>&nbsp;</td></tr>
						<tr>			
							<td style="width:40%">Уровень дохода:</td>
							<td style="width:60%">'.$data['income_level'].' рублей</td>
						</tr>
						<tr>			
							<td>Контактный телефон:</td>
							<td>'.$data['phone'].'</td>
						</tr>
						<tr>			
							<td>E-mail:</td>
							<td>'.$data['email'].'</td>
						</tr>
						
						<!-- -->
						<tr><td>&nbsp;</td></tr>
						<tr>			
							<td colspan="2"><span style="font-size: 19; font-weight:bold;">Абилимпикс</span></td>			
						</tr>
						<tr>			
							<td>Название компетенции:</td>
							<td>'.$data['competence'].'</td>
						</tr>		
						<tr>			
							<td>Результат регионального/национального конкурса:</td>
							<td>'.$data['result_competition'].'</td>
						</tr>
						
						<!-- -->
						<tr><td>&nbsp;</td></tr>
						<tr>			
							<td colspan="2"><span style="font-size: 19; font-weight:bold;">Образование</span></td>			
						</tr>
						<tr>			
							<td>Учебное заведение:</td>
							<td>'.$data['institution'].'</td>
						</tr>
						<tr>			
							<td>Дата окончания:</td>
							<td>'.$data['graduation_date'].'</td>
						</tr>		
						<tr>			
							<td>Факультет:</td>
							<td>'.$data['faculty'].'</td>
						</tr>
						<tr>			
							<td>Специальность:</td>
							<td>'.$data['specialty'].'</td>
						</tr>
						<tr>			
							<td>Форма обучения:</td>
							<td>'.$data['form_study'].'</td>
						</tr>
								
						<!-- -->
						<tr><td>&nbsp;</td></tr>
						<tr>			
							<td colspan="2"><span style="font-size: 19; font-weight:bold;">Опыт работы</span></td>			
						</tr>
						<tr>			
							<td>Период работы:</td>
							<td>с '.$data['work_period_begin'].' по '.$data['work_period_end'].'</td>
						</tr>
						<tr>			
							<td>Должность:</td>
							<td>'.$data['position'].'</td>
						</tr>
						<tr>			
							<td>Название организации:</td>
							<td>'.$data['organization'].'</td>
						</tr>
						<tr>			
							<td>Должностные обязанности и достижения:</td>
							<td><p>'.$data['job_function'].'</p><strong>Достижения:</strong> '.$data['achievements'].'</td>
						</tr>

						<!-- -->
						<tr><td>&nbsp;</td></tr>
						<tr>			
							<td colspan="2"><span style="font-size: 19; font-weight:bold;">Личная информация</span></td>			
						</tr>
						<tr>			
							<td>Город проживания:</td>
							<td>'.$data['city'].'</td>
						</tr>
						<tr>			
							<td>Ближайшее метро: </td>
							<td>'.$data['metro'].'</td>
						</tr>
						<tr>			
							<td>Дата рождения:</td>
							<td>'.$data['date_birth'].'</td>
						</tr>
				 

						<!-- -->
						<tr><td>&nbsp;</td></tr>
						<tr>			
							<td colspan="2"><span style="font-size: 19; font-weight:bold;">Иностранные языки и компьютерные навыки</span></td>			
						</tr>
						<tr>			
							<td>Английский язык:</td>
							<td>'.$data['english'].'</td>
						</tr>
						<tr>			
							<td>Компьютерные навыки и знания:</td>
							<td>'.$data['computer_skills'].'</td>
						</tr>

						<!-- -->
						<tr><td>&nbsp;</td></tr>
						<tr>			
							<td colspan="2"><span style="font-size: 19; font-weight:bold;">Дополнительная информация</span></td>			
						</tr>
						<tr>			
							<td>О себе:</td>
							<td>'.$data['about'].'</td>
						</tr>
						<tr>			
							<td>Рекомендации:</td>
							<td>'.$data['recommendations'].'</td>
						</tr>						
					</table>';
		return $html;
	}
	
	
	public function createPDF($type, $data){
		$html		= $this->generateTemplate($type, $data);		
		$name_f 	= $this->generateFileName();		
		$tempDir 	= APPLICATION_PATH . '/../public/upload/files/';
					
		$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
		$pdf->SetFont('times', 'BI', 13, '', 'false');
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor(_('RGSU'));
		$pdf->SetTitle(_('Резюме'));
		$pdf->SetSubject(_('Резюме'));
		$pdf->SetKeywords(_('Резюме'));

		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins(2, 5, 5);

		$pdf->AddPage(); 
		$pdf->SetXY(2, 5, 5);
		$pdf->SetDrawColor(100, 100, 0); 		
		
		$pdf->writeHTML($html, true, false, false, false, '');  
		$pdfFilePath = $tempDir.$name_f.'.pdf';
		$pdf->Output($pdfFilePath,'F');
		return $pdfFilePath;
	}
	
	//генерация названия файла
	public function generateFileName(){		
		$hashe = md5( microtime() . mt_rand() );
		$result = '';
		$array = array_merge(range('a','z'), range('0','9'));
		for($i = 0; $i < 5; $i++){
			$result .= $array[mt_rand(0, 35)];
		}
		return $result;
	}
  
    
}