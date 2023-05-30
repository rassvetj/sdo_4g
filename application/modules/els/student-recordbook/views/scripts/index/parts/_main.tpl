<div class="recordbook-area">
	<!-- левая сторона -->
	<div class="rb-page">		
		<table class="rb-page-content">
			<tr class="rb-bold rb-center">
				<td class="rb-photo">
				<?php if($this->photo != '') : ?>
					<img src="<?=$this->baseUrl($this->photo);?>" />
				<?php else : ?>	
					Место для<br>фотокарточки
				<?php endif;?>
				
				</td>	
				<td colspan="2">&nbsp;</td>					
			</tr>			
			<tr class="rb-bold rb-center">
				<td class="rb-photo" style="position: relative;">
					<!-- м.п. -->
					<?/*
					<img src="/images/student-recordbook/stamps/demo.png" style="position: absolute; max-width: 150px; max-height: 150px; left: 30px; top: -50px;">	
					*/?>					
				</td>
				<td colspan="2">&nbsp;</td>					
			</tr>
			<tr class="rb-text-right">			
				<td colspan="3">
					<div class="rb-bg-line" style="width: 400px; height: 20px; overflow: hidden; margin-bottom: -5px; float: right; text-align: left;">
						<span class="rb-bg-none-line">Подпись студента (курсанта)</span>
						<!--[Подпись студента (курсанта)]-->
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;					
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;							
					</div>	
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>	
				<td class="rb-text-right rb-w400" style="width: 398px;" colspan="2">
					<div class="rb-bg-line" style="width: 295px; height: 20px; overflow: hidden; margin-bottom: -5px;   float: right;">												
						<?=$this->dateTake;?>
					</div>					
				</td>											
			</tr>
			<tr class="rb-text-right">
				<td>&nbsp;</td>					
				<td class="rb-text-under">(дата выдачи зачетной книжки)</td>
				<td>&nbsp;</td>
			</tr>
		</table>
		<div class="rb-footer"><?=$this->currentPage;?></div>
		<?php $this->currentPage++;?>
	</div>	
	<!-- правая сторона -->
	<div class="rb-page">		
		<table class="rb-page-content">
			<tr>
				<td colspan="2"><div class="rb-mar-r rb-center rb-bg-line"><?=$this->institute_name;?></div></td>				
			</tr>			
			<tr>
				<td colspan="2" class="rb-text-under rb-center">(наименование образовательной организации)</td>				
			</tr>
			<tr class="rb-center rb-caption">
				<td colspan="2">
					<div class="rb-center" style="width:330px;">
						ЗАЧЕТНАЯ КНИЖКА №
						<div class="rb-mar-r rb-center rb-float-r rb-bg-line" style="width:100px;">							
							<?=$this->recordbook_number;?>
						</div>
					</div>
				</td>				
			</tr>			
			<tr class="rb-center">
				<td colspan="2">
					<div class="rb-mar-r rb-bg-line" style="margin-bottom: -5px; height: 20px; overflow: hidden;">
						<?=$this->fio;?>
					</div>
				</td>				
			</tr>			
			<tr>
				<td colspan="2" class="rb-text-under rb-center">(фамилия, имя, отчество (последнее - при наличии) студента (курсанта))</td>				
			</tr>
			<tr>
				<td colspan="2" class="rb-pt10">
					<div class="rb-bg-line rb-mar-r" style="height: 56px; overflow: hidden;">
						<span class="rb-bg-none-line">Код, направление подготовки (специальность)</span>						
						<?=$this->speciality;?>
						<?php if($this->specialization && $this->specialization != '') : ?>
							(<?=$this->specialization;?>)
						<?php endif; ?>
					</div>				
				</td>				
			</tr>			
			<tr>
				<td colspan="2">
					<div class="rb-bg-line rb-mar-r" style="height: 20px; overflow: hidden;">
						<span class="rb-bg-none-line">Структурное подразделение</span>												
						<?=$this->faculty;?>
					</div>	
				</td>				
			</tr>
			<tr>
				<td colspan="2">
					<div class="rb-bg-line rb-mar-r" style="height: 20px; overflow: hidden;">
						<span class="rb-bg-none-line">Зачислен приказом </span>
						<!--[название приказа]	-->										
						<?=$this->orderName;?>
						<span class="rb-bg-none-line"> от </span>						
						<?=$this->dateTake;?>										
						<span class="rb-bg-none-line">&nbsp;№ </span>
						<!--[номер приказа]-->						
						<?=$this->code;?>
					</div>
				</td>				
			</tr>
			<tr>
				<td colspan="2">
					<div class="rb-bg-line rb-mar-r" style="height: 20px; overflow: hidden; margin-bottom: -5px;">
						<span class="rb-bg-none-line">Проректор</span>
						<!--[ФИО проректора]	-->
						<span style="position:relative;">
							<!-- м.п. -->
							<?/*
							<img src="/images/student-recordbook/signs/demo.png" style="position: absolute; max-width: 150px; max-height: 35px; left: 70px; bottom: -5px;">
							*/?>
						</span >
					</div>				
				</td>				
			</tr>
			<tr>
				<td class="rb-text-under rb-center" style="position:relative;">
					<!-- м.п. -->
					<?/*
					<img src="/images/student-recordbook/stamps/demo.png" style="position: absolute; max-width: 150px; max-height: 150px; left: 100px; top: -60px;">					
					*/?>
				</td>
				<td class="rb-text-under">(подпись, Фамилия И.О.)</td>				
			</tr>
			<tr>
				<td  class="rb-w290">
					Руководитель структурного подразделения				
				</td>				
				<td>
					<div class="rb-bg-line rb-mar-r" style="height: 20px; margin-bottom: -5px;">
						<!--[ФИО руководителя]-->
						<?=$this->structural_supervisor?>
					</div>				
				</td>				
			</tr>
			<tr>
				<td>&nbsp;</td>				
				<td class="rb-text-under">(подпись, Фамилия И.О.)</td>				
			</tr>
			<tr>
				<td colspan="2" class="rb-center">&nbsp;</td>				
			</tr>
		</table>
		<div class="rb-footer"><?=$this->currentPage;?></div>
		<?php $this->currentPage++;?>		
	</div>	
</div>	