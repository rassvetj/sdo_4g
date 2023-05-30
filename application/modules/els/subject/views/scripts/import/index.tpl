<?php if ($this->form):?>
<?php echo $this->form?>
<?else:?>
    <p><?php echo sprintf(_('Будут добавлены %d записи(ей) и обновлены %d записи(ей)'), $this->importManager->getInsertsCount(), $this->importManager->getUpdatesCount())?></p>
    <br/>
	<?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'index', 'base'=>HM_Subject_SubjectModel::BASETYPE_SESSION))).'"'))?>
    <?php echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'subject', 'controller' => 'import', 'action' => 'process', 'source' => $this->source))).'"'))?>
     <br/>
	    
	<?php if ($this->importManager->getNotFoundTutorSessionsCount()):?>
		<div class="import-group-container">
			<?php $count = 1;?>
			<h3><?php echo _('Следующие сессии не будут добавлены или изменены, т.к. отсутствует связанный с ней тьютор в СДО')?>:</h3>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<br/>
			<div class="import-data" style="display: none;">
				<table class="main" width="100%">
				<tr>
					<th><?php echo _('Название')?></th>
					<th><?php echo _('Идентификатор сессии')?></th>
					<th><?php echo _('Идентификатор тьютора')?></th>
				</tr>
				<?php foreach($this->importManager->getNotFoundTutorSessions() as $s):?>
					<?php if ($count >= 1000) { echo "<tr><td>...</td></tr>"; break;}?>
					<tr>
						<td><?php echo $s->name?></td>
						<td><?php echo $s->external_id?></td>
						<td><?=(!empty($s->not_found_guid) ? $s->not_found_guid.' ('.$s->not_found_guid_type.')' : $s->tutor_laboratory)?></td>
					</tr>
					<?php $count++;?>
				<?php endforeach;?>
				</table>
			</div>
			<br/> 
		</div>
    <?php endif;?>
   
	
	
	<?php if ($this->importManager->getNotFoundGroupSessionsCount()):?>
		<div class="import-group-container">
			<?php $count = 1;?>
			<h3><?php echo _('Следующие тьюторы не будут назначены на группу, т.к. группа не найдена в СДО')?>:</h3>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<br/>
			<div class="import-data" style="display: none;">
				<table class="main" width="100%">
				<tr>
					
					<th><?php echo _('ID тьютора')?></th>
					<th><?php echo _('ФИО')?></th>
					<th><?php echo _('ID группы')?></th>
					<th><?php echo _('Название сессии')?></th>
					<th><?php echo _('ID сессии')?></th>
				</tr>
				<?php foreach($this->importManager->getNotFoundGroupSessions() as $s):?>
					<?php if ($count >= 1000) { echo "<tr><td>...</td></tr>"; break;}?>
					<tr>                
						<td><?php echo $s['source']->teacher_id_external?></td>
						<td><?php echo $s['additional']['fio']?></td>
						<td><?php echo $s['source']->group_external_id?></td>
						<td><?php echo $s['source']->name?></td>
						<td><?php echo $s['source']->external_id?></td>
					</tr>
					<?php $count++;?>
				<?php endforeach;?>
				</table>
			</div>
			<br/>
		</div>
    <?php endif;?>
    
	
	
	<?php if ($this->importManager->getUpdateAssignSessionsCount()):?>
		<div class="import-group-container">
			<?php $count = 1;?>
			<h3><?php echo _('Следующие тьюторы будут назначены на сессии')?>:</h3>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<br/>
			<div class="import-data" style="display: none;">
				<table class="main" width="100%">
				<tr>            
					<th>№</th>
					<th><?php echo _('ID тьютора')?></th>
					<th><?php echo _('ФИО')?></th>            
					<th><?php echo _('ID группы')?></th>			
					<th><?php echo _('Название сессии')?></th>            			
					<th><?php echo _('ID сессии')?></th>
				</tr>
				<?php foreach($this->importManager->getUpdateAssignSessions() as $s):?>
					<?php if ($count >= 2000) { echo "<tr><td>...</td></tr>"; break;}?>
					<tr> 
						<td><?=$count;?></td>
						<td><?php echo $s['tutor_id_external']?></td>
						<td><?php echo $s['tutor_fio']?></td>
						<td><?php echo $s['group_external_id']?></td>                
						<td><?php echo $s['subject_name']?></td>	
						<td><?php echo $s['subject_external_id']?></td>								                				                				
					</tr>
					<?php $count++;?>
				<?php endforeach;?>
				</table>
			</div>
			<br/>
		</div>
    <?php endif;?>
   

	
	 
    <?php if ($this->importManager->getInsertsCount()):?>
		<div class="import-group-container">
			<?php $count = 1;?>
			<h3><?php echo _('Следующие записи будут добавлены')?>:</h3>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<br/>
			<div class="import-data">
				<table class="main" width="100%">
				<tr>
					<th><?php echo _('Название')?></th>
					<th><?php echo _('Идентификатор сессии')?></th>
				</tr>
				<?php foreach($this->importManager->getInserts() as $insert):?>
					<?php if ($count >= 1000) { echo "<tr><td>...</td></tr>"; break;}?>
					<tr>
						<td><?php echo $insert->name?></td>
						<td><?php echo $insert->external_id?></td>
					</tr>
					<?php $count++;?>
				<?php endforeach;?>
				</table>
			</div>
			<br/>
		</div>
    <?php endif;?> 
	
    <?php if ($this->importManager->getNotFoundCount()):?>
		<div class="import-group-container">			
			<?php $count = 1;?>
			<h3><?php echo _('Следующие записи не будут добавлены, т.к. для них отсутствует соответствующий учебный предмет или привязка к курсу')?>:</h3>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<br/>
			<div class="import-data" style="display: none;">
				<table class="main" width="100%">
				<tr>
					<th><?php echo _('Название')?></th>
					<th><?php echo _('Идентификатор сессии')?></th>
					<th><?php echo _('ID предмета')?></th>
					<th><?php echo _('Подробнее')?></th>
				</tr>
				<?php foreach($this->importManager->getNotFound() as $insert):?>
					<?php if ($count >= 1000) { echo "<tr><td>...</td></tr>"; break;}?>
					<tr>
						<td><?php echo $insert->name?></td>
						<td><?php echo $insert->external_id?></td>
						<td><?php echo $insert->learning_subject_id_external?></td>
						<td><?php 
							if($insert->notFoundLearningsubject){
								echo 'не найден учебный предмет';
							}
							if($insert->notAssignBaseSubject){
								#echo 'учебный предмет не связан с базовым курсом';
								echo $insert->learning_subject_comment;
							}							
						?></td>						
					</tr>
					<?php $count++;?>
				<?php endforeach;?>
				</table>
			</div>
			<br/>
		</div>
    <?php endif;?>
	
	<?php if ($this->importManager->getNotFoundLearningsubjectsCount()):?>
		<div class="import-group-container">			
			<?php $count = 1;?>
			<h3><?php echo _('Следующие записи не будут добавлены, т.к. для них отсутствует соответствующий учебный предмет')?>:</h3>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<br/>
			<div class="import-data" style="display: none;">
				<table class="main" width="100%">
				<tr>
					<th><?php echo _('Название')?></th>
					<th><?php echo _('Идентификатор сессии')?></th>
					<th><?php echo _('ID предмета')?></th>
					<th><?php echo _('Подробнее')?></th>
				</tr>
				<?php foreach($this->importManager->getNotFoundLearningsubjects() as $insert):?>
					<?php if ($count >= 1000) { echo "<tr><td>...</td></tr>"; break;}?>
					<tr>
						<td><?php echo $insert->name?></td>
						<td><?php echo $insert->external_id?></td>
						<td><?php echo $insert->learning_subject_id_external?></td>
						<td><?php 
							if($insert->notFoundLearningsubject){
								echo 'не найден учебный предмет';
							}
							if($insert->notAssignBaseSubject){
								#echo 'учебный предмет не связан с базовым курсом';
								echo $insert->learning_subject_comment;
							}							
						?></td>						
					</tr>
					<?php $count++;?>
				<?php endforeach;?>
				</table>
			</div>
			<br/>
		</div>
    <?php endif;?>
	
	<?php if ($this->importManager->getNotLinkLearningsubjectsCount()):?>
		<div class="import-group-container">			
			<?php $count = 1;?>
			<h3><?php echo _('Следующие записи не будут добавлены, т.к. для них отсутствует привязка к курсу')?>:</h3>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<br/>
			<div class="import-data" style="display: none;">
				<table class="main" width="100%">
				<tr>
					<th><?php echo _('Название')?></th>
					<th><?php echo _('Идентификатор сессии')?></th>
					<th><?php echo _('ID предмета')?></th>
					<th><?php echo _('Подробнее')?></th>
				</tr>
				<?php foreach($this->importManager->getNotLinkLearningsubjects() as $insert):?>
					<?php if ($count >= 1000) { echo "<tr><td>...</td></tr>"; break;}?>
					<tr>
						<td><?php echo $insert->name?></td>
						<td><?php echo $insert->external_id?></td>
						<td><?php echo $insert->learning_subject_id_external?></td>
						<td><?php 
							if($insert->notFoundLearningsubject){
								echo 'не найден учебный предмет';
							}
							if($insert->notAssignBaseSubject){
								#echo 'учебный предмет не связан с базовым курсом';
								echo $insert->learning_subject_comment;
							}							
						?></td>						
					</tr>
					<?php $count++;?>
				<?php endforeach;?>
				</table>
			</div>
			<br/>
		</div>
    <?php endif;?>
   
	 <?php if ($this->importManager->getNotFoundProgrammCount()):?>
		<div class="import-group-container">
			<?php $count = 1;?>
			<h3><?php echo _('Следующие записи не будут добавлены, т.к. для них отсутствует программа обучения')?>:</h3>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<br/>
			<div class="import-data" style="display: none;">
				<table class="main" width="100%">
				<tr>
					<th><?php echo _('Название')?></th>
					<th><?php echo _('Идентификатор сессии')?></th>					
				</tr>
				<?php foreach($this->importManager->getNotFoundProgramm() as $insert):?>
					<?php if ($count >= 1000) { echo "<tr><td>...</td></tr>"; break;}?>
					<tr>
						<td><?php echo $insert->name?></td>
						<td><?php echo $insert->external_id?></td>						
					</tr>
					<?php $count++;?>
				<?php endforeach;?>
				</table>
			</div>
			<br/>
		</div>
    <?php endif;?>
   
    <?php if ($this->importManager->getUpdatesCount()):?>
		<div class="import-group-container">
			<?php $count = 1;?>
			<h3><?php echo _('Будут обновлены следующие записи')?>:</h3>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<br/>
			<div class="import-data" style="display: none;">
				<table class="main" width="100%">
				<tr>
					<th><?=_('ID сессии из 1С')?></th>
					<th><?=_('Было')?></th>
					<th><?=_('Стало')?></th>
				</tr>
				<?php foreach($this->importManager->getUpdates() as $update):?>			
					<?php if ($count >= 1000) { echo "<tr><td colspan=\"2\">...</td></tr>"; break;}?>
					<tr>
						<td><?=$update['destination']->external_id?></td>
						<?php if(isset($update['source']->name)) :  ?>
							<td><?=$update['source']->name?></td>
						<?php else : ?>	
							<td><?=$update['source']['name'];?></td>
						<?php endif;?>	
						<td><?=$update['destination']->name?></td>
					</tr>
					<?php $count++;?>
				<?php endforeach;?>
				</table>
			</div>
			<br/>
		</div>
    <?php endif;?>
	
	
	
	
	<?php if ($this->importManager->getNotHoursCount()):?>
		<div class="import-group-container">
			<?php $count = 1;?>
			<h3><?php echo _('Внимание! В следующих сессиях (неДО) не заданы часы')?>:</h3>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<br/>
			<div class="import-data" style="display: none;">
				<table class="main" width="100%">
				<tr>
					<th><?php echo _('Идентификатор сессии')?></th>            
					<th><?php echo _('Название')?></th>
					<th><?php echo _('Кафедра')?></th>
					<th><?php echo _('Лекционные часы')?></th>
					<th><?php echo _('Лабораторные часы')?></th>
					<th><?php echo _('Практические часы')?></th>
					<th><?php echo _('ЗЕТ')?></th>
					
				</tr>
				<?php foreach($this->importManager->getNotHours() as $s):?>
					<?php if ($count >= 1000) { echo "<tr><td>...</td></tr>"; break;}?>
					<tr>
						<td><?php echo $s->external_id?></td>
						<td><?php echo $s->name?></td>
						<td><?php echo $s->chair?></td>                                
						<td><?php echo $s->lection?></td>                
						<td><?php echo $s->lab?></td>                
						<td><?php echo $s->practice?></td>                
						<td><?php echo $s->zet?></td>                
					</tr>
					<?php $count++;?>
				<?php endforeach;?>
				</table>
			</div>
			<br/> 
		</div>
    <?php endif;?>
	
	
	<?php if ($this->importManager->getNotTaskLessonsCount()):?>
		<div class="import-group-container">
			<?php $count = 1;?>
			<h3><?php echo _('Следующие сессии (неДО) не будут добавлены или изменены, т.к. в базовом курсе отсутствует занятие с типом "Задание" и словом "Задание" в названии')?>:</h3>
			<a href="#" onClick="hideTbl($(this)); return false;">Скрыть/показать</a>
			<br/>
			<div class="import-data" style="display: none;">
				<table class="main" width="100%">
				<tr>
					<th><?php echo _('Идентификатор сессии')?></th>            
					<th><?php echo _('Название')?></th>
					<th><?php echo _('ID базового курса')?></th>
					<th><?php echo _('Название базового курса')?></th>
				</tr>
				<?php foreach($this->importManager->getNotTaskLessons() as $s):?>
					<?php if ($count >= 1000) { echo "<tr><td>...</td></tr>"; break;}?>
					<tr>
						<td><?php echo $s->external_id?></td>
						<td><?php echo $s->name?></td>                
						<td><?php echo $s->base_id?></td>                
						<td><?php echo $s->base_name?></td>                
					</tr>
					<?php $count++;?>
				<?php endforeach;?>
				</table>
			</div>
			<br/> 
		</div>
    <?php endif;?>
	
	
	
	
	<br/>
	 <?php if ($this->importManager->getTutorLectureCount()):?>
		<p><?='Назначения тьюторов на лекционные занятия будут обновлены в '.$this->importManager->getTutorLectureCount().' сесси(ях)'?></p>
        <br/>
	<?php endif;?>
	<?php if ($this->importManager->getTutorLabCount()):?>
		<p><?='Назначения тьюторов на лабораторные занятия будут обновлены в '.$this->importManager->getTutorLabCount().' сесси(ях)'?></p>
        <br/>
	<?php endif;?>
	<?php if ($this->importManager->getTutorPracticeCount()):?>
		<p><?='Назначения тьюторов на практические занятия будут обновлены в '.$this->importManager->getTutorPracticeCount().' сесси(ях)'?></p>
        <br/>
	<?php endif;?>
	
	
	
	
    <?php if ($this->importManager->getUpdatesCount() || $this->importManager->getNotFoundCount()): ?>
        <?php echo $this->formButton('cancel', _('Отмена'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'index', 'base'=>HM_Subject_SubjectModel::BASETYPE_SESSION))).'"'))?>
        <?php #echo $this->formButton('process', _('Далее'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'subject', 'controller' => 'import', 'action' => 'process', 'source' => $this->source))).'"'))?>
    <?php endif;?>
<?php endif;?>
<?php $this->inlineScript()->captureStart()?>
	$( document ).ready(function() {
		var buttons = $('button[name="process"]');
		
		$(buttons).click(function(){
			$(buttons).prop("disabled", true);
		});
	});
	
	function hideTbl(btn){
		var tbl = btn.closest('.import-group-container').find('.import-data');
		console.log(btn);
		console.log(tbl);
		if(tbl.is(":visible")){
			tbl.slideToggle("slow");
		} else {
			tbl.slideToggle("slow");
		}
	}
<?php $this->inlineScript()->captureEnd()?>