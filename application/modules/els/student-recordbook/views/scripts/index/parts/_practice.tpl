<div class="recordbook-area">
	<!-- левая сторона -->
	<div class="rb-page rb-page-2 rb-pad-reset rb-border-reset">			
		<table class="rb-page-content rb-tbl-border rb-text-under rb-center">
			<tr>
				<td colspan="5" class="rb-text-left rb-border-b-hide rb-pad-l-3">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="5" class="rb-bold rb-text-right rb-pad-r-3">ПР</td>
			</tr>			
			<tr class="rb-tbl-caption-big">
				<td class="rb-c-big">Наименование практики</td>
				<td class="rb-c-small">Семестр</td>
				<td class="rb-c-normal">Место проведения<br>практики</td>
				<td class="rb-c2">В качестве кого<br>работал<br>(должность)</td>
				<td class="rb-c-normal">Ф.И.О. руководителя<br>практики от<br>предприятия<br>(организации, учреждения)</td>
						
			</tr>
			
			<?php $count = 0; ?>
			<?php if(!empty($this->p_data)) : ?>
				<?php foreach($this->p_data as $p) : ?>
					<tr class="rb-row-def">								
						<td class="rb-text-left"><div class="rb-hide-out"><?=$p->Disciplina;?></div></td>				
						<td><?=$p->Semester;?></td>																		
						<td><?=$p->Company;?></td>																									
						<td><?=$p->Position;?></td>																																
						<td><?=$p->Manager;?></td>																																					
					</tr>		
					<?php $count++; ?>
					<?php if($count == 10) { break; } ?>				
				<?php endforeach; ?>
			<?php endif; ?>	
			
			<?php $i = 0; ?>
			<?php while($i < (10-count($this->p_data)) ) : /* Дорисовываем недостающие строки. Их должно быть 10 всего.*/ ?>
				<tr class="rb-row-def">
					<td>&nbsp;</td>				
					<td>&nbsp;</td>				
					<td>&nbsp;</td>				
					<td>&nbsp;</td>				
					<td>&nbsp;</td>				
					
				</tr>		
				<?php $i++; ?>
			<?php endwhile; ?>
			
			
			<tr>
				<td colspan="5" class="rb-text-right rb-border-b-hide rb-pad-r-3">Руководитель</td>								
			</tr>		
			<tr>
				<td colspan="5"><?=$this->currentPage;?></td>								
				<?php $this->currentPage++;?>				
			</tr>		
		</table>
	</div>
	<!-- правая сторона -->
	<div class="rb-page rb-page-2 rb-pad-reset rb-border-reset">			
		<table class="rb-page-content rb-tbl-border rb-text-under rb-center">
			<tr>
				<td colspan="5" class="rb-text-right rb-border-b-hide rb-pad-r-3">
					<div class="rb-center rb-float-r rb-bg-line" style="min-width:50%; background-position: 0px -4px; margin-bottom: -3px; overflow: hidden; line-height: 14px; height: 13px;">
						<?=$this->fio;?>
					</div>
				</td>
			</tr>
			<tr>				
				<td colspan="2" class="rb-bold rb-text-left rb-pad-l-3 rb-border-r-hide">АКТИКА</td>
				<td colspan="3" class="rb-text-right rb-pad-r-3">(Фамилия И.О. студента (курсанта))</td>
			</tr>			
			<tr class="rb-tbl-caption-big">
				<td class="rb-c-small">Общее<br>кол-во<br>час./з.ед.</td>
				<td class="rb-c-medium">Ф.И.О. руководителя практики<br>от образовательной организации</td>
				<td class="rb-c-small">Оценка по<br>итогам<br>аттестации</td>
				<td class="rb-c-small">Дата<br>проведения<br>аттестации</td>
				<td class="rb-c-normal">Подпись и фамилия лица,<br>проводившего аттестацию</td>				
			</tr>
			
			
			
			<?php $count = 0; ?>
			<?php if(!empty($this->p_data)) : ?>
				<?php foreach($this->p_data as $p) : ?>
					<tr class="rb-row-def">													
						<td><?=$p->Hours;?></td>				
						<td><?=$p->Teacher;?></td>				
						<td><?=$p->Mark;?></td>				
						<td><?php						
							$timestamp = strtotime($p->Date);
							if($timestamp > 0){
								echo date('d.m.Y', $timestamp);
							}
						?></td>							
						<td>&nbsp;</td>				
					</tr>	
					<?php $count++; ?>
					<?php if($count == 10) { break; } ?>				
				<?php endforeach; ?>
			<?php endif; ?>
			
			<?php $i = 0; ?>
			<?php while($i < (10-count($this->p_data)) ) : /* Дорисовываем недостающие строки. Их должно быть 10 всего.*/ ?>
				<tr class="rb-row-def">
					<td>&nbsp;</td>				
					<td>&nbsp;</td>				
					<td>&nbsp;</td>				
					<td>&nbsp;</td>				
					<td>&nbsp;</td>	
				</tr>		
				<?php $i++; ?>
			<?php endwhile; ?>
			
			<tr>
				<td colspan="5" class="rb-text-left rb-border-b-hide rb-pad-l-3">
					<div class="rb-bg-line rb-mar-r" style="height: 10px; overflow: hidden; background-position: 0px -5px; width:80%">
						<span class="rb-bg-none-line">структурного подразделения </span>
										
						<span class="rb-bg-none-line" style="float: right;"> (подпись) </span>						
					</div>
				</td>								
			</tr>		
			<tr>
				<td colspan="5"><?=$this->currentPage;?></td>								
				<?php $this->currentPage++;?>		
			</tr>		
		</table>
	</div>
	
</div>	