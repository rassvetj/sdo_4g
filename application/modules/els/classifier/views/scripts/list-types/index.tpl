<?php if ($this->gridAjaxRequest): ?>
    <?php echo $this->grid?>
<?php else:?>
    <?php echo $this->actions('classifier-types')?>
    <?php echo $this->grid?>
<?php endif;?>