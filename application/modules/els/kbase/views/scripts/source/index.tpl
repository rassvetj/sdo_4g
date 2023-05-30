<?php if ($this->gridAjaxRequest): ?>
    <?php echo $this->grid?>
<?php else:?>
    <?php echo $this->actions('source')?>
    <?php echo $this->grid?>
<?php endif;?>