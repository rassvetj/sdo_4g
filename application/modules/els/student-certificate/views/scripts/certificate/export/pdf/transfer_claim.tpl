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
					на <?=$this->transfer_type?>
					в <?=$this->organization?>
				</td>
			</tr>			
			<tr>
				<td colspan="2" >
					на <?=$this->course?> курс 
					по направлению подготовки «<?=$this->direction?>»</td>
			</tr>
			<tr>
				<td colspan="2" >
					<span  style="font-family: 'museosanscyrl700';" >по программе обучения:</span>
					бакалавриат  <img src="<?=($this->program == 'бакалавриат'  ? $this->url_checkbox_checked : $this->url_checkbox_empty)?>" width="10" height="10">&nbsp;&nbsp;
					специалитет  <img src="<?=($this->program == 'специалитет'  ? $this->url_checkbox_checked : $this->url_checkbox_empty)?>" width="10" height="10">&nbsp;&nbsp;
					магистратура <img src="<?=($this->program == 'магистратура' ? $this->url_checkbox_checked : $this->url_checkbox_empty)?>" width="10" height="10">&nbsp;&nbsp;
					среднее профессионально образование <img src="<?=($this->program == 'среднее профессионально образование' ? $this->url_checkbox_checked : $this->url_checkbox_empty)?>" width="10" height="10">&nbsp;&nbsp;
				</td>
			</tr>
			<tr>
				<td colspan="2" >
					<span  style="font-family: 'museosanscyrl700';" >форма обучения:</span>
					очная                                          <img src="<?=($this->study_form == 'очная'                                          ? $this->url_checkbox_checked : $this->url_checkbox_empty)?>" width="10" height="10">&nbsp;&nbsp;
					очно-заочная                                   <img src="<?=($this->study_form == 'очно-заочная'                                   ? $this->url_checkbox_checked : $this->url_checkbox_empty)?>" width="10" height="10">&nbsp;&nbsp;
					заочная                                        <img src="<?=($this->study_form == 'заочная'                                        ? $this->url_checkbox_checked : $this->url_checkbox_empty)?>" width="10" height="10">&nbsp;&nbsp;
					заочная с применением дистанционных технологий <img src="<?=($this->study_form == 'заочная с применением дистанционных технологий' ? $this->url_checkbox_checked : $this->url_checkbox_empty)?>" width="10" height="10">&nbsp;&nbsp;
				</td>
			</tr>
			<tr>
				<td colspan="2" >
					<span  style="font-family: 'museosanscyrl700';" >на места:</span>
					за счет бюджетных ассигнований федерального бюджета
					<img src="<?=($this->basis_learning == 'бюджетная' ? $this->url_checkbox_checked : $this->url_checkbox_empty)?>" width="10" height="10">
				</td>
			</tr>
			<tr>
				<td colspan="2" >
					на места по договорам об оказании платных образовательных услуг
					<img src="<?=($this->basis_learning == 'контрактная' ? $this->url_checkbox_checked : $this->url_checkbox_empty)?>" width="10" height="10">
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
		</table>
    </body>
</html>