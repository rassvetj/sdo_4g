<?php if (!$this->gridAjaxRequest):?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:user:list:new')):?>
        <?php echo $this->Actions('users');?>
                
                
    <?php endif;?>
	<span><?=_('Для вывода данных нажмите кнопку "Искать"')?></span>
<?php endif;?>
<?php echo $this->grid?>
<?php $this->inlineScript()->captureStart(); ?>
    jQuery(document).ready(function(){
        jQuery('#_fdiv [multiple]').attr('size','1');
        jQuery('#_fdiv [multiple]').removeAttr('multiple');
    });
<?php $this->inlineScript()->captureEnd(); ?>