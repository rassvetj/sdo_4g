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
					<td colspan="7" style="text-align:right;" ><span>ПР&nbsp;&nbsp;</span></td>					
				</tr>
				<tr style="text-align:center; font-size: 3mm; line-height: 2.7mm;">
					<td colspan="2" style="border: 0.1px solid black; height:12mm; width:46mm;" ><p style="font-size: 0.1mm; line-height: 4mm;">&nbsp;</p>Наименование практики</td>
					<td style="border: 0.1px solid black; width:15mm;" ><p style="font-size: 0.1mm; line-height: 4mm;">&nbsp;</p>Семестр</td>					
					<td style="border: 0.1px solid black; width:20mm;" ><p style="font-size: 0.1mm; line-height: 2mm;">&nbsp;</p>Место<br />проведения<br />практики</td>					
					<td colspan="2" style="border: 0.1px solid black; width:30mm;" ><p style="font-size: 0.1mm; line-height: 2.5mm;">&nbsp;</p>В качестве кого работал<br />(должность)</td>					
					<td style="border: 0.1px solid black; width:35.5mm;" ><p style="font-size: 0.1mm; line-height: 0.2mm;">&nbsp;</p>Ф.И.О. руководителя<br />практики от<br />предприятия<br />(организации)</td>
				</tr>
				<?php $row_num = 1; ?>
				<?php foreach($this->practic as $i):?>				
					<tr style="font-size: 3mm; line-height: 2.7mm;">
						<td  colspan="2" style="border: 0.1px solid black; height:6.5mm;" ><?=$i->Disciplina?></td>
						<td style="border: 0.1px solid black; height:6.5mm;" ><p style="font-size: 0.1mm; line-height: 1.5mm;">&nbsp;</p><?=$i->Semester?></td>						
						<td style="border: 0.1px solid black; "><?=$i->Company?></td>						
						<td colspan="2" style="border: 0.1px solid black; "><?=$i->Position?></td>
						<td style="border: 0.1px solid black; "><?=$i->Manager?></td>
					</tr>
					<?php $row_num++; ?>
					<?php if($row_num > 10){ break; } ?>
				<?php endforeach;?>	
			</table>
		</td>
		<td style="border: 0.1px solid black; height:9.5cm;" cellpadding="0" >
			<table cellpadding="0" style="text-align:center; width:100%">
				<tr style="text-align:left;" >
					<td colspan="2" ></td>
					<td colspan="5" style="text-align:right; font-size: 3.5mm;" ><span style="text-decoration:underline;" ><?=$this->fio;?></span>&nbsp;&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:left;" ><span>&nbsp;&nbsp;АКТИКА</span></td>
					<td colspan="5" style="text-align:right; font-size: 3mm; line-height: 2mm; height:2mm;" >(Фамилия И.О. студента)&nbsp;&nbsp;</td>
				</tr>
				<tr style="text-align:center; font-size: 3mm; line-height: 2.7mm;">
					<td style="border: 0.1px solid black; height:12mm; width:20mm;" ><p style="font-size: 0.1mm; line-height: 3mm;">&nbsp;</p>Общее кол-во<br />час./з.ед</td>
					<td colspan="3" style="border: 0.1px solid black; width:50mm;" ><p style="font-size: 0.1mm; line-height: 2mm;">&nbsp;</p>Ф.И.О. руководителя<br />практики от<br />образовательной организации</td>
					<td style="border: 0.1px solid black; width:28mm;" ><p style="font-size: 0.1mm; line-height: 2mm;">&nbsp;</p>Оценка по<br />итогам<br />аттестации</td>
					<td style="border: 0.1px solid black; width:17mm;" ><p style="font-size: 0.1mm; line-height: 2mm;">&nbsp;</p>Дата проведения<br />аттестации</td>
					<td style="border: 0.1px solid black; width:31.5mm;" ><p style="font-size: 0.1mm; line-height: 2mm;">&nbsp;</p>Подпись и фамилия<br />лица, проводившего<br />аттестацию</td>
				</tr>
				<?php $row_num = 1; ?>
				<?php foreach($this->practic as $i):?>
					<tr style="font-size: 3mm; line-height: 2.9mm;">
						<td style="border: 0.1px solid black; height:6.5mm;" ><p style="line-height: 6.2mm;" ><?=$i->Hours?></p></td>
						<td colspan="3" style="border: 0.1px solid black; height:6.5mm;" ></td>
						<td style="border: 0.1px solid black; "><p style="line-height: 6.2mm;" ><?=$i->Mark?></p></td>
						<td style="border: 0.1px solid black; "><p style="line-height: 6.2mm;" ><?=$i->Date?></p></td>
						<td style="border: 0.1px solid black; "></td>						
					</tr>
					<?php $row_num++; ?>
					<?php if($row_num > 10){ break; } ?>				
				<?php endforeach;?>
				
				
				<tr style="font-size: 4mm; text-align:left;">
					<td colspan="7" style="border: 0.1px solid black; height:8.5mm;" >
						<p style="font-size: 0.1mm; line-height: 1mm;">&nbsp;</p>
						<p style="line-height: 3mm;">
							&nbsp;&nbsp;Руководитель структурного подразделения <span style="text-decoration:underline"><?=$this->director_subdivision_fio_practic?></span>
						</p>					
					</td>
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
			<table cellpadding="0" style="text-align:center; width:100%">
				<tr>
					<td colspan="7" >&nbsp;</td>					
				</tr>
				<tr>
					<td colspan="7" style="text-align:right;" ><span>НАУЧНО-ИССЛЕДОВАТЕЛЬСКАЯ&nbsp;&nbsp;</span></td>					
				</tr>				
				<tr style="text-align:center; font-size: 3mm; line-height: 2.7mm;">
					<td colspan="3" style="border: 0.1px solid black; height:12mm; width:40mm;" ><p style="font-size: 0.1mm; line-height: 3mm;">&nbsp;</p>Вид научно-<br />исследовательской работы</td>
					<td  style="border: 0.1px solid black; width:15mm;" ><p style="font-size: 0.1mm; line-height: 4mm;">&nbsp;</p>Семестр</td>
					<td  style="border: 0.1px solid black; width:16.5mm;" ><p style="font-size: 0.1mm; line-height: 4.5mm;">&nbsp;</p>Дата сдачи</td>					
					<td  style="border: 0.1px solid black; width:25mm;" ><p style="font-size: 0.1mm; line-height: 3mm;">&nbsp;</p>Подпись<br />преподавателя</td>					
					<td  style="border: 0.1px solid black; width:50mm;" ><p style="font-size: 0.1mm; line-height: 3mm;">&nbsp;</p>Фамилия<br />преподавателя</td>					
				</tr>
				<?php $row_num = 1; ?>
				<?php foreach($this->scientific_work as $key => $i):?>
					<?php if($key>=10){ continue; }?>
					<tr style="font-size: 3mm; line-height: 2.7mm;">
						<td colspan="3" style="border: 0.1px solid black; height:6.5mm;" ></td>
						<td style="border: 0.1px solid black; height:6.5mm;" ></td>
						<td style="border: 0.1px solid black; "></td>
						<td style="border: 0.1px solid black; "></td>
						<td style="border: 0.1px solid black; "></td>
					</tr>
					<?php $row_num++; ?>
					<?php if($row_num > 10){ break; } ?>
				<?php endforeach;?>	
				
			
				
				
			</table>
		</td>
		<td style="border: 0.1px solid black; height:9.5cm;" cellpadding="0" >
			<table cellpadding="0" style="text-align:center; width:100%">
				<tr style="text-align:left;" >
					<td colspan="2" >&nbsp;</td>
					<td colspan="5" style="text-align:right; font-size: 3.5mm;" ><span style="text-decoration:underline;" ><?=$this->fio;?></span>&nbsp;&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:left;" ><span>&nbsp;&nbsp;РАБОТА</span></td>
					<td colspan="5" style="text-align:right; font-size: 3mm; line-height: 2mm; height:2mm;" >(Фамилия И.О. студента)&nbsp;&nbsp;</td>
				</tr>				
				<tr style="text-align:center; font-size: 3mm; line-height: 2.7mm;">
					<td colspan="3" style="border: 0.1px solid black; height:12mm; width:40mm;" ><p style="font-size: 0.1mm; line-height: 3mm;">&nbsp;</p>Вид научно-<br />исследовательской работы</td>
					<td  style="border: 0.1px solid black; width:15mm;" ><p style="font-size: 0.1mm; line-height: 4mm;">&nbsp;</p>Семестр</td>
					<td  style="border: 0.1px solid black; width:16.5mm;" ><p style="font-size: 0.1mm; line-height: 4.5mm;">&nbsp;</p>Дата сдачи</td>					
					<td  style="border: 0.1px solid black; width:25mm;" ><p style="font-size: 0.1mm; line-height: 3mm;">&nbsp;</p>Подпись<br />преподавателя</td>					
					<td  style="border: 0.1px solid black; width:50mm;" ><p style="font-size: 0.1mm; line-height: 3mm;">&nbsp;</p>Фамилия<br />преподавателя</td>					
				</tr>
				<?php $row_num = 1; ?>
				<?php foreach($this->scientific_work as $key => $i):?>
					<?php if($key<10){ continue; }?>
					<tr style="font-size: 3mm; line-height: 2.7mm;">
						<td colspan="3" style="border: 0.1px solid black; height:6.5mm;" ></td>
						<td style="border: 0.1px solid black; height:6.5mm;" ></td>
						<td style="border: 0.1px solid black; "></td>
						<td style="border: 0.1px solid black; "></td>
						<td style="border: 0.1px solid black; "></td>
					</tr>
					<?php $row_num++; ?>
					<?php if($row_num > 10){ break; } ?>				
				<?php endforeach;?>	

				<tr style="font-size: 4mm; text-align:left;">
					<td colspan="7" style="border: 0.1px solid black; height:8.5mm;" >
						<p style="font-size: 0.1mm; line-height: 1mm;">&nbsp;</p>
						<p style="line-height: 3mm;">
							&nbsp;&nbsp;Руководитель структурного подразделения <span style="text-decoration:underline"><?=$this->director_subdivision_fio_scientific_work?></span>
						</p>					
					</td>
				</tr>
				
				
				
			</table>
		</td>
	</tr>	
</table>