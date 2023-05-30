<table style="font-size:0.4cm;" cellpadding="0" >
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>	
	<!-- Факультативы -->
	<tr cellpadding="0" >
		<td style="border: 0.1px solid black; height:9.5cm;" cellpadding="0" >
			<table cellpadding="0" style="text-align:center; ">
				<tr>
					<td colspan="7" >&nbsp;</td>					
				</tr>
				<tr>
					<td colspan="7" >&nbsp;</td>					
				</tr>
				<tr>
					<td colspan="7" style="text-align:right;" ><span>ГОСУДАРСТВЕННЫЕ&nbsp;&nbsp;</span></td>					
				</tr>
				<tr style="text-align:center; font-size: 3mm; line-height: 2.7mm;">
					<td style="border: 0.1px solid black; height:12mm; width:15mm;" ><p style="font-size: 0.1mm; line-height: 3mm;">&nbsp;</p>№<br />п/п</td>
					<td colspan="3" style="border: 0.1px solid black; width:100mm;" ><p style="font-size: 0.1mm; line-height: 4mm;">&nbsp;</p>Наименование дисциплин (модулей)</td>
					<td colspan="3" style="border: 0.1px solid black; width:31.5mm;" ><p style="font-size: 0.1mm; line-height: 4mm;">&nbsp;</p>Дата сдачи экзамена</td>
				</tr>
				<?php $row_num = 1; ?>
				<?php foreach($this->gos as $i):?>					
					<?php $i['date_exam'] = (strtotime($i['date_exam']) > 0 ? date('d.m.Y', strtotime($i['date_exam'])) : ''); ?>
					<tr style="font-size: 3mm; line-height: 2.7mm;">
						<td style="border: 0.1px solid black; height:8.5mm;" ><p style="font-size: 0.1mm; line-height: 2.8mm;">&nbsp;</p><?=$row_num?></td>
						<td colspan="3" style="border: 0.1px solid black; height:8.5mm;" ><p style="font-size: 0.1mm; line-height: 0.8mm;">&nbsp;</p><?=$i['name']?></td>
						<td colspan="3" style="border: 0.1px solid black; height:8.5mm;" ><p style="font-size: 0.1mm; line-height: 2.8mm;">&nbsp;</p><?=$i['date_exam']?></td>
					</tr>
					<?php $row_num++; ?>
					<?php if($row_num > 10){ break; } ?>
				<?php endforeach;?>	
				
				<tr>
					<td colspan="7" style="text-align:right;" ><span>Студент <span style="text-decoration:underline;"><?=$this->gia_is_passed_fio?></span> допущен к государственной&nbsp;&nbsp;&nbsp;</span></td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:center;" ><span>(Фамилия И.О.)&nbsp;&nbsp;</span></td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:right;" ><span>Руководитель&nbsp;&nbsp;</span></td>
				</tr>
				
				
				
				<?php if($this->view->gia_is_passed):?>
				<?php endif;?>
				
				
				
				
			</table>
		</td>
		<td style="border: 0.1px solid black; height:9.5cm;" cellpadding="0" >
			<table cellpadding="0" style="text-align:center; width:100%; ">
				<tr style="text-align:left;" >
					<td colspan="2" ></td>
					<td colspan="5" style="text-align:right; font-size: 3.5mm;" ><span style="text-decoration:underline;" ><?=$this->fio;?></span>&nbsp;&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:left;" ><span>&nbsp;&nbsp;</span></td>
					<td colspan="5" style="text-align:right; font-size: 3mm; line-height: 2mm; height:2mm;" >(Фамилия И.О. студента)&nbsp;&nbsp;</td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:left;" ><span>&nbsp;&nbsp;ЭКЗАМЕНЫ</span></td>					
				</tr>
				<tr style="text-align:center; font-size: 3mm; line-height: 2.7mm;">
					<td colspan="2" style="border: 0.1px solid black; height:12mm; width30mm;" ><p style="font-size: 0.1mm; line-height: 4mm;">&nbsp;</p>Оценка</td>
					<td colspan="5" style="border: 0.1px solid black; width:104.5mm;" ><p style="font-size: 0.1mm; line-height: 3mm;">&nbsp;</p>Подписи председателя и членов<br />Государственной экзаменационной комиссии</td>							
				</tr>
				<?php $row_num = 1; ?>
				<?php foreach($this->gos as $i):?>
					<tr style="font-size: 3mm; line-height: 2.9mm;">
						<td colspan="2" style="border: 0.1px solid black; height:8.5mm;" ><p style="font-size: 0.1mm; line-height: 2.8mm;">&nbsp;</p><?=$i['ball']?></td>
						<td colspan="5" style="border: 0.1px solid black; height:8.5mm;" ></td>						
					</tr>
					<?php $row_num++; ?>
					<?php if($row_num > 10){ break; } ?>				
				<?php endforeach;?>
				
				<tr>
					<td colspan="7" style="text-align:left;" ><span>&nbsp;&nbsp;итоговой аттестации. Приказ от <span style="text-decoration:underline;"><?=$this->gia_date_take;?></span> № <span style="text-decoration:underline;"><?=$this->gia_order_number?></span></span></td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:center;" ><span>&nbsp;</span></td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:left;" >&nbsp;&nbsp; структурного подразделения <span style="text-decoration:underline"><?=$this->director_subdivision_sign_gos?></span></td>
				</tr>
				
				
			</table>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>	
	<!-- Курсовые -->
	<tr>
		<td style="border: 0.1px solid black; height:9.5cm;" cellpadding="0" >
			<table cellpadding="0" style="text-align:center; width:100%; font-size:4.5mm">
				<tr style="text-align:left;" >
					<td colspan="2" >&nbsp;</td>
					<td colspan="5" style="text-align:right; font-size: 3.5mm;" ><span style="text-decoration:underline;" ><?=$this->fio;?></span>&nbsp;&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:left;" ><span>&nbsp;&nbsp;</span></td>
					<td colspan="5" style="text-align:right; font-size: 3mm; line-height: 2mm; height:2mm;" ><span>(Фамилия И.О. студента)&nbsp;&nbsp;</span></td>
				</tr>
				<tr>
					<td colspan="7" >&nbsp;</td>					
				</tr>
				<tr>
					<td colspan="7" style="text-align:center; font-size: 5mm;" >Выпускная квалификационная работа</td>					
				</tr>
				<tr>
					<td colspan="7" style="text-align:left;" >&nbsp;&nbsp;&nbsp;&nbsp;Форма выпускной квалификационной работы: <span style="text-decoration:underline;" ><?=$this->grad['type_work'];?></span></td>					
				</tr>
				
				<tr>
					<td style="text-align:left; width:16mm;" >&nbsp;&nbsp;&nbsp;&nbsp;Тема:</td>
					<td colspan="5" style="width:127mm; text-align:left; border-bottom:0.1px solid black;" ><?=$this->grad['theme_separated'][1]?></td>
					<td >&nbsp;</td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:center; font-size: 3.5mm;" >(выпускной квалификационной работы)</td>					
				</tr>
				<tr>
					<td style="width:5mm;" >&nbsp;</td>
					<td colspan="5" style="width:137mm;  text-align:left; border-bottom:0.1px solid black;" ><?=$this->grad['theme_separated'][2]?></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td style="width:5mm;" >&nbsp;</td>
					<td colspan="5" style="width:137mm;  text-align:left; border-bottom:0.1px solid black;" ><?=$this->grad['theme_separated'][3]?></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td style="width:5mm;" >&nbsp;</td>
					<td colspan="5" style="width:137mm;  text-align:left; border-bottom:0.1px solid black;" ><?=$this->grad['theme_separated'][4]?></td>
					<td>&nbsp;</td>
				</tr>
				
				
				<tr>
					<td colspan="7" style="text-align:left;" >&nbsp;&nbsp;&nbsp;&nbsp;Руководитель: <span style="text-decoration:underline;"><?=$this->grad['manager']?></span></td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:center; font-size: 3.5mm;" >(Фамилия И.О.)</td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:left;" >&nbsp;&nbsp;&nbsp;&nbsp;Дата защиты: <span style="text-decoration:underline;"><?=$this->grad['date_graduation_work']?></span></td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:left;" >&nbsp;&nbsp;&nbsp;&nbsp;Оценка: <span style="text-decoration:underline;"><?=$this->grad['ball']?></span></td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;Подписи председателя и членов Государственной</td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:left;">&nbsp;&nbsp;&nbsp;&nbsp;экзаменационной комиссии:</td>
				</tr>
			</table>
		</td>
		<td style="border: 0.1px solid black; height:9.5cm;" cellpadding="0" >
			<table cellpadding="0" style="text-align:center; width:100%; font-size:4.5mm;">
				<tr style="text-align:left;" >				
					<td colspan="7" style="text-align:center; font-size: 5mm;" >Решением государственной экзаменационной комиссии</td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:center;" ></td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:left;" >&nbsp;&nbsp;&nbsp;&nbsp;от <span style="text-decoration:underline;" ><?=$this->grad['date_commission']?></span> протокол № <span style="text-decoration:underline;" ><?=$this->grad['protocol_number']?></span></td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:left;" >&nbsp;&nbsp;&nbsp;&nbsp;студенту <span style="text-decoration:underline;" ><?=$this->grad['student_fio']?></span></td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:center; font-size: 3.5mm;" >(фамилия, имя, отчество (последнее – при наличии))</td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:left;" >&nbsp;&nbsp;&nbsp;&nbsp;Присвоена квалификация _______________________________________</td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:center; font-size: 3.5mm;" >(наименование)</td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:left;" >&nbsp;&nbsp;&nbsp;&nbsp;Председатель: <span style="text-decoration:underline;"><?=$this->grad['chair'];?></span>	(подпись)</td>
				</tr>
				<tr>
					<td style="width:5mm;" >&nbsp;</td>
					<td colspan="6" style="text-align:left; width:140mm; height:11mm;" >Члены комиссии: <?=$this->grad['members_commission']?></td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:left;" >&nbsp;&nbsp;&nbsp;&nbsp;_____________________________________________________________</td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:center; font-size: 3.5mm;" >(подписи)</td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:left;" >&nbsp;&nbsp;&nbsp;&nbsp;Руководитель структурного подразделения <span style="text-decoration:underline"><?=$this->director_subdivision_sign_grad?></span></td>
				</tr>				
				<tr>
					<td colspan="7" style="text-align:right; font-size: 3.5mm;" ><span>(подпись, фамилия и.о.)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
				</tr>				
			</table>
		</td>
	</tr>	
</table>