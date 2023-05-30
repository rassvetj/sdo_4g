<html >   
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <head>
		<style>
			body {
				font-size:5px;
			}
			table {
				font-size:10px;
				color: #1f1f1f;
				font-family: 'museosanscyrl100';
				line-height:16px; 
				
			}
		</style>
    </head>
    <body>
		<table>
			<tr style="text-align: center; line-height:20px;" >
				<td colspan="2" >&nbsp;</td>
			</tr>
			<tr><td>&nbsp;</td><td style="font-family: 'museosanscyrl700';">Ректору РГСУ</td></tr>
			<tr><td>&nbsp;</td><td style="font-family: 'museosanscyrl700';">Починок Н.Б.</td></tr>
			<tr><td>&nbsp;</td><td>От <?=$this->fio_genitive?></td></tr>
			<tr><td>&nbsp;</td><td>Контактный телефон: <?=$this->phone?></td></tr>
			<tr><td>&nbsp;</td><td>e-mail: <?=$this->email?></td></tr>
			<tr style="text-align: center; line-height:20px;" >
				<td colspan="2" >&nbsp;</td>
			</tr>
			<tr style="text-align: center; line-height:25px; font-family: 'museosanscyrl700';" >
				<td colspan="2" >ЗАЯВЛЕНИЕ</td>
			</tr>
			<tr>
				<td colspan="2" style="font-family: 'museosanscyrl700'; text-indent: 20px;" >
					Прошу Вас перевести меня 
					<?=$this->transfer_type?>					
				</td>
			</tr>			
			<tr>
				<td colspan="2" >
					на <?=$this->course?> курс 
					по направлению подготовки «<?=$this->direction_desired?>»</td>
			</tr>
			
			<tr>
				<td colspan="2" >
					<span  style="font-family: 'museosanscyrl700';" ><?=_('на базе')?>:</span>
					<?php foreach($this->educationTypes as $id => $name):?>
						<?=$name?> 
						<img src="<?=($this->education_type == $id ? $this->url_checkbox_checked : $this->url_checkbox_empty)?>" width="10" height="10">
						&nbsp;&nbsp;
					<?php endforeach;?>
				</td>
			</tr>
			
			<tr>
				<td colspan="2" >
					<span  style="font-family: 'museosanscyrl700';" ><?=_('по программе обучения')?>:</span>
					<?php foreach($this->programs as $id => $name):?>
						<?=$name?> 
						<img src="<?=($this->program == $id ? $this->url_checkbox_checked : $this->url_checkbox_empty)?>" width="10" height="10">
						&nbsp;&nbsp;
					<?php endforeach;?>
				</td>
			</tr>
			
			<tr>
				<td colspan="2" >
					<span  style="font-family: 'museosanscyrl700';" ><?=_('форма  обучения')?>:</span>
					<?php foreach($this->studyForms as $id => $name):?>
						<?=$name?> 
						<img src="<?=($this->study_form == $id ? $this->url_checkbox_checked : $this->url_checkbox_empty)?>" width="10" height="10">
						&nbsp;&nbsp;
					<?php endforeach;?>
				</td>
			</tr>
			<tr>
				<td colspan="2" >
					<span  style="font-family: 'museosanscyrl700';" ><?=_('на места')?>:</span>
					<?=_('за счет бюджетных ассигнований федерального бюджета')?>
					<img src="<?=($this->basis_learning == HM_StudentCertificate_StudentCertificateModel::BASIS_LEARNING_BUDGET ? $this->url_checkbox_checked : $this->url_checkbox_empty)?>" width="10" height="10">
				</td>
			</tr>
			<tr>
				<td colspan="2" >
					<?=_('на места по договорам об оказании платных образовательных услуг')?>
					<img src="<?=($this->basis_learning == HM_StudentCertificate_StudentCertificateModel::BASIS_LEARNING_CONTRACT ? $this->url_checkbox_checked : $this->url_checkbox_empty)?>" width="10" height="10">
				</td>
			</tr>
			<tr style="text-align: center; line-height:20px;" >
				<td colspan="2" >&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2" >
					<span  style="font-family: 'museosanscyrl700';" ><?=_('Вид программы обучения')?>:</span>
					
					<?=_('нормативный срок')?>
					<img src="<?=$this->url_checkbox_checked?>" width="10" height="10">
					&nbsp;&nbsp;
					
					<?=_('ускоренное обучение')?>
					<img src="<?=$this->url_checkbox_empty?>" width="10" height="10">
					&nbsp;&nbsp;
				</td>
			</tr>
			<tr style="text-align: center; line-height:70px;" >
				<td colspan="2" >&nbsp;</td>
			</tr>
			<tr>
				<td>«<?=$this->day?>» <?=$this->month?> <?=$this->year?> г.</td>
				<td style="text-align:right;">
					<span style="text-decoration:underline;">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					</span>
					/<?=$this->fio?>/
				</td>
			</tr>
			<tr style="line-height:5px;">
				<td>&nbsp;</td>
				<td style="text-align:center; font-size:6px;">
					подпись
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					(Ф.И.О.)
				</td>
			</tr>
			<tr style="text-align: center; line-height:30px;" >
				<td colspan="2" >&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2" >
					<span  style="font-family: 'museosanscyrl700';" ><?=_('Заключение руководителя центра сопровождения образовательной деятельности')?></span>
				</td>
			</tr>
			<tr style="text-align: center; line-height:20px;" >
				<td colspan="2" >&nbsp;</td>
			</tr>
			<tr>
				<td>
					«<span style="text-decoration:underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>»
					&nbsp;
					<span style="text-decoration:underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
					&nbsp;
					20<span style="text-decoration:underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;г.
				</td>
				<td style="text-align:right;">
					<span style="text-decoration:underline;">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					</span>
					/&nbsp;<span style="text-decoration:underline;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;						
					</span>
				</td>
			</tr>
			<tr style="line-height:5px;">
				<td>&nbsp;</td>
				<td style="text-align:center; font-size:6px;">
					подпись
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					(Ф.И.О.)
				</td>
			</tr>
			
			

			
		</table>
    </body>
</html>



