<style>
		.marksheets-info-area td, .marksheets-info-area th{
			border: 1px solid #c5d0d7;
			padding: 5px;
		}
		.marksheets-info-area table{
			border-collapse: collapse;    
		}
		
		

		.accordion-container{
			font-size:17px;
			text-align: justify;
			border: 1px solid #fdfdfd;
			margin-bottom: 10px;	
			padding: 0px;
			padding-top: 10px;
		}

		.accordion-header a{
			margin: 0 !important;
			padding: 10px;
			font-size: 15px;
			background: #f9f9f9;
			display: block;
			padding-right: 30px;
			position: relative;	
			border-bottom: none;
			color: #3d3d3d;
			text-decoration: none;
		}

		.accordion-container.open .accordion-header a{
			background: #effaff;			
		}
		
		.accordion-header{
			max-width: 365px;
		}

		.accordion-header a::after{
			content: '';
			position: absolute;
			right: 20px;
			top: 20px;
			border: 5px solid transparent;
			border-top: 5px solid #ccc;
		}

		.accordion-container.open .accordion-header a::after{
			content: '';
			position: absolute;
			right: 20px;
			top: 15px;
			border: 5px solid transparent;
			border-bottom: 5px solid #3467A0;
		}

		.accordion-container.open .accordion-data-new{
			display:block;
		}

		.accordion-container .accordion-data-new {
			display:none;
			font-size: 12px;
			padding: 10px;
		}
		
		.marksheets-info-area .marksheets-errors {
			color: red;
			text-align: center;
			font-size: 12px;
		}
</style>
<div class="marksheets-info-area">
	<?php if(empty($this->marksheets)):?>
		<div class="marksheets-errors"><?=_('Для завершения курса необходим номер ведомости - обратитесь в деканат')?> <a href="mailto:dekanat@rgsu.net">dekanat@rgsu.net</a></div>		
	<?php else: ?>
		<div class="accordion-container">
			<div class="accordion-header">
				<a href="#" class="btn-accordion"><?=_('Дополнительная информация по ведомости')?></a>
			</div>
			<div class="accordion-data-new">
				<table>
					<tr>
						<th>№</th>
						<th><?=_('Факультет')?></th>
						<th><?=_('Основа обучения')?></th>
						<th><?=_('Семестр')?></th>
						<th><?=_('Курс')?></th>
						<th><?=_('Год')?></th>
						<th><?=_('Декан')?></th>
						<th><?=_('Номер попытки')?></th>
						<th><?=_('Контроль')?></th>
						<th><?=_('Преподаватель')?></th>
						<th><?=_('Форма обучения')?></th>
						<th><?=_('Группа')?></th>
						<th><?=_('Дисциплина')?></th>
						<th><?=_('Студенты')?></th>
					</tr>
					<?php foreach($this->marksheets as $i):?>
					<tr>
						<td><?=$i->external_id?></td>
						<td><?=$i->faculty?></td>
						<td><?=$i->study_base?></td>
						<td><?=$i->semester?></td>
						<td><?=$i->course?></td>
						<td><?=$i->year?></td>
						<td><?=$i->dean?></td>
						<td><?=$i->attempt?></td>
						<td><?=$i->form_control?></td>
						<td><?=$i->tutor?></td>
						<td><?=$i->form_study?></td>
						<td><?=$i->group?></td>
						<td><?=$i->discipline?></td>
						<td>
							<ol>
							<?php foreach($i->students as $fio):?>
								<li><?=$fio?></li>
							<?php endforeach;?>
							</ol>
						</td>
					</tr>
					<?php endforeach;?>
				</table>
			</div>
			<script>
				$( document ).ready(function() {
					$('body').on('click', '.btn-accordion', function(event) {
						event.preventDefault();
						var container = $(this).closest('.accordion-container');
						if ( container.hasClass('open')){
							container.removeClass('open');
						} else {
							container.addClass('open');
						}
					});
				});
			</script>
		</div>
		<?php endif;?>	
	
</div>
<br />

<?php if($this->isAllowBringTrained) : ?>

	<?php echo $this->markSheetTable($this->score[0],
									$this->score[1],
									$this->score[2],
									NULL,
									'page',
									$this->subjectId
									#$this->additional
								);?>

<?php else : ?>

	<?php echo $this->markSheetTableTutor($this->score[0],
									$this->score[1],
									$this->score[2],
									$this->page,
									$this->subjectId,
									$this->score[3], 	# Итоговый текущий рейтинг
									$this->score[4],  # Рубежный рейтинг
									$this->additional
								);?>

<?php endif; ?>

<?php if(!$this->isSelectedGroup): ?>	
	<?php $this->inlineScript()->captureStart(); ?>
		btn = $('.btn-grad-vedomost-all');		
		btn.prop('disabled', true).html(btn.text()+' <span class="marksheet-tooltip-text">Выберите группу</span>');			
	<?php $this->inlineScript()->captureEnd(); ?>
<?php else: ?>
	<?php if(!$this->has_marksheet && !$this->is_manager): ?>	
		<?php $this->inlineScript()->captureStart(); ?>
			btn = $('.btn-grad-vedomost-all');		
			btn.prop('disabled', true).html(btn.text()+' <span class="marksheet-tooltip-text">Нет номера ведомости <br />для выбранной группы</span>');			
		<?php $this->inlineScript()->captureEnd(); ?>
	<?php endif; ?>
<?php endif; ?>

<?php if($this->isShowBtnBlockedTask): ?>
	<?php if(empty($this->blockedTaskGroups)):?>
		<p><?=_('Нет доступных групп для завершения приема ПЗ и РК')?></p>
	<?php else: ?>
		<span>
			<form method="POST" name="form-blocked-task" id="form-blocked-task" action="<?=$this->url(array('module' => 'marksheet', 'controller' => 'set', 'action' => 'blocked-task'));?>">
				<select name="group_id" style="width: 200px; height: 18px;">
				<?php
				if(count($this->blockedTaskGroups) > 1){
					echo '<option name="group_id" value="">-- выберите группу --</option>';	
				}
				foreach($this->blockedTaskGroups as $id => $name){
					echo '<option name="group_id" value="'.$id.'">'.$name.'</option>';	
				} 
				?>
				</select>
				<button class="dateFilter btn-blocked-task"><?=_('Завершение приема ПЗ и РК')?></button>
			</form>
		</span>

		<div id="dialog-blocked-task" title="Завершить" data-url="" >
			<p>
				<span style="float: left;">
					<?=_('Вы уверены, что хотите заблокировать возможность прикрепления работ для студентов выбранной группы?')?>
					<br />
					<br />
					<?=_('Выставится оценка «0» (неявка).')?>
				</span>
			</p>
		</div>

		<?php $this->inlineScript()->captureStart(); ?>
			$('[name="group_id"]').change(function() {
				if($('#form-blocked-task [name="group_id"]:selected').val() > 0){
					$('.btn-blocked-task').prop( "disabled", false );
				} else {
					$('.btn-blocked-task').prop( "disabled", true );
				}				
			});
			
			$( '[name="group_id"]').trigger( "change" );	
		
		
			$( ".btn-blocked-task" ).click(function() {	
				$( "#dialog-blocked-task" ).dialog( "open" );	
				return false;	
			});


			$( "#dialog-blocked-task" ).dialog({
				resizable: false,
				autoOpen: false,
				height:180,
				modal: true,
				buttons:
				{
					<?php echo _('Да')?>: function() {
						$( this ).dialog( "close" );				
						//console.log(	'Отправляем форму'	);
						$('#form-blocked-task').submit();
					},
					<?php echo _('Нет')?>: function() {
						$( this ).dialog( "close" );
						//console.log(	'Ничего не делаем'	);
					}
				}
			});

		<?php $this->inlineScript()->captureEnd(); ?>
	<?php endif; ?>
<?php endif; ?>










<?php if(!empty($this->files_marksheet)):?>
	<div style="float: right; padding: 10px;">
		<p><?=_('Архив ведомостей успеваемости:')?></p>
		<ol>
		<?php foreach($this->files_marksheet as $file_id => $file_name):?>
			<li><a href="
			<?=$this->url(array('module' => 'marksheet', 'controller' => 'get', 'action' => 'index', 'file_id' => $file_id), 'default', true);?>
			
			" target="_blank"><?=$file_name;?></a></li>
		<?php endforeach; ?>
		</ol>
	</div>
<?php endif; ?>