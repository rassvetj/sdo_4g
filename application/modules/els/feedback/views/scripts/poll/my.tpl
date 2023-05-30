<?php
$this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/schedule_table.css');
?>
<?php echo $this->headSwitcher(array('module' => 'lesson', 'controller' => 'list', 'action' => 'index', 'switcher' => 'my'));?>

<?php if ($this->markDisplay):?>
	<table class="progress_table" border="0" cellpadding="0" cellspacing="0">
	  <tr>
	    <td height="20" width="470" align="left" valign="middle">
		<div class="progress_title"><?php echo _('Общий прогресс')?></div>
		</td>
	    <td height="20" align="center" valign="middle">
		<div class="progress_title"><?php echo _('Оценка')?></div>
		</td>
	  </tr>
	  <tr>
	    <td class="progress_td" height="48" width="470" align="center" valign="middle">
		<?php echo $this->progress($this->percent, 'xlarge')?>
		</td>
	    <td>
	<?php if ($this->mark):?>
	    <?php echo $this->score($this->mark->mark)?>
	<?php else:?>
	    <?php echo $this->score(-1)?>
	<?php endif;?>	
	  </td>
	  </tr>
	</table>
<?php endif;?>

<?php if (count($this->lessons)):?>
    <?php foreach($this->lessons as $lesson):?>
        <?php if ($lesson instanceof HM_Lesson_LessonModel):?>
        <?php echo $this->lessonPreview($lesson, $this->titles)?>
        <?php endif;?>
    <?php endforeach;?>
<?php else:?>
    <?php echo _('Отсутствуют данные для отображения')?>
<?php endif;?>