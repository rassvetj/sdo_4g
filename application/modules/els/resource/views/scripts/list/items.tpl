<?php if (!$this->gridAjaxRequest):?>
    <?php echo $this->formButton('cancel', _('Назад'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'course', 'controller' => 'structure', 'action' => 'index', 'subject_id' => $this->subjectId, 'course_id' => $this->courseId, 'key' => $this->key), null, true)).'"'))?>
    <br/>
    <br/>
<?php endif;?>

<?php echo $this->partial(
    'list/index.tpl',
    array(
        'gridAjaxRequest' => $this->gridAjaxRequest,
        'grid' => $this->grid,
        'subjectId' => $this->subjectId
))?>