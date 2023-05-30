<?php if ($this->courseId > 0 && $this->key >= 0):?>
    <?php echo $this->formButton('cancel', _('Назад'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'course', 'controller' => 'structure', 'action' => 'index', 'subject_id' => $this->subjectId, 'course_id' => $this->courseId, 'key' => $this->key), null, true)).'"'))?>
<?php endif;?>

<?php if ($this->subjectId): ?>
<?php echo $this->headSwitcher(array(
            'module' => 'resource',
            'controller' => 'index',
            'action' => 'edit-content',
            'switcher' => 'edit-content',
            'subject_id' => $this->subjectId
));?>
<?php endif;?>

<?php echo $this->form?>