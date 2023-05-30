<div class="recordbook-area">
	<!-- левая сторона -->
	<div class="rb-page rb-page-3">			
		<table class="rb-page-content">
			<tr>
				<td class="rb-bold rb-text-right rb-border-b-hide rb-pad-r-3">
					<div class="rb-bg-line" style="height: 10px; overflow: hidden; background-position: 0px -5px; width:50%; float: right; font-size: 11px; font-weight: normal;">						
						<?=$this->fio;?>
					</div>
					
				</td>
			</tr>
			<tr>
				<td class="rb-text-right rb-border-b-hide rb-pad-r-3 rb-text-under">(Фамилия И.О. студента (курсанта))</td>
			</tr>
			
			<tr>
				<td class="rb-center rb-caption rb-caption-2 rb-bold">Выпускная квалификационная работа</td>			
			</tr>
			<tr>
				<td>
					<div class="rb-bg-line rb-mar-r" style="height: 20px; overflow: hidden;">
						<span class="rb-bg-none-line">Вид выпускной квалификационной работы:</span>
						<?=$this->grad['type_work'];?>					
					</div>						
				</td>
			</tr>	
			<tr>
				<td>
					<div class="rb-bg-line rb-mar-r" style="height: 70px; overflow: hidden;">
						<span class="rb-bg-none-line">Тема:</span>
						<?=$this->grad['theme'];?>					
					</div>					
				</td>
			</tr>
			<tr>
				<td class="rb-text-under rb-center">(выпускной квалификационной работы)</td>								
			</tr>			
			<tr class="rb-row-grad">
				<td>
					<div class="rb-bg-line rb-mar-r" style="height: 20px; overflow: hidden; margin-bottom: -5px;">
						<span class="rb-bg-none-line">Руководитель:</span>
						<?=$this->grad['manager'];?>					
					</div>					
				</td>
			</tr>
			<tr>
				<td class="rb-text-under rb-center">(Фамилия И.О.)</td>								
			</tr>
			<tr>
				<td>
					<div class="rb-bg-line rb-mar-r" style="height: 20px; overflow: hidden; width: 50%;">
						<span class="rb-bg-none-line">Дата защиты:</span>
						&nbsp;&nbsp;
						<?php 
							
							if(isset($this->grad['date_graduation_work'])){								
								$timestamp = strtotime($this->grad['date_graduation_work']);
								if($timestamp > 0){
									echo date('d.m.Y г.', $timestamp);
								}
							}						
						?>	
					</div>						
				</td>
			</tr>
			<tr>
				<td>
					<div class="rb-bg-line rb-mar-r" style="height: 20px; overflow: hidden; width: 50%;">
						<span class="rb-bg-none-line">Оценка:&nbsp;</span>
						<?=$this->grad['ball'];?>					
					</div>					
				</td>
			</tr>
			<tr class="rb-row-signature-area">
				<td>Подписи представителя и членов Государственной экзаменационной комиссии:<br>
				<?php 
					if(count($this->grad['members_commission']) > 0){
						$count = 1;
						foreach($this->grad['members_commission'] as $m){
							if($count == count($this->grad['members_commission'])){
								echo $m;
							} else {
								echo $m.', ';
							}							
							$count++;
						}						
					}
				?>				
				</td>				
			</tr>
			<tr>
				<td class="rb-center"><?=$this->currentPage;?></td>								
				<?php $this->currentPage++;?>								
			</tr>		
		</table>
	</div>
	<!-- правая сторона -->
	<div class="rb-page rb-page-3">			
		<table class="rb-page-content">
			<tr>
				<td class="rb-center rb-caption rb-caption-2 rb-bold">Решение Государственной экзаменационной комиссии</td>			
			</tr>
			<tr>
				<td>
					<div class="rb-bg-line rb-mar-r" style="height: 20px; overflow: hidden;">
						<span class="rb-bg-none-line">от:</span>						
						<?php 
							if(isset($this->grad['date_commission'])){								
								$timestamp = strtotime($this->grad['date_commission']);
								if($timestamp > 0){
									echo date('d.m.Y г.', $timestamp);
								}
							}										
						?>							
						<span class="rb-bg-none-line">г. протокол №</span>
						<?php if($this->grad['protocol_number']) { echo $this->grad['protocol_number'];} ?>
					</div>						
				</td>
			</tr>	
			<tr>
				<td>
					<div class="rb-bg-line rb-mar-r" style="height: 20px; overflow: hidden; margin-bottom: -5px;">
						<span class="rb-bg-none-line">студенту (курсанту):&nbsp;</span>
						<?=$this->fio;?>					
					</div>					
				</td>
			</tr>
			<tr>
				<td class="rb-text-under rb-center">(фамилия, имя, отчество (последнее - при наличии))</td>								
			</tr>
			<tr>
				<td>
					<div class="rb-bg-line rb-mar-r" style="height: 20px; overflow: hidden; margin-bottom: -5px;">
						<span class="rb-bg-none-line">Присвоена квалификация&nbsp;</span>
						<?=$this->grad['qualification'];?>					
					</div>						
				</td>
			</tr>
			<tr>
				<td class="rb-text-under rb-center">(наименование)</td>								
			</tr>
			<tr>
				<td>
					<div class="rb-bg-line rb-mar-r" style="height: 20px; overflow: hidden; margin-bottom: -5px;">
						<span class="rb-bg-none-line">Присвоено специальное звание&nbsp;</span>
						<?=$this->grad['rank'];?>						
					</div>					
				</td>
			</tr>
			<tr>
				<td class="rb-text-under rb-center">(наименование)</td>								
			</tr>
			<tr class="rb-row-grad">
				<td>
					<div class="rb-bg-line rb-mar-r" style="height: 20px; overflow: hidden;">
						<span class="rb-bg-none-line">Председатель&nbsp;</span>
						<?=$this->grad['chair'];?>					
						<span class="rb-bg-none-line rb-text-under" style="float: right;">(подпись)</span>
					</div>	
				</td>								
			</tr>
			<tr class="rb-row-signature-area-min">
				<td>Члены комиссии:
				<?php 
					if(!empty($this->grad['members_commission'])){
						echo str_replace('~', ', ', $this->grad['members_commission']);
					}					
				?>	
				</td>								
			</tr>
			<tr>
				<td>
					<div class="rb-bg-line rb-mar-r" style="height: 20px; overflow: hidden;">						
					
					</div>						
				</td>
			</tr>
			<tr>
				<td class="rb-text-under rb-center">(подписи)</td>								
			</tr>	
			<tr class="rb-row-gos">
				<td>
					<div class="rb-bg-line rb-mar-r" style="height: 20px; overflow: hidden;">
						<span class="rb-bg-none-line">Выдан диплом&nbsp;</span>
						<?=$this->grad['diplom_series'];?>						
						<div style="float:right;">							
							<span class="rb-bg-none-line">№</span>
							<?=$this->grad['diplom_number'];?>				
							<span class="rb-bg-none-line">от</span>																	
							<?php 
								if(isset($this->grad['date_diplom'])){								
									$timestamp = strtotime($this->grad['date_diplom']);
									if($timestamp > 0){
										echo date('d.m.Y г.', $timestamp);
									}
								}														
							?>								
							<span class="rb-bg-none-line"></span>
						</div>							
					</div>						
				</td>
			</tr>	
			<tr>
				<td>
					<div class="rb-bg-line rb-mar-r" style="height: 20px; overflow: hidden; margin-bottom: -5px;">
						<span class="rb-bg-none-line">Руководитель структурного подразделения&nbsp;</span><span style="padding-left: 50px;"><?=$this->structural_supervisor?></span>										
					</div>						
				</td>
			</tr>		
			<tr>
				<td class="rb-text-under rb-text-right rb-pad-r-75">(подпись, Фамилия, И.О.)</td>								
			</tr>			
			
			<tr>
				<td class="rb-center"><?=$this->currentPage;?></td>								
				<?php $this->currentPage++;?>								
			</tr>		
		</table>
	</div>
	
</div>	