<?php if (!$this->isGridAjaxRequest):?>
    <?php echo $this->formButton('cancel', _('Назад'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'course', 'controller' => 'structure', 'action' => 'index', 'subject_id' => $this->subjectId, 'course_id' => $this->courseId, 'key' => $this->key), null, true)).'"'))?>
<?php endif;?>
<?php echo $this->grid?>