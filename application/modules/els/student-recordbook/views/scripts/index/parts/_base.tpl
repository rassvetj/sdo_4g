<div class="recordbook-area">		
		<!-- левая сторона экзамены -->
		<div class="rb-page rb-page-2 rb-pad-reset rb-border-reset">			
				<table class="rb-page-content rb-tbl-border rb-text-under rb-center">
					<tr>
						<td colspan="7" class="rb-text-left rb-border-b-hide rb-pad-l-3">
							<?=$this->semestr;?>-й семестр <?=$this->year_begin;?>/<?=$this->year_end;?> учебного года					
						</td>
					</tr>
					<tr>
						<td colspan="7" class="rb-bold rb-text-right rb-pad-r-3"><?=$this->cource_name;?></td>
					</tr>
					<tr>
						<td colspan="7">Результаты промежуточной аттестации (экзамены)</td>
					</tr>
					<tr class="rb-tbl-caption">
						<td class="rb-c1">№<br>п/п</td>
						<td class="rb-c2">Наименование дисциплины<br>(модуля, раздела)</td>
						<td class="rb-c3">Общее<br>кол-во<br>час./з.ед.</td>
						<td class="rb-c4">Оценка</td>
						<td class="rb-c5">Дата<br>сдачи<br>экзамена</td>						
						<td class="rb-c7">Фамилия<br>преподавателя</td>
					</tr>		
					<?php $count = 0; ?>
					<?php if(!empty($this->p_data[HM_StudyCard_StudyCardModel::TIPE_EXAM])) : ?>
						<?php foreach($this->p_data[HM_StudyCard_StudyCardModel::TIPE_EXAM] as $ve) : ?>				
							<tr class="rb-row-def">
								<td>&nbsp;</td>				
								<td class="rb-text-left"><div class="rb-hide-out"><?=$ve->Disciplina;?></div></td>				
								<td><?=$ve->Hours;?></td>				
								<td><?=$ve->Mark;?></td>				
								<td><?php
									$timestamp = strtotime($ve->Date);
									if($timestamp > 0){
										echo date('d.m.Y', $timestamp);
									}
								?></td>												
								<td><?=$ve->Teacher;?></td>				
							</tr>	
							<?php $count++; ?>
							<?php if($count == 10) { break; } ?>
						<?php endforeach; ?>
					<?php endif; ?>	
					
					<?php $i = 0; ?>
					<?php while($i < (10-count($this->p_data[HM_StudyCard_StudyCardModel::TIPE_EXAM])) ) : /* Дорисовываем недостающие строки. Их должно быть 10 всего.*/ ?>
						<tr class="rb-row-def">
							<td>&nbsp;</td>				
							<td>&nbsp;</td>				
							<td>&nbsp;</td>				
							<td>&nbsp;</td>				
							<td>&nbsp;</td>													
							<td>&nbsp;</td>				
						</tr>		
						<?php $i++; ?>
					<?php endwhile; ?>
				
					
					<tr>
						<td colspan="7" class="rb-text-right rb-border-b-hide rb-pad-r-3">Руководитель</td>								
					</tr>		
					<tr>
						<td colspan="7"><?=$this->currentPage;?></td>
						<?php $this->currentPage++;?>						
					</tr>		
				</table>
			</div>
						
			<!-- правая сторона Зачеты-->
			<div class="rb-page rb-page-2 rb-pad-reset rb-border-reset">					
				<table class="rb-page-content rb-tbl-border rb-text-under rb-center">
					<tr>
						<td colspan="7" class="rb-text-right rb-border-b-hide rb-pad-r-3">
							<div class="rb-center rb-float-r rb-bg-line" style="min-width:50%; background-position: 0px -4px; margin-bottom: -3px; overflow: hidden; line-height: 14px; height: 13px;">
								<?=$this->fio;?>
							</div>					
						</td>
					</tr>
					<tr>				
						<td colspan="3" class="rb-bold rb-text-left rb-pad-l-3 rb-border-r-hide">КУРС</td>
						<td colspan="4" class="rb-text-right rb-pad-r-3">(Фамилия И.О. студента (курсанта))</td>
					</tr>
					<tr>
						<td colspan="7">Результаты промежуточной аттестации (зачеты)</td>
					</tr>
					<tr class="rb-tbl-caption">
						<td class="rb-c1">№<br>п/п</td>
						<td class="rb-c2">Наименование дисциплины<br>(модуля, раздела)</td>
						<td class="rb-c3">Общее<br>кол-во<br>час./з.ед.</td>
						<td class="rb-c4">Оценка</td>
						<td class="rb-c5">Дата<br>сдачи<br>зачета</td>
						<!--<td class="rb-c6">Подпись<br>преподавателя</td>-->
						<td class="rb-c7">Фамилия<br>преподавателя</td>
					</tr>					
					<?php $count = 0; ?>
					<?php if(!empty($this->p_data[HM_StudyCard_StudyCardModel::TIPE_ZACHET])) : ?>
						<?php foreach($this->p_data[HM_StudyCard_StudyCardModel::TIPE_ZACHET] as $k => $ve) : ?>
							<tr class="rb-row-def">
								<td>&nbsp;</td>				
								<td class="rb-text-left"><div class="rb-hide-out "><?=$ve->Disciplina;?></div></td>							
								<td><?=$ve->Hours;?></td>				
								<td><?=$ve->Mark;?></td>											
								<td><?php
									$timestamp = strtotime($ve->Date);
									if($timestamp > 0){
										echo date('d.m.Y', $timestamp);
									}
								?></td>							
								<td><?=$ve->Teacher;?></td>				
							</tr>				
							<?php $count++; ?>
							<?php if($count == 10) { break; } ?>
						<?php endforeach; ?>
					<?php endif; ?>
					<?php $i = 0; ?>
					<?php while($i < (10-count($this->p_data[HM_StudyCard_StudyCardModel::TIPE_ZACHET])) ) : /* Дорисовываем недостающие строки. Их должно быть 10 всего.*/ ?>
						<tr class="rb-row-def">
							<td>&nbsp;</td>				
							<td>&nbsp;</td>				
							<td>&nbsp;</td>				
							<td>&nbsp;</td>				
							<td>&nbsp;</td>												
							<td>&nbsp;</td>				
						</tr>		
						<?php $i++; ?>
					<?php endwhile; ?>
					
					<tr>
						<td colspan="7" class="rb-text-left rb-border-b-hide rb-pad-l-3">
							<div class="rb-bg-line rb-mar-r" style="height: 10px; overflow: hidden; background-position: 0px -5px; width:80%">
								<span class="rb-bg-none-line">структурного подразделения </span>
								<span style="padding-left: 50px;"><?=$this->structural_supervisor?></span>
								<!--[подпись]	-->										
								<span class="rb-bg-none-line" style="float: right;"> (подпись) </span>						
							</div>
						</td>								
					</tr>		
					<tr>
						<td colspan="7"><?=$this->currentPage;?></td>								
						<?php $this->currentPage++;?>
					</tr>		
				</table>
			</div>			
</div>	