<?php if (!$this->gridAjaxRequest):?>
	<span><?=_('Для вывода данных нажмите кнопку "Искать"')?></span>
<?php endif;?>
<?php echo $this->grid;?>
<?php if (!$this->gridAjaxRequest):?>
<?php echo $this->footnote();?>
<?php endif;?>
<?php $this->inlineScript()->captureStart(); ?>
    jQuery(document).ready(function(){
        jQuery('#_fdiv [multiple]').attr('size','1');
        jQuery('#_fdiv [multiple]').removeAttr('multiple');
    });
<?php $this->inlineScript()->captureEnd(); ?>
