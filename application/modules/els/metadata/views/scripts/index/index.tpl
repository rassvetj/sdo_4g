<?php if (!$this->gridAjaxRequest):?>
    <?php echo $this->addButton($this->url(array('action' => 'new', 'controller' => 'index', 'module' => 'metadata')), _('создать группу полей'))?>
<?php endif;?>
<?php echo $this->grid?>