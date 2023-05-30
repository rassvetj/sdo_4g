<html >   
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <head>
		<style>
			body {
				font-size:5px;
			}
			table {
				font-size:7px;
				color: #1f1f1f;
				font-family: 'museosanscyrl700';
				line-height:8px; 
				
			}
			
			.under-text {
				font-size:6px;
				color: #6a6a68;
				font-family: 'museosanscyrl300';
			}
			
			.under-text-2 {
				font-size:8px;
				color: #6a6a68;
				font-family: 'museosanscyrl300';
			}
			
			.under-text-2-value{
				font-family: 'museosanscyrl700';
				color:black;
				line-height:12px; 
			}
			
			.fio {
				font-size:20px;
				color: #003252;
			}
		</style>
    </head>
    <body>
	
		<div style="line-height:150px;">&nbsp;</div>
		<table><tr><td>
		
			<table >			
				<tr>
					<td style="width:110px;">&nbsp;</td><td colspan="2" style="width:500px;"><b>Министерство науки и высшего образования Российской Федерации</b></td>
				</tr>
				<tr>
					<td>&nbsp;</td><td  colspan="2" class="under-text">(учредитель)</td>
				</tr>
				<tr>
					<td>&nbsp;</td><td colspan="2" >Федеральное государственное бюджетное образовательное учреждение</td>
				</tr>
				<tr>
					<td>&nbsp;</td><td colspan="2" >высшего образования &laquo;Российский Государственный Социальный Университет&raquo;</td>
				</tr>
				<tr>
					<td>&nbsp;</td><td colspan="2" class="under-text">(полное наименование организации, осуществляющей государственную деятельность)</td>
				</tr>
				<tr>
					<td>&nbsp;</td><td colspan="2"><span style="font-size:17px; text-transform: uppercase; color: #6a6a68; font-family:'museosanscyrl300'; line-height:33px; ">Студенческий билет № <span style="color:black; font-family:'museosanscyrl500';"><?=$this->number?></span></span></td>
				</tr>
				<tr>
					<td>&nbsp;</td><td colspan="2" class="fio"><?=$this->last_name?></td>
				</tr>
				<tr>
					<td>&nbsp;</td><td colspan="2" class="under-text-2">(фамилия)</td>
				</tr>
				<tr>
					<td>&nbsp;</td><td colspan="2" class="fio"><?=$this->first_name?> <?=$this->patronymic?></td>
				</tr>
				<tr>
					<td>&nbsp;</td><td colspan="2" class="under-text-2">(имя, отчество (последнее - при наличии))</td>
				</tr>
				<tr>
					<td>&nbsp;</td><td colspan="2" style="line-height:10px;">&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td><td colspan="2" class="under-text-2">Форма обучения: <span class="under-text-2-value"><?=$this->form_study?></span></td>
				</tr>
				<tr>
					<td>&nbsp;</td><td colspan="2" class="under-text-2">Зачислен приказом от: <span class="under-text-2-value"><?=$this->date_from ? $this->date_from : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'?></span> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;№ <span class="under-text-2-value"><?=$this->code?></span></td>
				</tr>
				<tr>
					<td>&nbsp;</td><td colspan="2" class="under-text-2">Дата выдачи: <span class="under-text-2-value"><?=$this->date_take?></span></td>
				</tr>
				<tr>
					<td>&nbsp;</td><td colspan="2" style="line-height:15px;">&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td><td style="width:50px">&nbsp;</td><td class="under-text-2"><span class="under-text-2-value"><?=$this->director_organization_fio?></span></td>
				</tr>
				<tr>
					<td>&nbsp;</td><td style="width:50px" class="under-text-2">(подпись)</td><td class="under-text-2">(фамилия, имя, отчество (последнее - при наличии))</td>
				</tr>
				<tr>
					<td><span style="text-decoration:underline; color:#dfdfdf; font-family: 'museosanscyrl500'; text-align:right;">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					</span></td>
					<td colspan="2">Руководитель организации, осуществляющей образовательную деятельность</td>
				</tr>
				<tr>
					<td><span class="under-text" style="text-align:center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(подпись студента)</span></td><td colspan="2">или иное уполномоченное им лицо</td>
				</tr>
			</table>
		
		</td><td style=".border-left: 1px solid #dfdfdf; ">
		
			<table>
				<?php foreach($this->years as $i):?>
					<tr>
						<td style="width:30px;">&nbsp;</td><td colspan="3" class="under-text-2-value" style="line-height:7px;">Действителен по <?=$this->date_valid[$i['year']]?></td>
					</tr>					
					<tr>
						<td >&nbsp;</td>
						<td colspan="1" class="under-text-2" style="width:70px;" >Декан/директор</td>
						<td style="width:150px;">
							<span style="text-decoration:underline; color:#dfdfdf; font-family: 'museosanscyrl500'; text-align:center; line-height:12px;">
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							</span>
						</td>
						<td class="under-text-2-value" style="width:150px;" ><?=$i['director_subdivision_fio']?></td>
					</tr>
					<tr>
						<td >&nbsp;</td>
						<td style="text-align:center;">М.П.</td>
						<td style=" text-align:center;" class="under-text">(подпись)</td>
						<td style="" class="under-text" >(фамилия, имя, отчество</td>
					</tr>
					<tr>
						<td colspan="3" >&nbsp;</td>					
						<td class="under-text" style="line-height:4px;" >(последнее - при наличии))</td>
					</tr>
				<?php endforeach;?>
			</table>
			
		</td></tr></table>
		
    </body>
</html>