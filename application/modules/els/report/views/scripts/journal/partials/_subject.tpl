<div class="report-journal-subject-item">
	Сессия: 
	<a target="_blank" href="<?=$this->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $this->subject->subid))?>">
		<?=$this->subject->name?>		
	</a>&nbsp;<?=$this->subject->external_id ? '(' . $this->subject->external_id . ')' : ''?>

	<?php if(!$this->subject->lessons):?>
		<p><?=_('Нет занятий с типом "Журнал"')?></p>
	<?php else:?>
		<?php foreach($this->subject->lessons as $lesson):?>
			<?php $this->lesson = $lesson; ?>
			<?=$this->render('journal/partials/_lesson.tpl')?>
		<?php endforeach;?>
	<?php endif;?>
</div>