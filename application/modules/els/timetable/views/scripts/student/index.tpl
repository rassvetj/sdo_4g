<?php
if($this->groupCode == '13623'){
	echo $this->render('student/partial/distance.tpl');
	return;
}
?>
<style>
	.tt-table-area {
		max-width:50%;
		float: left;
	}
	.timetable-criteria-area select{
		width: 250px;
	}
	
	.timetable-criteria-area{
		font-size: 14px;
	}
	.not-data-area{
		font-size: 13px;
		text-align:center;
		font-weight: bold;
	}
</style>

<div class="_grid_gridswitcher timetable-gridswitcher">		
		<?php if($this->show_next_week):?>
			<a href="<?=$this->url(array('module' => 'timetable', 'controller' => 'student', 'action' => 'index'), 'default', true);?>">
				<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending">
					<?= _('Текущая неделя') ?>
				</div>
			</a>
			<div class="ending _u_selected">
				<?= _('Следующая неделя') ?>
			</div>
		<?php else:?>
			<div class="ending _u_selected">
				<?= _('Текущая неделя') ?>
			</div>
			<a href="<?=$this->url(array('module' => 'timetable', 'controller' => 'student', 'action' => 'index', 'week' => 'next'));?>">
				<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending">
					<?= _('Следующая неделя') ?>
				</div>
			</a>
		<?php endif;?>			
</div>
<div style="clear:both"></div>


<div class="timetable-criteria-area">
	<?#=$this->form?>
</div>
<br />
<script>
	$( document ).ready(function() {
		$('.timetable-criteria-area [name="group"]').select2();
		$('.timetable-criteria-area [name="group"]').change(function(){
			$(this).closest('form').submit();
		});
	});
	
</script>

<?php if(empty($this->timetable)): ?>
	<p class="not-data-area"><?=_('Нет данных')?></p>
<?php else:?>
	<?php if($this->has_odd):?>
		<div class="tt-table-area">
			<?php $this->current_even_odd_id = HM_Timetable_TimetableModel::TYPE_ODD; ?>
			<?=$this->render('/student/partial/table.tpl');?>
		</div>
	<?php endif;?>
	<?php if($this->has_even):?>
		<div class="tt-table-area">
			<?php $this->current_even_odd_id = HM_Timetable_TimetableModel::TYPE_EVEN; ?>
			<?=$this->render('/student/partial/table.tpl');?>
		</div>
	<?php endif;?>
	<div style="clear:both;">
	<br />
<?php endif;?>
