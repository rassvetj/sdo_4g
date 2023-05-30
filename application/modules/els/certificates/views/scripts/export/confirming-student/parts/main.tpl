<table style="font-size:11px; font-family:'museosanscyrl300'; text-align:justify; ">
	<tr>
		<td colspan="2">
		
			<!-- -->
			<table style="font-size:11px; font-family:'museosanscyrl100'; ">
				<tr>
					<td style="width:110px;">&nbsp;</td>
					<td style="width:400px; font-size:10px;">
						<br />
						<br />
						Министерство науки и высшего образования Российской Федерации
						<br />
						<span style="font-family:'museosanscyrl300';">Федеральное государственное бюджетное образовательное учреждение</span>
						<br />
						<span style="font-family:'museosanscyrl300';">высшего образования</span>
						<br />
						<span style="font-family:'museosanscyrl300';">«РОССИЙСКИЙ ГОСУДАРСТВЕННЫЙ СОЦИАЛЬНЫЙ УНИВЕРСИТЕТ» (РГСУ)</span>
						<br />
						Лицензия серия 90Л01 № 0009072
						<br />
						Регистрационный № 2017 от 21.03.2016
						<br />
						Срок действия лицензии бессрочно
						<br />
						Свидетельство государственной аккредитации
						<br />
						oт 31.10.2018 № 2929 серия 90А01 № 0003074
						<br />
						129226, г. Москва, ул. Вильгельма Пика, дом № 4, корпус 1
						<br />
						тел.: +7 (495) 255-67-67
					</td> 
				</tr>
				<tr>
					<td colspan="2" style="text-align:center; font-family:'museosanscyrl500'; font-size:25px; ">
						<br />			
						<br />
						СПРАВКА
					</td>		
				</tr>
				<tr style="line-height:20px;">
					<td style="text-align:left;">
						№ <?=$this->order_numbr?>
					</td>
					<td style="text-align:right;">
						от <?=$this->order_date 
								? $this->order_date 
								: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<br />
						<br />			
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Дана <span style="font-family:'museosanscyrl300';"><?=$this->fio_dative?></span>, <?=$this->date_birth?> г.р., в том, 
						что он(а) действительно является студентом РГСУ по основной профессиональной образовательной программе 
						на <?=$this->current_course?> 
						курсе <?=$this->study_form?> формы обучения <?=$this->based?>
						по направлению подготовки «<?=$this->direction?>»<?=empty($this->direction_code) ? '' : ' - '.$this->direction_code?>.
					</td>
				</tr>
				
				<?php if(!empty($this->orders)):?>
				<tr>
					<td colspan="2">
						<p style="line-height:5px;">&nbsp;</p>
					</td>
				</tr>				
				<tr>
					<td colspan="2">
						<?php foreach($this->orders as $order):?>
							&nbsp;&nbsp;&nbsp;&nbsp; &bull; &nbsp;
							<?=$order?>
							<br />			
						<?php endforeach;?>
					</td>
				</tr>
				<?php endif;?>
				
				<tr>
					<td colspan="2">
						<p style="line-height:5px;">&nbsp;</p>
					</td>
				</tr>
				<tr>
					<td colspan="2">			
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Срок окончания при успешном завершении обучения с учетом каникулярного времени <?=empty($this->date_graduation) ? '' : $this->date_graduation?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<p style="line-height:5px;">&nbsp;</p>
					</td>
				</tr>
				<tr>
					<td colspan="2">			
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;В соответствии с приказом РГСУ № 1630 от 11.09.2019 «Об утверждении Положения о студенческом билете и зачетной книжке обучающегося в федеральном государственном бюджетном 
						образовательном учреждении высшего образования «Российскийгосударственный социальный университет»:
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<p style="line-height:5px;">&nbsp;</p>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Студенческий билет – это электронный документ установленной формы, выдаваемый посредством размещения в личном кабинете обучающегося в 
						электронной информационно-образовательной среде РГСУ, удостоверяющий, что данное лицо является обучающимся Университета.
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<p style="line-height:5px;">&nbsp;</p>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Студенческие билеты на бумажном носителе не используются в РГСУ с 01.09.2019.
						</td>
				</tr>
				<tr>
					<td colspan="2">
						<p style="line-height:15px;">&nbsp;</p>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<div style="position:relative;line-height:0;">
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Справка выдана для предоставления по месту требования.
						</div>
						<span>
						<?php if($this->stamp_and_sign_url):?>							
							&nbsp;&nbsp;
							<img src="<?=$this->stamp_and_sign_url?>" height="125" width="475" >
						<?php endif; ?>
						</span>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<p style="line-height:15px;">&nbsp;</p>
					</td>
				</tr>
				<tr  >
					<td colspan="2" >
							
					</td>
				</tr>
			</table>
			<!-- -->
		</td>
	</tr>	
</table>