<?php if ($this->form):?>
	<?=$this->form?>
<?php else:?>	
	<style>
		.tbl-debtor-info{
			border: 1px solid #c5d0d7;
			border-collapse: collapse;
		}
		
		.tbl-debtor-info td{
			border: 1px solid #c5d0d7;
			padding: 3px;
		}
		
		.import-group-container{
			padding-bottom: 25px;
			padding-top: 25px;			
		}
	</style>
	<?php $reasonList = HM_Debtors_DebtorsModel::getReasonList(); ?>
	<table class="tbl-debtor-info">
		<?=(count($this->importManager->getUpdateData()))			?	'<tr><td>Продлятся штатным способом</td><td>'.count($this->importManager->getUpdateData()).'</td></tr>':	''?>
		<?=(count($this->importManager->getTutorsAssignSubjects()))	?	'<tr><td>Назначатся тьюторы на сессии</td><td>'.count($this->importManager->getTutorsAssignSubjects()).'</td></tr>':	''?>
		<?=(count($this->importManager->getTutorsAssignGroups()))	?	'<tr><td>Назначатся тьюторы на группы</td><td>'.count($this->importManager->getTutorsAssignGroups()).'</td></tr>':	''?>
		<?=(count($this->importManager->getTutorsUpdateSubjects()))	?	'<tr><td>Изменятся назначения тьюторов</td><td>'.count($this->importManager->getTutorsUpdateSubjects()).'</td></tr>':	''?>				
		<?=(count($this->importManager->getGraduatedDebtorsEnded()))?	'<tr><td>Назначатся без продления и сброса попытки, т.к. завершили обучение и набрали проходной балл</td><td>'.count($this->importManager->getGraduatedDebtorsEnded()).'</td></tr>':	''?>
		<?=(count($this->importManager->getGraduatedDebtors()))		?	'<tr><td>Назначатся с продлением и сбросом попыток, т.к. завершили обучение и НЕ набрали проходной балл</td><td>'.count($this->importManager->getGraduatedDebtors()).'</td></tr>':	''?>
		<?=(count($this->importManager->getAlredyExtended()))		?	'<tr><td>Не продлятся, т.к. даты продления совпадают со старыми</td><td>'.count($this->importManager->getAlredyExtended()).'</td></tr>':	''?>
		<?=(count($this->importManager->getNotAssign()))			?	'<tr><td>Не продлятся, т.к. студент никогда не обучался на сессии</td><td>'.count($this->importManager->getNotAssign()).'</td></tr>'			:	''?>
		<?=(count($this->importManager->getNotFoundDebtors()))		?	'<tr><td>Не найден студент или сессия</td><td>'.count($this->importManager->getNotFoundDebtors()).'</td></tr>'	:	''?>
		<?=(count($this->importManager->getConflictData()))			?	'<tr><td>Конфликтная ситуация</td><td>'.count($this->importManager->getConflictData()).'</td></tr>'					:	''?>
		<?=(count($this->importManager->getIncorrectData()))		?	'<tr><td>Некорректные данные</td><td>'.count($this->importManager->getIncorrectData()).'</td></tr>'					:	''?>
		<?=(count($this->importManager->getTutorNotFound()))		?	'<tr><td>Не найден тьютор</td><td>'.count($this->importManager->getTutorNotFound()).'</td></tr>'					:	''?>
	
	</table>
	
	<br/>
    <?=$this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'debtors', 'controller' => 'import', 'action' => 'index', 'source' => $this->source))).'"'))?>
    <a href="#" onClick="importAll($(this)); return false;" id="btn-import-all"><button class="ui-button-text">Импортировать все</button></a>
	<input type="hidden" id="next-block" value=""><?# служит тригером дял запуска следующего блока импорта.По окончанию первого блока он меняет значение на id следующего блока. Скрипты всех блоков слушают этот input в случае изменения?>
	<div id="area-all-progress">
	</div>
    
	
	<br />
	<?php if (count($this->importManager->getUpdateData())):?>
		<div class="import-group-container">
			<?php $count = 1;?>
			<h3>Будут обновлены следующие записи:</h3>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<a href="<?=$this->baseUrl($this->url(array('module' => 'debtors', 'controller' => 'export',  'action' => 'csv', 'type' => 'updated')));?>" target="_blank"><button class="ui-button-text">Выгрузить в csv</button></a>
			<a href="<?=$this->baseUrl($this->url(array('module' => 'debtors', 'controller' => 'process', 'action' => 'base')));?>" target="_blank" class="btn-import-action" id="btn-UpdateData"><button class="ui-button-text">Импортировать</button></a>
			<br/>
			<div class="import-data">
				<table class="main " width="100%">
				<tr>
					<th rowspan="2">ID сессии</th>
					<th rowspan="2">Сессия</th>
					<th rowspan="2">ФИО</th>
					<th colspan="3">Первое продление</th>
					<th colspan="3">Второе продление</th>
					<th rowspan="2">Семестр</th>
					<th rowspan="2"><?=_('Контроль')?></th>
					<th rowspan="2"></th>
				</tr>
				<tr>
					<th>Уже продлена до</th>
					<th>Продлить до</th>
					<th>Назначатся тьюторы</th>
					<th>Уже продлена до</th>
					<th>Продлить до</th>
					<th>Назначатся тьюторы</th>
				</tr>		
				<?php foreach($this->importManager->getUpdateData() as $row):?>					
					<?php if ($count >= 500) { echo "<tr><td colspan=\"100%\"><b>...</b></td></tr>"; break;}?>
					<tr>
						<td><?=$row->session_external_id?></td>
						<td><?=$row->name?></td>                
						<td><?=$row->fio?></td>                				
						<td><?=($row->old_time_ended_debtor) 	? date('d.m.Y', strtotime($row->old_time_ended_debtor)) : 'Не продлена';?></td>
						<td><?=($row->time_ended_debtor) 		? date('d.m.Y', strtotime($row->time_ended_debtor))  	: 'Нет';?></td>				
						<td><?=(!empty($row->tutor)) 			? implode(', ', $row->tutor) 							: 'Нет';?></td>
						
						<td><?=($row->old_time_ended_debtor_2) ? date('d.m.Y', strtotime($row->old_time_ended_debtor_2)): 'Не продлена';?></td>
						<td><?=($row->time_ended_debtor_2) 	? date('d.m.Y', strtotime($row->time_ended_debtor_2))  		: 'Нет';?></td>				
						<td><?=(!empty($row->tutor_2)) 		? implode(', ', $row->tutor_2) 								: 'Нет';?></td>
						
						<td><?=$row->semester?></td>
						<td><?=$row->exam_type_name?></td>

						<td><ol><?php 
								if(!empty($row->reasonFail)){
									foreach($row->reasonFail as $i){
										echo '<li>'.$i['message'];
										if(!empty($i['lessons'])){
											echo ' в занятииях #'.implode(', ', $i['lessons']);
										}		
										echo '</li>';
									}								
								}
						?></ol></td>						
					</tr>
					<?php $count++;?>
				<?php endforeach;?>
				</table>
			</div>
		</div>
    <?php endif;?>	
	
	
	<?php if (count($this->importManager->getAlredyExtended())):?>
		<div class="import-group-container">
			<?php $count = 1; ?>				
			<h3>Записи не нужнаются в продлении и НЕ будут изменены:</h3>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<a href="<?=$this->baseUrl($this->url(array('module' => 'debtors', 'controller' => 'export', 'action' => 'csv', 'type' => 'not-changed')));?>" target="_blank"><button class="ui-button-text">Выгрузить в csv</button></a>
			<br/>
			<div class="import-data" style="display: none;">
				<table class="main" width="100%" id="tbl_incorrect" >
					<tr>
						<th>ID студента</th>
						<th>ФИО</th>
						<th>ID сессии</th>
						<th>Сессия</th>
						<th>Специальность</th>
						<th>Семестр</th>
						<th>Дата продления</th>
						<th>Дата продления 2</th>
						<th>Причина</th>
						<th><?=_('Контроль')?></th>						
					</tr>
					<?php foreach($this->importManager->getAlredyExtended() as $row):?>
						<?php if ($count >= 100) { echo "<tr><td colspan=\"100%\"><b>...</b></td></tr>"; break;}?>				
						<tr>
							<td><?=$row->mid_external?></td>
							<td><?=$row->fio?></td>
							<td><?=$row->session_external_id?></td>
							<td><?=$row->name?></td>
							<td><?=$row->specialty?></td>
							<td><?=$row->semester?></td>
							<td><?=	($row->time_ended_debtor)	? (date('d.m.Y', strtotime($row->time_ended_debtor)) )  	: ('');?></td>
							<td><?=	($row->time_ended_debtor_2)	? (date('d.m.Y', strtotime($row->time_ended_debtor_2)) )	: ('');?></td>	
							
							<td><?=$reasonList[$row->conflict_reason_code]?></td>
							<td><?=$row->exam_type_name?></td>							
						</tr>
						<?php $count++;?>
					<?php endforeach;?>
				</table>
			</div>		
		</div>		
	<?php endif;?>
	
	
	
	<?php if (count($this->importManager->getIncorrectData())):?>
		<div class="import-group-container">
			<?php $count = 1; ?>				
			<h3>Некорректные даннные:</h3>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<a href="<?=$this->baseUrl($this->url(array('module' => 'debtors', 'controller' => 'export', 'action' => 'csv', 'type' => 'incorrect')));?>" target="_blank"><button class="ui-button-text">Выгрузить в csv</button></a>
			<br/>		
			<div class="import-data"  style="display: none;">
				<table class="main" width="100%" id="tbl_incorrect" >
					<tr>
						<th>ID студента</th>
						<th>ФИО</th>
						<th>ID сессии</th>
						<th>Сессия</th>
						<th>Специальность</th>
						<th>Семестр</th>
						<th>Дата продления</th>
						<th>Дата продления 2</th>
						<th>Причина</th>
						<th><?=_('Контроль')?></th>							
					</tr>
					<?php foreach($this->importManager->getIncorrectData() as $row):?>
						<?php if ($count >= 100) { echo "<tr><td colspan=\"100%\"><b>...</b></td></tr>"; break;}?>
						<tr>
							<td><?=$row->mid_external?></td>
							<td><?=$row->fio?></td>
							<td><?=$row->session_external_id?></td>
							<td><?=$row->name?></td>
							<td><?=$row->specialty?></td>
							<td><?=$row->semester?></td>
							<td><?=$row->time_ended_debtor?></td>
							<td><?=$row->time_ended_debtor_2?></td>
							<td><?=$reasonList[$row->conflict_reason_code]?></td>
							<td><?=$row->exam_type_name?></td>							
						</tr>
						<?php $count++;?>
					<?php endforeach;?>
				</table>
			</div>		
		</div>		
	<?php endif;?>
	
	
	<?php if (count($this->importManager->getNotAssign())):?>
		<div class="import-group-container">
			<?php $count = 1; ?>				
			<h3>Студент никогда не обучался на сессии. Не будет продлен.</h3>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<a href="<?=$this->baseUrl($this->url(array('module' => 'debtors', 'controller' => 'export', 'action' => 'csv', 'type' => 'not-assign')));?>" target="_blank"><button class="ui-button-text">Выгрузить в csv</button></a>
			<br/>
			<div class="import-data"  style="display: none;">			
				<table class="main" width="100%" id="tbl_incorrect" >
					<tr>
						<th>ID студента</th>
						<th>ФИО</th>
						<th>ID сессии</th>
						<th>Сессия</th>
						<th>Специальность</th>
						<th>Семестр</th>
						<th>Дата продления</th>
						<th>Дата продления 2</th>
						<th><?=_('Контроль')?></th>
					</tr>
					<?php foreach($this->importManager->getNotAssign() as $row):?>
						<?php if ($count >= 100) { echo "<tr><td colspan=\"100%\"><b>...</b></td></tr>"; break;}?>				
						<tr>
							<td><?=$row->mid_external?></td>
							<td><?=$row->fio?></td>
							<td><?=$row->session_external_id?></td>
							<td><?=$row->name?></td>
							<td><?=$row->specialty?></td>
							<td><?=$row->semester?></td>
							<td><?=	($row->time_ended_debtor)	? (date('d.m.Y', strtotime($row->time_ended_debtor)) )  	: ('');?></td>
							<td><?=	($row->time_ended_debtor_2)	? (date('d.m.Y', strtotime($row->time_ended_debtor_2)) )	: ('');?></td>
							<td><?=$row->exam_type_name?></td>
						</tr>
						<?php $count++;?>
					<?php endforeach;?>
				</table>	
			</div>
		</div>
	<?php endif;?>
	
	
	
	<?php if (count($this->importManager->getTutorsAssignSubjects())):?>
		<div class="import-group-container">
			<?php $count = 1; ?>				
			<h3>Назначаться тьюторы на сессии:</h3>
			<p>Даты продления влияют только на принадлежность тьютора к 1 или 2 продлению, величина самой даты не имеет значения.</p>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<a href="<?=$this->baseUrl($this->url(array('module' => 'debtors', 'controller' => 'export',  'action' => 'csv', 'type' => 'assign-tutor-subject')));?>" target="_blank"><button class="ui-button-text">Выгрузить в csv</button></a>
			<a href="<?=$this->baseUrl($this->url(array('module' => 'debtors', 'controller' => 'process', 'action' => 'assign-tutor-subject')));?>" target="_blank" class="btn-import-action" id="btn-TutorsAssignSubjects"><button class="ui-button-text">Импортировать</button></a>		
			<br/>
			<div class="import-data"  style="display: none;">			
				<table class="main" width="100%" id="tbl_incorrect" >
					<tr>				
						<th>Тьютор</th>
						<th>ID сессии</th>
						<th>Сессия</th>				
						<th>Дата продления</th>
						<th>Дата продления 2</th>				
					</tr>
					<?php foreach($this->importManager->getTutorsAssignSubjects() as $row):?>
						<?php if ($count >= 100) { echo "<tr><td colspan=\"100%\"><b>...</b></td></tr>"; break;}?>
						<tr>				
							<td><?=$row['tutor_fio']?></td>
							<td><?=$row['subject_external_id']?></td>
							<td><?=$row['subject_name']?></td>
							<td><?=	($row['date_debt'])		? (date('d.m.Y', strtotime($row['date_debt'])) )  : ('');?></td>
							<td><?=	($row['date_debt_2'])	? (date('d.m.Y', strtotime($row['date_debt_2'])) ): ('');?></td>
						</tr>
						<?php $count++;?>
					<?php endforeach;?>
				</table>	
			</div>
		</div>
	<?php endif;?>
	
	
	
	
	
	
	
	
	
	
	
	
	<?php if (count($this->importManager->getTutorNotFound())):?>
		<div class="import-group-container">
			<?php $count = 1; ?>				
			<h3>Не найдены тьюторы</h3>			
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<a href="<?=$this->baseUrl($this->url(array('module' => 'debtors', 'controller' => 'export',  'action' => 'csv', 'type' => 'tutor-not-found')));?>" target="_blank"><button class="ui-button-text">Выгрузить в csv</button></a>			
			<br/>
			<div class="import-data"  style="display: none;">			
				<table class="main" width="100%" id="tbl_incorrect" >
					<tr>				
						<th>ID тьютора</th>						
					</tr>
					<?php foreach($this->importManager->getTutorNotFound() as $row):?>
						<?php if ($count >= 100) { echo "<tr><td colspan=\"100%\"><b>...</b></td></tr>"; break;}?>
						<tr>				
							<td><?=$row['tutor_mid_external']?></td>												
						</tr>
						<?php $count++;?>
					<?php endforeach;?>
				</table>	
			</div>
		</div>
	<?php endif;?>
	
	
	
	
	
	
	
	<?php if (count($this->importManager->getTutorsUpdateSubjects())):?>
		<div class="import-group-container">
			<?php $count = 1; ?>				
			<h3>Изменятся назначения тьюторов.</h3>
			<p>Задается максимальная дата среди продлений студентов. Важен факт наличия даты, а не ее значение. Если дата есть, значит пользователь является тьютором N продления</p>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<a href="<?=$this->baseUrl($this->url(array('module' => 'debtors', 'controller' => 'export', 'action' => 'csv', 'type' => 'update-assign-tutor')));?>" target="_blank"><button class="ui-button-text">Выгрузить в csv</button></a>
			<a href="<?=$this->baseUrl($this->url(array('module' => 'debtors', 'controller' => 'process', 'action' => 'update-tutor-subject')));?>" target="_blank" class="btn-import-action" id="btn-TutorsUpdateSubjects"><button class="ui-button-text">Импортировать</button></a>		
			<br/>	
			<div class="import-data"  style="display: none;">			
				<table class="main" width="100%" id="tbl_incorrect">
					<tr>				
						<th>Тьютор</th>
						<th>ID сессии</th>
						<th>Сессия</th>				
						<th>Старая дата первого продления</th>
						<th>Новая дата первого продления</th>
						<th>Старая дата второго продления</th>
						<th>Новая дата второго продления</th>				
					</tr>
					<?php foreach($this->importManager->getTutorsUpdateSubjects() as $row):?>
						<?php if ($count >= 100) { echo "<tr><td colspan=\"100%\"><b>...</b></td></tr>"; break;}?>
						<tr>				
							<td><?=$row['tutor_fio']?></td>
							<td><?=$row['subject_external_id']?></td>
							<td><?=$row['subject_name']?></td>
							<td>
								<?=	($row['old_date_debt'])				? (date('d.m.Y', strtotime($row['old_date_debt'])) )  	: ('');?>
								<?= (!isset($row['new_date_debt'])) 	? 'Без изменений' : '' ?>
							</td>
							<td>
								<?=	($row['new_date_debt'])				? (date('d.m.Y', strtotime($row['new_date_debt'])) )	: ('');?>
								<?= (!isset($row['new_date_debt'])) 	? '-' : '' ?>
							</td>
							<td>
								<?=	($row['old_date_debt_2'])			? (date('d.m.Y', strtotime($row['old_date_debt_2'])) )	: ('');?>
								<?= (!isset($row['new_date_debt_2'])) 	? 'Без изменений' : '' ?>
							</td>
							<td>
								<?=	($row['new_date_debt_2'])			? (date('d.m.Y', strtotime($row['new_date_debt_2'])) )	: ('');?>
								<?= (!isset($row['new_date_debt_2'])) 	? '-' : '' ?>
							</td>
						</tr>
						<?php $count++;?>
					<?php endforeach;?>
				</table>
			</div>
		</div>
	<?php endif;?>
	
	
	
	
	<?php if (count($this->importManager->getTutorsAssignGroups())):?>
		<div class="import-group-container">
			<?php $count = 1; ?>				
			<h3>Назначаться тьюторы на группы:</h3>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<a href="<?=$this->baseUrl($this->url(array('module' => 'debtors', 'controller' => 'export',  'action' => 'csv', 'type' => 'assign-tutor-group')));?>" target="_blank"><button class="ui-button-text">Выгрузить в csv</button></a>
			<a href="<?=$this->baseUrl($this->url(array('module' => 'debtors', 'controller' => 'process', 'action' => 'assign-tutor-group')));?>" target="_blank" class="btn-import-action" id="btn-TutorsAssignGroups"><button class="ui-button-text">Импортировать</button></a>		
			<br/>	
			<div class="import-data"  style="display: none;">			
				<table class="main" width="100%" id="tbl_incorrect" >
					<tr>				
						<th>Тьютор</th>
						<th>ID сессии</th>
						<th>Сессия</th>				
						<th>Группа</th>								
						<th>Из-за студента</th>								
					</tr>
					<?php foreach($this->importManager->getTutorsAssignGroups() as $row):?>
						<?php if ($count >= 100) { echo "<tr><td colspan=\"100%\"><b>...</b></td></tr>"; break;}?>
						<tr>				
							<td><?=$row['tutor_fio']?></td>
							<td><?=$row['subject_external_id']?></td>
							<td><?=$row['subject_name']?></td>
							<td><?=$row['group_name']?></td>					
							<td><?=$row['student_fio']?></td>					
						</tr>
						<?php $count++;?>
					<?php endforeach;?>
				</table>
			</div>
		</div>
	<?php endif;?>
	
	
	
	
	
	
	<?php if (count($this->importManager->getNotFoundDebtors())):?>
		<div class="import-group-container">
			<?php $count = 1; ?>				
			<h3>Не найден студент или сессия. Данные записи НЕ будут обновлены:</h3>
			<span>Возможные сессии - сессии, на которые назначен студент с вхождением имени сессии из scv</span><br />
			<span>Группа - общие группы сессии (через программу обучения) и студента. Если общих нет, выводятся все группы студента</span><br />
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<a href="<?=$this->baseUrl($this->url(array('module' => 'debtors', 'controller' => 'export', 'action' => 'csv', 'type' => 'not-found')));?>" target="_blank"><button class="ui-button-text">Выгрузить в csv</button></a>
			<br/>	
			<div class="import-data"  style="display: none;">			
				<table class="main" width="100%" id="tbl_notfound" >
					<tr>
						<th>Причина</th>
						<th>ID студента</th>
						<th>ФИО</th>
						<th>Группы студента</th>
						<th>ID сессии</th>
						<th>Дисциплина</th>
						<th>Специальность</th>
						<th>Дата начала</th>
						<th>Дата окончания</th>
						<th>Продлить до</th>
						<th>Продлить до 2</th>
						<th>Возможные сессии</th>
						<th>Группа</th>
						<th>Семестр</th>
						<th><?=('Контроль')?></th>
					</tr>
					
					<?php foreach($this->importManager->getNotFoundDebtors() as $row):?>	
						<?php if ($count >= 100) { echo "<tr><td colspan=\"100%\"><b>...</b></td></tr>"; break;}?>
						<tr>
							<td><?php 					
								if(is_array($row->conflict_reason_code)){
									foreach($row->conflict_reason_code as $code){
										echo $reasonList[$code].'<br />';								
									}							
								}						
							?></td>
							<td><?=$row->mid_external?></td>
							<td><?=$row->fio?></td>
							<td><?=(!empty($row->groups))	? implode(', ', $row->groups) : 'Нет';?></td>
							<td><?=$row->session_external_id?></td>
							<td><?=$row->name?></td>					
							<td><?=$row->specialty?></td>
							<td><?=	($row->dateBegin) 			? (date('d.m.Y', strtotime($row->dateBegin)) ) 	 		: ('');?></td>
							<td><?=	($row->dateEnd) 			? (date('d.m.Y', strtotime($row->dateEnd)) ) 	 		: ('');?></td>
							<td><?=	($row->time_ended_debtor) 	? (date('d.m.Y', strtotime($row->time_ended_debtor)) ) 	: ('');?></td>
							<td><?=	($row->time_ended_debtor_2)	? (date('d.m.Y', strtotime($row->time_ended_debtor_2)) ): ('');?></td>
							<td>
							<?php if(!empty($row->supposedSubjects)): ?>						
								<?php $firstSupposedSubjects = reset($row->supposedSubjects); ?>			
								<?=($firstSupposedSubjects['external_id']) ? $firstSupposedSubjects['external_id'] : 'нет';?> - 
								<a href="<?=$this->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card' , 'subject_id' =>$firstSupposedSubjects['id']), null, true)?>" target="_blank"><?=$firstSupposedSubjects['name']?></a>
								<?=(!empty($firstSupposedSubjects['session_ended'])) ? ' (до '.date('d.m.Y', strtotime($firstSupposedSubjects['session_ended'])).')' : ''?>
							<?php endif; ?>
							</td>
							
							<td>
							<?php if(!empty($row->supposedSubjects)): ?>											
								<?=implode(', ', $firstSupposedSubjects['groups']);?>
							<?php endif; ?>
							</td>
							
							<td><?=$row->semester?></td>
							<td><?=$row->exam_type_name?></td>
						</tr>				
						<?php if(!empty($row->supposedSubjects)): ?>
							<?php $numRow = 0; ?>
							<?php foreach($row->supposedSubjects as $subj):?>
								<?php $numRow++; ?>
								<?php if($numRow == 1){ continue; }?>
								<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td><?=$row->session_external_id?></td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>
										<?=($subj['external_id']) ? $subj['external_id'] : 'нет';?> - 
										<a href="<?=$this->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card' , 'subject_id' => $subj['id']), null, true)?>" target="_blank"><?=$subj['name']?></a>
										<?=(!empty($subj['session_ended'])) ? ' (до '.date('d.m.Y', strtotime($subj['session_ended'])).')' : ''?>
									</td>
									<td>
										<?=implode(', ', $subj['groups']);?>
									</td>
									<td><?=$row->semester?></td>
									<td><?=$row->exam_type_name?></td>
								</tr>
							<?php endforeach;?>
						<?php endif;?>				
						
						<?php $count++;?>
					<?php endforeach;?>
				</table>
			</div>
		</div>
	<?php endif;?>
	          
		
	<?php if (count($this->importManager->getGraduatedDebtors())):?>
		<div class="import-group-container">
			<?php $count = 1; ?>				
			<h3>Следующие записи будут переведены из "прошедших обучение" в активное и продлены:</h3>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<a href="<?=$this->baseUrl($this->url(array('module' => 'debtors', 'controller' => 'export', 'action' => 'csv', 'type' => 'graduated')));?>" target="_blank"><button class="ui-button-text">Выгрузить в csv</button></a>
			<a href="<?=$this->baseUrl($this->url(array('module' => 'debtors', 'controller' => 'process', 'action' => 'graduate')));?>" target="_blank" class="btn-import-action" id="btn-GraduatedDebtors"><button class="ui-button-text">Импортировать</button></a>
			<br/>
			<div class="import-data"  style="display: none;">
				<table class="main" width="100%" id="tbl_graduated" >
					<tr>
						<th>ID сессии</th>
						<th>Сессия</th>
						<th>ФИО</th>
						<th>Продлить до</th>
						<th>Продлить до 2</th>				
						<th>Семестр</th>
						<th><?=_('Контроль')?></th>
						<th>Дополнительно</th>
						<th></th>
					</tr>
					<?php foreach($this->importManager->getGraduatedDebtors() as $row):?>
						<?php if ($count >= 100) { echo "<tr><td colspan=\"100%\"><b>...</b></td></tr>"; break;}?>
						<tr>
							<td><?=$row->session_external_id?></td>
							<td><?=$row->name?></td>
							<td><?=$row->fio?></td>					
							<td><?=	($row->time_ended_debtor) 	? (date('d.m.Y', strtotime($row->time_ended_debtor)) ) 	 : ('');?></td>
							<td><?=	($row->time_ended_debtor_2) ? (date('d.m.Y', strtotime($row->time_ended_debtor_2)) ) : ('');?></td>								
							<td><?=$row->semester?></td>
							<td><?=$row->exam_type_name?></td>
							<td>
								<?php if($row->isDO):?>
									Итоговый балл: <?=$row->mark?> из 100. Минимум <?=$row->mark_needed?>.
								<?php else:?>
									Рубежный рейтинг: <?=$row->mark_landmark?> из <?=$row->mark_landmark_max?>. Минимум <?=$row->mark_landmark_needed?>.
								<?php endif;?>					
							</td>							
							<td><ol><?php 
								foreach($row->reasonFail as $i){
									echo '<li>'.$i['message'];
									if(!empty($i['lessons'])){
										echo ' в занятииях #'.implode(', ', $i['lessons']);
									}		
									echo '</li>';
								}								
							?></ol></td>
						</tr>
						<?php $count++;?>
					<?php endforeach;?>
				</table>
			</div>
		</div>
	<?php endif;?>	            			
            
	
	
	<?php if (count($this->importManager->getGraduatedDebtorsEnded())):?>	
		<div class="import-group-container">
			<?php $count = 1;?>
			<h3>
				В следующих записях будет обновлена итоговая оценка на оценку по урокам в СДО. 
				Студент будет повторно назначен на курс из "прошедших обучение". 
				Продлены эти записи не будут, т.к. итоговый балл (рубежный рейтинг) больше или равен 65%:
			</h3>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<a href="<?=$this->baseUrl($this->url(array('module' => 'debtors', 'controller' => 'export',  'action' => 'csv', 'type' => 'graduate-passed')));?>" target="_blank"><button class="ui-button-text">Выгрузить в csv</button></a>
			<a href="<?=$this->baseUrl($this->url(array('module' => 'debtors', 'controller' => 'process', 'action' => 'graduate-passed')));?>" target="_blank" class="btn-import-action" id="btn-GraduatedDebtorsEnded"><button class="ui-button-text">Импортировать</button></a>
			<br/>
			<div class="import-data"  style="display: none;">
				<table class="main" width="100%" id="tbl_gradend" >
				<tr>
					<th>ID сессии</th>
					<th>Сессия</th>
					<th>ФИО</th>
					<th>Старый итоговый балл (Итоговый текущий рейтинг/Рубежный рейтинг)</th>
					<th>Новый итоговый балл  (Итоговый текущий рейтинг/Рубежный рейтинг)</th>
					<th>Семестр</th>			
					<th><?=_('Контроль')?></th>			
					<th>Причина</th>			
				</tr>
				<?php foreach($this->importManager->getGraduatedDebtorsEnded() as $row):?>
					<?php if ($count >= 100) { echo "<tr><td colspan=\"100%\"><b>...</b></td></tr>"; break;}?>
					<tr>
						<td><?=$row->session_external_id?></td>
						<td><?=$row->name?></td>
						<td><?=$row->fio?></td>					
						<td>
							<?php if($row->isDO):?>
								<?=$row->old_mark?>
							<?php else:?>
								<?=$row->old_mark_current?>/<?=$row->old_mark_landmark?>						
							<?php endif;?>
						</td>
						<td>
							<?php if($row->isDO):?>
								<?=$row->mark?>				
							<?php else:?>
								<?=$row->mark_current?>/<?=$row->mark_landmark?>
							<?php endif;?>
						</td>				
						<td><?=$row->semester?></td>
						<td><?=$row->exam_type_name?></td>						
						<td><?=$reasonList[$row->conflict_reason_code]?></td>							
					</tr>
					<?php $count++;?>
				<?php endforeach;?>
				</table>
			</div>
		</div>
    <?php endif;?>
	
	
	
	<?php if (count($this->importManager->getConflictData())):?>	
		<div class="import-group-container">
			<?php $count = 1; ?>				
			<h3>Кофликтные ситуации:</h3>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<a href="<?=$this->baseUrl($this->url(array('module' => 'debtors', 'controller' => 'export', 'action' => 'csv', 'type' => 'conflict')));?>" target="_blank"><button class="ui-button-text">Выгрузить в csv</button></a>
			<br/>	
			<div class="import-data"  style="display: none;">			
				<table class="main" width="100%" id="tbl_conflict" >
				<tr>
					<th>ID сессии</th>
					<th>Сессия</th>
					<th>ID студента</th>
					<th>ФИО</th>                        
					<th>Группа</th>                        
					<th>ID тьютора</th>
					<th>ФИО тьютора</th>
					<th>ID тьютора 2</th>
					<th>ФИО тьютора 2</th>
					<th>Текущий балл в СДО (Итоговый текущий рейтинг/Рубежный рейтинг)</th>
					<th>Дата продления</th>
					<th>Дата продления 2</th>
					<th>Семестр</th>
					<th><?=_('Контроль')?></th>
					<th>Причина</th>
					<th>Тьютор в СДО</th>
				</tr>
				<?php foreach($this->importManager->getConflictData() as $row):?>
					<?php if ($count >= 100) { echo "<tr><td colspan=\"100%\"><b>...</b></td></tr>"; break;}?>
					<tr>
						<td><?=$row->session_external_id?></td>
						<td><?=$row->name?></td>
						<td><?=$row->mid_external?></td>
						<td><?=$row->fio?></td>
						<td><?=(!empty($row->groups))	? implode(', ', $row->groups) 				: 'Нет';?></td>
						<td><?=(!empty($row->tutor))	? implode(', ', array_keys($row->tutor))	: 'Нет';?></td>
						<td><?=(!empty($row->tutor)) 	? implode(', ', $row->tutor) 				: 'Нет';?></td>
						<td><?=(!empty($row->tutor_2))	? implode(', ', array_keys($row->tutor_2))	: 'Нет';?></td>
						<td><?=(!empty($row->tutor_2)) 	? implode(', ', $row->tutor_2) 				: 'Нет';?></td>
						<td>
							<?php if($row->isDO):?>
								<?=$row->old_mark?>
							<?php else:?>
								<?/*<?=$row->old_mark_current?>/<?=$row->old_mark_landmark?>*/?>
								<?php if(!empty($row->mark_current) && !empty($row->mark_landmark)): ?>
									<?=$row->mark?> => <?=$row->mark_current?>/<?=$row->mark_landmark?>
								<?php else: ?>
									<?=$row->old_mark?> => <?=$row->old_mark_current?>/<?=$row->old_mark_landmark?>
								<?php endif;?>
							<?php endif;?>	
						</td>				
						<td><?=($row->old_time_ended_debtor) 	? date('d.m.Y', strtotime($row->old_time_ended_debtor)) : 'Не продлена';?></td>
						<td><?=($row->old_time_ended_debtor_2) 	? date('d.m.Y', strtotime($row->old_time_ended_debtor_2)): 'Не продлена';?></td>
						<td><?=$row->semester?></td>
						<td><?=$row->exam_type_name?></td>
						<td><?=$reasonList[$row->conflict_reason_code]?></td>				                               	
						<td>
							<?php if(!empty($row->tutors)):?>
								<?php foreach($row->tutors as $tutor_id => $tutor_name):?>									
									<?=$tutor_name?>
									<br />
								<?php endforeach;?>
							<?php endif;?>					
						</td>
					</tr>
					<?php $count++;?>
				<?php endforeach;?>
				</table>	
			</div>
		</div>
	<?php endif;?>
	
	<div class="import-group-container">
		<h3>Лишние назначения первого и второго продления:</h3>
		<a href="<?=$this->baseUrl($this->url(array('module' => 'debtors', 'controller' => 'process', 'action' => 'set-base-role-tutors')));?>" target="_blank" class="btn-import-action" id="btn-setBaseRoleTutors"><button class="ui-button-text">Удалить</button></a>
	</div>
	
	<div class="import-group-container">
		<h3>Удалить закрепления за определенным занятием (роли лектор/практик/лаборант) для всех указанных тьюторов и соотв. сессий в csv:</h3>
		<a href="<?=$this->baseUrl($this->url(array('module' => 'debtors', 'controller' => 'process', 'action' => 'remove-lesson-assign-tutors')));?>" target="_blank" class="btn-import-action" id="btn-removeLessonAssignTutors"><button class="ui-button-text">Удалить</button></a>
	</div>
	
	
	
	<script>	
	$( document ).ready(function() {
		$('.btn-import-action').click(function() {
			var url = $(this).attr('href');
			var el = $(this);
			el.css( 'pointer-events', 'none' );
			el.find('button').prop( "disabled", true );
			$.ajax({
			  type: "POST",
			  url: url,
			  dataType: "json",
			  success: function(data){				
				if (typeof data.message !== "undefined") {				
					el.after('<span>'+data.message+'</span>');
					el.remove();
				}
			  },
			  fail: function(data){				
				el.after('<span>Что-то пошло не так :/</span>');
				el.remove();
			  },
			  error: function (request, status, error) {
				el.after('<span>Что-то пошло не так :/ '+request.status+' ('+error+')</span>');
				el.remove();				
			  }
			});
			return false;
		});
		
	});
	
	<? # общая ф-ция мипорта аяксом ?>
	function importBase(url, el, next_block_id){
		el.css( 'pointer-events', 'none' );
		el.find('button').prop( "disabled", true );
		
		var res = $.ajax({
					  type: "POST",
					  url: url,
					  dataType: "json",
					  success: function(data){				
						if (typeof data.message !== "undefined") {							
							el.after('<span>'+data.message+'</span>');
							$('#area-all-progress').append(data.message);
							$('#next-block').val(next_block_id);
							$('#next-block').trigger('change');
							el.remove();
						}
					  },
					  fail: function(data){				
						var msg = 'Что-то пошло не так :/';
						el.after('<span>'+msg+'</span>');						
						$('#area-all-progress').append(msg);
						$('#next-block').val(next_block_id);
						$('#next-block').trigger('change');
						el.remove();
					  },
					  error: function (request, status, error) {
						var msg = 'Что-то пошло не так :/ '+request.status+' ('+error+')';
						el.after('<span>'+msg+'</span>');
						$('#area-all-progress').append(msg);
						$('#next-block').val(next_block_id);
						$('#next-block').trigger('change');
						el.remove();
					  }
				  });
		return res;
	}
	
	function hideTbl(btn){
			var tbl = btn.closest('.import-group-container').find('.import-data');
			
			if(tbl.is(":visible")){
				tbl.slideToggle("slow");
			} else {
				tbl.slideToggle("slow");
			}
	}
	
	function importBlock(block_id, next_block_id){
		
		var messages = {
			'UpdateData'			: 'Выполняется старнадртное продлениение...',
			'GraduatedDebtors'		: 'Выполняется продление "прошедших обучение" с порогом меньше 65%...',
			'GraduatedDebtorsEnded'	: 'Выполняется продление "прошедших обучение" с порогом больше 65%...',
			'TutorsAssignSubjects'	: 'Выполняется назначение тьюторов на сессии...',
			'TutorsAssignGroups'	: 'Выполняется назначение тьюторов на группы...',
			'TutorsUpdateSubjects'	: 'Выполняется обновление назначений тьюторов...',
			'setBaseRoleTutors'		: 'Выполняется удаление лишних назначений...',
			'removeLessonAssignTutors' : 'Выполняется удаление закреплений тьюторов за определенными занятиями...',
		};
		
		var btn = $('#btn-'+block_id);		
		if(btn.length){
			$('#area-all-progress').append('<hr /><p>'+messages[block_id]+'</p>');
			var url = btn.attr('href');			
			importBase(url, btn, next_block_id);
		} else {
			// Если нет текущего блока, переходим к след. Иначе переход выполняется в ф-ции ajax по окончанию скрипта.
			$('#next-block').val(next_block_id);
			$('#next-block').trigger('change');
		}	
		
	}
	
	$('#next-block').change(function() {
		var next_block_id = $(this).val();
		
		// 1 Будут обновлены следующие записи
		if(next_block_id == 'UpdateData')			{ importBlock(next_block_id, 'GraduatedDebtors'); }
		
		// 2 Следующие записи будут переведены из "прошедших обучение" в активное и продлены
		if(next_block_id == 'GraduatedDebtors')		{ importBlock(next_block_id, 'GraduatedDebtorsEnded'); }
		
		// 3 В следующих записях будет обновлена итоговая оценка на оценку по урокам в СДО. Студент будет повторно назначен на курс из "прошедших обучение". Продлены эти записи не будут
		if(next_block_id == 'GraduatedDebtorsEnded'){ importBlock(next_block_id, 'TutorsAssignSubjects'); }
		
		// 4 Назначаться тьюторы на сессии
		if(next_block_id == 'TutorsAssignSubjects')	{ importBlock(next_block_id, 'TutorsAssignGroups'); }
		
		// 5 Назначаться тьюторы на группы
		if(next_block_id == 'TutorsAssignGroups')	{ importBlock(next_block_id, 'TutorsUpdateSubjects'); }
		
		// 6 Изменятся назначения тьюторов
		if(next_block_id == 'TutorsUpdateSubjects')	{ importBlock(next_block_id, 'setBaseRoleTutors'); }
		
		// 7 Удаление лишних назначений
		if(next_block_id == 'setBaseRoleTutors')	{ importBlock(next_block_id, 'removeLessonAssignTutors'); }
		
		// 8 Удаление всех закреплений на занятия указанных в csv тьюооров
		if(next_block_id == 'removeLessonAssignTutors')	{ importBlock(next_block_id, ''); }
		
		if(next_block_id == '')	{ $('#area-all-progress').append('<hr /><p>Импорт завершен</p>');  }
		
		
	});
	
	<? # Импортирует последовательно каждый из элементов импорта. ?>	
	<? # Порядок продления
		#1. Будут обновлены следующие записи
		#2. Следующие записи будут переведены из "прошедших обучение" в активное и продлены
		#3. В следующих записях будет обновлена итоговая оценка на оценку по урокам в СДО. Студент будет повторно назначен на курс из "прошедших обучение". Продлены эти записи не будут
		#4. Назначаться тьюторы на сессии	
		#5. Назначаться тьюторы на группы
		#6. Изменятся назначения тьюторов		
	?>
	function importAll(){
		$('#btn-import-all').css( 'pointer-events', 'none' ).find('button').prop( "disabled", true );
		$('#area-all-progress').append('<p>Прогресс</p>');
		
		// инициация импорта
		$('#next-block').val('UpdateData');
		$('#next-block').trigger('change');
	}
	</script>
	
	
	
	
	
	
<?php endif;?>	
	
	
	
	
	
	
	
	


