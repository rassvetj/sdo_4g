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
					<td colspan="7" style="text-align:right;" ><span>ФАКУЛЬТАТИВНЫЕ&nbsp;&nbsp;</span></td>					
				</tr>
				<tr>
					<td colspan="7" style="text-align:center; font-size: 4.5mm; line-height: 2mm; height:4mm;" >Результаты промежуточной аттестации (экзамены)</td>
				</tr>
				<tr style="text-align:center; font-size: 3mm; line-height: 2.7mm;">
					<td style="border: 0.1px solid black; height:8mm; width:6mm;" ><p style="font-size: 0.1mm; line-height: 1mm;">&nbsp;</p>№<br />п/п</td>
					<td style="border: 0.1px solid black; width:50mm;" ><p style="font-size: 0.1mm; line-height: 1mm;">&nbsp;</p>Наименование дисциплины<br />(модуля), раздела</td>
					<td style="border: 0.1px solid black; width:15mm;" ><p style="font-size: 0.1mm; line-height: 1mm;">&nbsp;</p>Общее кол-<br />во час./з.ед</td>
					<td style="border: 0.1px solid black; width:15mm;" ><p style="font-size: 0.1mm; line-height: 2mm;">&nbsp;</p>Оценка</td>
					<td style="border: 0.1px solid black; width:15mm;" ><p style="font-size: 0.1mm; line-height: 1mm;">&nbsp;</p>Дата сдачи<br />экзамена</td>
					<td style="border: 0.1px solid black; width:15mm;" >Подпись<br />преподават<br />еля</td>
					<td style="border: 0.1px solid black; width:30.5mm;" ><p style="font-size: 0.1mm; line-height: 1mm;">&nbsp;</p>Фамилия<br />преподавателя</td>
				</tr>
				<?php $row_num = 1; ?>
				<?php foreach($this->facultative_exams as $i):?>				
					<tr style="font-size: 3mm; line-height: 2.7mm;">
						<td style="border: 0.1px solid black; height:6.5mm;" ><p style="line-height: 6.2mm;" ><?=$row_num?></p></td>
						<td style="border: 0.1px solid black; height:6.5mm;" ><?=$i->Disciplina?></td>
						<td style="border: 0.1px solid black; "><p style="line-height: 6.2mm;" ><?=$i->Hours?></p></td>
						<td style="border: 0.1px solid black; "><p style="<?=($i->Mark=='Удовлетворительно' ? '' : 'line-height: 6.2mm;')?>" ><?=$i->Mark?></p></td>
						<td style="border: 0.1px solid black; "><p style="line-height: 6.2mm;" ><?=$i->Date?></p></td>
						<td style="border: 0.1px solid black; "></td>
						<td style="border: 0.1px solid black; height:5mm; overflow: hidden"><div style="overflow: hidden"><?=$i->Teacher?></div></td>
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
					<td colspan="2" style="text-align:left;" ><span>&nbsp;&nbsp;ДИСЦИПЛИНЫ</span></td>
					<td colspan="5" style="text-align:right; font-size: 3mm; line-height: 2mm; height:2mm;" >(Фамилия И.О. студента)&nbsp;&nbsp;</td>
				</tr>
				<tr>
					<td colspan="7" style="text-align:center; font-size: 4.5mm; line-height: 2mm; height:4mm;" >Результаты промежуточной аттестации (зачеты)</td>
				</tr>
				<tr style="text-align:center; font-size: 3mm; line-height: 2.7mm;">
					<td style="border: 0.1px solid black; height:8mm; width:6mm;" ><p style="font-size: 0.1mm; line-height: 1mm;">&nbsp;</p>№<br />п/п</td>
					<td style="border: 0.1px solid black; width:50mm;" ><p style="font-size: 0.1mm; line-height: 1mm;">&nbsp;</p>Наименование дисциплины<br />(модуля), раздела</td>
					<td style="border: 0.1px solid black; width:15mm;" ><p style="font-size: 0.1mm; line-height: 1mm;">&nbsp;</p>Общее кол-<br />во час./з.ед</td>
					<td style="border: 0.1px solid black; width:15mm;" ><p style="font-size: 0.1mm; line-height: 2mm;">&nbsp;</p>Оценка</td>
					<td style="border: 0.1px solid black; width:15mm;" ><p style="font-size: 0.1mm; line-height: 1mm;">&nbsp;</p>Дата сдачи<br />зачета</td>
					<td style="border: 0.1px solid black; width:15mm;" >Подпись<br />преподават<br />еля</td>
					<td style="border: 0.1px solid black; width:30.5mm;" ><p style="font-size: 0.1mm; line-height: 1mm;">&nbsp;</p>Фамилия<br />преподавателя</td>
				</tr>
				<?php $row_num = 1; ?>
				<?php foreach($this->facultative_credits as $i):?>
					<tr style="font-size: 3mm; line-height: 2.9mm;">
						<td style="border: 0.1px solid black; height:6.5mm;" ><p style="line-height: 6.2mm;" ><?=$row_num?></p></td>
						<td style="border: 0.1px solid black; height:6.5mm;" ><?=$i->Disciplina?></td>
						<td style="border: 0.1px solid black; "><p style="line-height: 6.2mm;" ><?=$i->Hours?></p></td>
						<td style="border: 0.1px solid black; "><p style="<?=($i->Mark=='Удовлетворительно' ? '' : 'line-height: 6.2mm;')?>" ><?=$i->Mark?></p></td>
						<td style="border: 0.1px solid black; "><p style="line-height: 6.2mm;" ><?=$i->Date?></p></td>
						<td style="border: 0.1px solid black; "></td>
						<td style="border: 0.1px solid black; height:6.2mm; overflow: hidden"><?=$i->Teacher?></td>
					</tr>
					<?php $row_num++; ?>
					<?php if($row_num > 10){ break; } ?>				
				<?php endforeach;?>
				
				
				<tr style="font-size: 4mm; text-align:left;">
					<td colspan="7" style="border: 0.1px solid black; height:8.5mm;" >
						<p style="font-size: 0.1mm; line-height: 1mm;">&nbsp;</p>
						<p style="line-height: 3mm;">
							&nbsp;&nbsp;Руководитель структурного подразделения <span style="text-decoration:underline"><?=$this->director_subdivision_fio_facultative?></span>
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
					<td colspan="7" style="text-align:right;" ><span>КУРСОВЫЕ&nbsp;&nbsp;</span></td>					
				</tr>				
				<tr style="text-align:center; font-size: 3mm; line-height: 2.7mm;">
					<td style="border: 0.1px solid black; height:12mm; width:6mm;" ><p style="font-size: 0.1mm; line-height: 3mm;">&nbsp;</p>№<br />п/п</td>
					<td colspan="3" style="border: 0.1px solid black; width:65mm;" ><p style="font-size: 0.1mm; line-height: 3mm;">&nbsp;</p>Наименование дисциплин (-ны)<br />(модуля)</td>
					<td colspan="3" style="border: 0.1px solid black; width:75.5mm;" ><p style="font-size: 0.1mm; line-height: 4.5mm;">&nbsp;</p>Тема курсовой работы (проекта)</td>					
				</tr>
				<?php $row_num = 1; ?>
				<?php foreach($this->course_work as $i):?>				
					<tr style="font-size: 3mm; line-height: 2.7mm;">
						<td style="border: 0.1px solid black; height:6.5mm;" ><p style="line-height: 6.2mm;" ><?=$row_num?></p></td>
						<td colspan="3" style="border: 0.1px solid black; height:6.5mm;" ><?=$i->Disciplina?></td>
						<td colspan="3" style="border: 0.1px solid black; "><p style="line-height: 6.2mm;" ></p></td>												
					</tr>
					<?php $row_num++; ?>
					<?php if($row_num > 10){ break; } ?>
				<?php endforeach;?>	
				
				<?php if($this->next_course): ?>
				<tr style="font-size: 4mm; text-align:left;">
					<td colspan="7" style="border: 0.1px solid black; height:8.5mm;" >
						<p style="font-size: 0.1mm; line-height: 1mm;">&nbsp;</p>
						<p style="line-height: 3mm;">
							&nbsp;Студент <span style="text-decoration:underline"><?=$this->fio;?></span> переведен на <span style="text-decoration:underline"><?=$this->next_course;?></span> курс					
						</p>
					</td>
				</tr>
				<?php endif;?>
			
				
				
			</table>
		</td>
		<td style="border: 0.1px solid black; height:9.5cm;" cellpadding="0" >
			<table cellpadding="0" style="text-align:center; width:100%">
				<tr style="text-align:left;" >
					<td colspan="2" >&nbsp;</td>
					<td colspan="5" style="text-align:right; font-size: 3.5mm;" ><span style="text-decoration:underline;" ><?=$this->fio;?></span>&nbsp;&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2" style="text-align:left;" ><span>&nbsp;&nbsp;РАБОТЫ (ПРОЕКТЫ)</span></td>
					<td colspan="5" style="text-align:right; font-size: 3mm; line-height: 2mm; height:2mm;" >(Фамилия И.О. студента)&nbsp;&nbsp;</td>
				</tr>				
				<tr style="text-align:center; font-size: 3mm; line-height: 2.7mm;">
					<td style="border: 0.1px solid black; height:12mm; width:15mm;" ><p style="font-size: 0.1mm; line-height: 4mm;">&nbsp;</p>Семестр</td>
					<td style="border: 0.1px solid black; width:28mm;" ><p style="font-size: 0.1mm; line-height: 4mm;">&nbsp;</p>Оценка</td>
					<td style="border: 0.1px solid black; width:17mm;" ><p style="font-size: 0.1mm; line-height: 4mm;">&nbsp;</p>Дата сдачи</td>
					<td style="border: 0.1px solid black; width:25mm;" ><p style="font-size: 0.1mm; line-height: 2.5mm;">&nbsp;</p>Подпись<br />преподавателя</td>
					<td colspan="3" style="border: 0.1px solid black; width:61.5mm;" ><p style="font-size: 0.1mm; line-height: 4mm;">&nbsp;</p>Фамилия преподавателя</td>
				</tr>
				<?php $row_num = 1; ?>
				<?php foreach($this->course_work as $i):?>
					<tr style="font-size: 3mm; line-height: 2.7mm;">
						<td style="border: 0.1px solid black; height:6.5mm;" ><p style="line-height: 6.2mm;" ><?=$i->Semester?></p></td>
						<td style="border: 0.1px solid black; "><p style="line-height: 6.2mm;" ><?=$i->Mark?></p></td>
						<td style="border: 0.1px solid black; "><p style="line-height: 6.2mm;" ><?=$i->Date?></p></td>
						<td style="border: 0.1px solid black; "></td>
						<td colspan="3" style="border: 0.1px solid black; height:6.2mm; overflow: hidden"><?=$i->Teacher?></td>
					</tr>
					<?php $row_num++; ?>
					<?php if($row_num > 10){ break; } ?>				
				<?php endforeach;?>	

				<tr style="font-size: 4mm; text-align:left;">
					<td colspan="7" style="border: 0.1px solid black; height:8.5mm;" >
						<p style="font-size: 0.1mm; line-height: 1mm;">&nbsp;</p>
						<p style="line-height: 3mm;">
							&nbsp;&nbsp;Руководитель структурного подразделения <span style="text-decoration:underline"><?=$this->director_subdivision_fio_course_work?></span>
						</p>					
					</td>
				</tr>
				
				
				
			</table>
		</td>
	</tr>	
</table>