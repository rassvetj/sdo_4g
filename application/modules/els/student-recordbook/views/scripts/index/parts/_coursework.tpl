<div class="recordbook-area">
	<!-- левая сторона -->
	<div class="rb-page rb-page-2 rb-pad-reset rb-border-reset">			
		<table class="rb-page-content rb-tbl-border rb-text-under rb-center">
			<tr>
				<td colspan="3" class="rb-text-left rb-border-b-hide rb-pad-l-3">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" class="rb-bold rb-text-right rb-pad-r-3">КУРСОВЫЕ</td>
			</tr>			
			<tr class="rb-tbl-caption">
				<td class="rb-c1">№<br>п/п</td>
				<td class="rb-c2">Наименование дисциплины<br>(модуля, раздела)</td>
				<td>Тема курсовой работы (проекта)</td>				
			</tr>
			<?php $count = 0; ?>			
			<?php if(!empty($this->p_data)) : ?>
				<?php foreach($this->p_data as $p) : ?>
					<tr class="rb-row-def">								
						<td>&nbsp;</td>			
						<td class="rb-text-left"><div class="rb-hide-out"><?=$p->Disciplina;?></div></td>				
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
				</tr>		
				<?php $i++; ?>
			<?php endwhile; ?>
			
			<tr>
				<td colspan="3" class="rb-text-right rb-border-b-hide rb-pad-r-3">&nbsp;</td>								
			</tr>		
			<tr>
				<td colspan="3"><?=$this->currentPage;?></td>								
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
				<td colspan="2" class="rb-bold rb-text-left rb-pad-l-3 rb-border-r-hide">РАБОТЫ (ПРОЕКТЫ)</td>
				<td colspan="3" class="rb-text-right rb-pad-r-3">(Фамилия И.О. студента (курсанта))</td>
			</tr>			
			<tr class="rb-tbl-caption">
				<td class="rb-c1">Семестр</td>
				<td class="rb-c4">Оценка</td>
				<td class="rb-c3">Дата сдачи</td>				
				<td class="rb-c2">Фамилия<br>преподавателя</td>				
			</tr>
			
			<?php $count = 0; ?>
			<?php if(!empty($this->p_data)) : ?>
				<?php foreach($this->p_data as $p) : ?>
					<tr class="rb-row-def">														
						<td><?=$p->Semester;?></td>				
						<td><?=$p->Mark;?></td>					
						<td><?php
							$timestamp = strtotime($p->Date);
							if($timestamp > 0){
								echo date('d.m.Y', $timestamp);
							}
						?></td>
						<td><?=$p->Teacher;?></td>																							
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
				</tr>		
				<?php $i++; ?>
			<?php endwhile; ?>
			
			
					
			
			<tr>
				<td colspan="5" class="rb-text-left rb-border-b-hide rb-pad-l-3">
					<div class="rb-bg-line rb-mar-r" style="height: 10px; overflow: hidden; background-position: 0px -5px; width:80%">
						<span class="rb-bg-none-line">Руководитель структурного подразделения </span>
						<span style="padding-left: 50px;"><?=$this->structural_supervisor?></span>
						<!--[подпись]-->					
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