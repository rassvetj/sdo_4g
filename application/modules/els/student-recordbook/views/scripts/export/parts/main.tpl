<table style="font-size:0.4cm;">
	<tr>
		<td style="text-align: left;">&nbsp;</td>
		<td style="text-align: right;">&nbsp;</td>
	</tr>
	<!-- Обложка -->
	<tr>
		<td style="border: 0.1px solid black; height:9.5cm;">
			&nbsp;
		</td>
		<td style="border: 0.1px solid black;">
			<p style="line-height: 3mm; font-size:0.5mm">&nbsp;</p>
			<img src="<?=$this->cover_main?>" style="height:9.5cm; width:16cm; padding-top: 5px; margin:5px" >
		</td>
	</tr>
	<tr>
		<td style="text-align: left;">&nbsp;</td>
		<td style="text-align: right;">&nbsp;</td>
	</tr>
	
	<!-- Первая страница -->
	<tr>
		<!-- Левая -->
		<td style="border: 0.1px solid black; height:9.5cm;" >
			<table>
				<tr>
					<td style="width:4cm; height:4cm; text-align: center;" >
						<p style="line-height: 1cm; font-size:0.5mm">&nbsp;</p>
						<?=($this->has_user_photo ? '' : 'Место для<br />фотокарточки')?>
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td style="height:1cm; text-align: right;" ><?=($this->has_stamp_dean_office ? '' : 'М.П.')?></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td style="width:10cm; font-size:4.7mm">
						<p style="line-height: 1cm; font-size:0.5mm">&nbsp;</p>
						Подпись студента________________________
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td style="width:10cm; font-size:4.7mm">						
						<span style="text-decoration: underline;" ><?=$this->date_take?></span>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td style="width:10cm; font-size:3.7mm">						
						&nbsp;<!--(дата выдачи зачетной книжки)-->
					</td>
				</tr>
			</table>		
		</td>
		
		<!-- Правая -->
		<td style="border: 0.1px solid black;">
			<table style="font-size:3.7mm; line-height: 4mm;">
				<tr><td style="text-decoration: underline; text-align: center;">Министерство науки и высшего образования Российской Федерации</td></tr>
				<tr><td style="text-align: center;">(учредитель)</td></tr>
				<tr><td style="text-decoration: underline; text-align: center;">Федеральное государственное бюджетное образовательное учреждение высшего</td></tr>
				<tr><td style="text-decoration: underline; text-align: center;">образования «Российский государственный социальный университет»</td></tr>
				<tr><td style="text-align: center;">(полное наименование организации, осуществляющей образовательную деятельность)</td></tr>
				<tr><td style="text-align: center; font-size:4.5mm">ЗАЧЕТНАЯ КНИЖКА № <span style="text-decoration: underline;" ><?=$this->number?></span></td></tr>
				<tr><td style="text-decoration: underline; text-align: left;"><?=$this->fio?></td></tr>
				<tr><td style="text-align: center;">(фамилия, имя, отчество (последнее – при наличии) студента)</td></tr>
				<tr><td>Код, направление подготовки (специальность) <span style="text-decoration: underline;" ><?=$this->speciality?></span></td></tr>
				<tr><td><span style="text-decoration: underline;" ><?=$this->specialization?></span></td></tr>
				<tr><td>Структурное подразделение <span style="text-decoration: underline;" ><?=$this->faculty?></span></td></tr>
				<tr><td>
					Зачислен приказом 
					<span style="text-decoration: underline;" ><?=$this->order_name?></span>
					от <span style="text-decoration: underline;" ><?=$this->date_take?></span>
					№<span style="text-decoration: underline;" ><?=$this->order_number?></span>
				</td></tr>
				<tr><td>Руководитель</td></tr>
				<tr><td>организации,&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;__________________&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="text-decoration: underline;" ><?=$this->director_organization_fio?></span></td></tr>
				<tr><td>осуществляющей&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(подпись)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(фамилия, имя, отчество</td></tr>
				<tr><td>образовательную&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(последнее – при наличии))</td></tr>
				<tr><td>деятельность,</td></tr>
				<tr><td>или иное</td></tr>
				<tr><td>уполномоченное им</td></tr>
				<tr><td>должностное лицо</td></tr>
				<tr><td>Руководитель</td></tr>
				<tr><td>структурного подразделения <span style="text-decoration: underline;" ><?=$this->director_subdivision_fio?></span></td></tr>
				<tr><td style="text-align: right;">(подпись, фамилия, имя, отчество (последнее – при наличии))</td></tr>
			</table>
		</td>
	</tr>
</table>