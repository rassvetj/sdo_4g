<?php if (!$this->gridAjaxRequest):?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:provider:list:new')):?>
        <?php echo $this->Actions('provider');?>
    <?php endif;?>
<?php endif;?>
<?php echo $this->grid?>