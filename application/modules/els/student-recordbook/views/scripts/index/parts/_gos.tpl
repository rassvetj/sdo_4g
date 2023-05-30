<div class="recordbook-area">
	<!-- левая сторона -->
	<div class="rb-page rb-page-2 rb-pad-reset rb-border-reset">			
		<table class="rb-page-content rb-tbl-border rb-text-under rb-center">
			<tr>
				<td colspan="3" class="rb-bold rb-text-right rb-border-b-hide rb-pad-r-3">ГОСУДАРСТВЕННЫЕ</td>
			</tr>
			<tr>
				<td colspan="3">&nbsp;</td>
			</tr>			
			<tr class="rb-tbl-caption-big">
				<td class="rb-c-mini">№<br>п/п</td>
				<td class="rb-c-big">Наименование дисциплины (модулей)</td>				
				<td class="rb-c-small">Дата сдачи экзамена</td>				
			</tr>
			
			<?php $count = 0; ?>
			<?php if(!empty($this->gos)) : ?>
				<?php foreach($this->gos as $g) : ?>
					<tr class="rb-row-gos">								
						<td>&nbsp;</td>			
						<td style="vertical-align: middle;" class="rb-text-left"><div class="rb-hide-out"><?=$g['name'];?></div></td>				
						<td style="vertical-align: middle;">
						<?php
							$timestamp = strtotime($g['date_exam']);
							if($timestamp > 0){
								echo date('d.m.Y', $timestamp);
							}
						?>
						</td>
					</tr>		
					<?php $count++; ?>
					<?php if($count == 10) { break; } ?>				
				<?php endforeach; ?>
			<?php endif; ?>
			
			<?php $i = 0; ?>
			<?php while($i < (5-count($this->gos)) ) : /* Дорисовываем недостающие строки. Их должно быть 10 всего.*/ ?>
				<tr class="rb-row-gos">
					<td>&nbsp;</td>				
					<td>&nbsp;</td>				
					<td>&nbsp;</td>				
				</tr>		
				<?php $i++; ?>
			<?php endwhile; ?>
			
			
			
			
			<tr class="rb-row-def">
				<td colspan="3" class="rb-center rb-border-b-hide rb-pad-r-3 rb-valign-b">
					<div class="rb-bg-line" style="height: 10px; overflow: hidden; background-position: 0px -5px; width:90%; float: right;">
						<span class="rb-bg-none-line" style="float: left;">Студент (курсант) </span>						
						<?php if($this->giaDateTake) : ?><?php /* Если есть дата приказа, значит допущен */ ?>
							<?=$this->fio;?>
						<?php endif; ?>						
						<span class="rb-bg-none-line" style="float: right;"> допущен к государственной итоговой </span>						
					</div>					
				</td>								
			</tr>				
			<tr>
				<td colspan="2" class="rb-border-b-hide rb-pad-r-3 rb-border-r-hide">(Фамилия И.О.)</td>								
				<td class="rb-text-right rb-border-b-hide rb-pad-r-3">Руководитель</td>								
			</tr>		
			<tr class="rb-row-gos">
				<td colspan="3" class="rb-valign-m"><?=$this->currentPage;?></td>								
				<?php $this->currentPage++;?>							
			</tr>		
		</table>
	</div>
	<!-- правая сторона -->
	<div class="rb-page rb-page-2 rb-pad-reset rb-border-reset">			
		<table class="rb-page-content rb-tbl-border rb-text-under rb-center">
			<tr>				
				<td class="rb-bold rb-text-left rb-pad-l-3 rb-border-r-hide rb-border-b-hide">ЭКЗАМЕНЫ</td>
				<td class="rb-text-right rb-pad-r-3 rb-border-b-hide">
					<div class="rb-center rb-float-r rb-bg-line" style="min-width:60%; background-position: 0px -4px; margin-bottom: -3px; overflow: hidden; line-height: 14px; height: 13px;">
						<?=$this->fio;?>
					</div>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="rb-text-right rb-pad-r-3 ">(Фамилия И.О. студента (курсанта))</td>
			</tr>			
			<tr class="rb-tbl-caption-big">
				<td class="rb-c-small">Оценка</td>
				<td>Подписи представителя и членов<br>Государственной экзаменационной комиссии</td>				
			</tr>
			<?php $count = 0; ?>
			<?php if(!empty($this->gos)) : ?>
				<?php foreach($this->gos as $g) : ?>
					<tr class="rb-row-gos">								
						<td style="vertical-align: middle;"><?=$g['ball'];?></td>								
						<td>&nbsp;</td>								
					</tr>	
					<?php $count++; ?>
					<?php if($count == 10) { break; } ?>				
				<?php endforeach; ?>
			<?php endif; ?>
			
			<?php $i = 0; ?>
			<?php while($i < (5-count($this->gos)) ) : /* Дорисовываем недостающие строки. Их должно быть 10 всего.*/ ?>
				<tr class="rb-row-gos">
					<td>&nbsp;</td>				
					<td>&nbsp;</td>												
				</tr>		
				<?php $i++; ?>
			<?php endwhile; ?>
			
			
			<tr class="rb-row-def">
				<td colspan="2" class="rb-text-left rb-border-b-hide rb-pad-l-3 rb-valign-b rb-pad-r-3">
					<div class="rb-bg-line" style="height: 10px; overflow: hidden; background-position: 0px -5px;">
						<span class="rb-bg-none-line">аттестации.&nbsp;</span>
						<?=$this->giaName;?>					
						<div style="float:right;">
							<span class="rb-bg-none-line"> № </span>													
							<?=$this->giaCode;?>
							<span class="rb-bg-none-line"> от </span>													
							<?=$this->giaDateTake;?>							
						</div>
					</div>	
				</td>	
			</tr>		
			<tr>
				<td colspan="2" class="rb-text-left rb-border-b-hide rb-pad-l-3">
					<div class="rb-bg-line rb-mar-r" style="height: 10px; overflow: hidden; background-position: 0px -5px; width:80%">
						<span class="rb-bg-none-line">структурного подразделения </span>
						<span style="padding-left: 50px;"><?=$this->structural_supervisor?></span>
						<!--[подпись]-->					
						<span class="rb-bg-none-line" style="float: right;"> (подпись) </span>						
					</div>
				</td>
			</tr>		
			<tr class="rb-row-gos">
				<td colspan="2" class="rb-valign-m"><?=$this->currentPage;?></td>								
				<?php $this->currentPage++;?>								
			</tr>		
		</table>
	</div>
	
</div>	