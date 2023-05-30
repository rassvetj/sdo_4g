<?php if (!$this->gridAjaxRequest):?>
    <?php echo $this->addButton($this->url(array('action' => 'new', 'controller' => 'index', 'module' => 'room')), _('создать аудиторию'))?>
<?php endif;?>
<?php echo $this->grid?>