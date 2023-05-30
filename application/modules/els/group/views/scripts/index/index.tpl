<?php if (!$this->gridAjaxRequest):?>
    <?php // echo $this->addButton($this->url(array('action' => 'new', 'controller' => 'index', 'module' => 'group')), _('создать учебную группу'))?>
    <?php echo $this->Actions('group',array(array('title' => _('Создать подгруппу'), 'url' => 'group/index/new/subject_id/' . (int) $this->subjectId, 'manual' => true)));?>
<?php endif;?>
<?php echo $this->grid;
?>