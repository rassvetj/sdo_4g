


<?php if (!$this->gridAjaxRequest):?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:webinar:list:new')):?>
    <?php echo $this->Actions('webinars', array(array('title' => _('Создать материалы вебинара'), 
                                                      'url'   => $this->url(array('action' => 'new'))
                                                )
                                          )
               );
    ?>
                
                
    <?php endif;?>
<?php endif;?>
<?php echo $this->grid?>