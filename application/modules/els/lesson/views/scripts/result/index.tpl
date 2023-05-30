<?php if ($this->content):?>
	<?php echo $this->content?>
<?php endif;?>
<?php if($this->grid):?>
    <?php if (!$this->gridAjaxRequest):?>
        <?php if ($this->allowBack):?>
            <?php echo $this->formButton('cancel', _('Назад'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'lesson', 'controller' => 'result', 'action' => 'index', 'subject_id' => $this->subjectId, 'lesson_id' => $this->lessonId), null, true)).'"'))?>
        <?php endif; ?>
    	<?php echo $this->headSwitcher(array('module' => 'lesson', 'controller' => 'result', 'action' => 'index', 'switcher' => 'index'), 'result', $this->disabledMods);?>
    <?php endif; ?>
    <?php echo $this->grid?>
<?php endif;?>