<?php if (!$this->gridAjaxRequest && Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:scale:list:new')):?>
    <?php echo $this->actions('scale');?>
<?php endif;?>

<?php echo $this->grid;?>