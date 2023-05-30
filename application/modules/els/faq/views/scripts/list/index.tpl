<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/faq.css');?>
<?php if (!$this->gridAjaxRequest):?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:faq:list:new')):?>
    <?php if($this->isModerator): ?>
        <?php echo $this->ViewType('actions', array(
                    'url' => $this->url(array('module' => 'faq', 'controller' => 'list', 'action' => 'index'), null, true)
                ));
        ?>
    <?php endif; ?>
    <?php echo $this->Actions('faq', array( array('title' => _('Создать вопрос'), 'url' => $this->url(array('module' => 'faq', 'controller' => 'list', 'action' => 'new')))));?>
    <?php endif;?>
<?php endif;?>
<?php if ($this->viewType != 'table'):?>
    <div class="faq-middle">
        <div class="faq-list">
            <?php if(count($this->faq) > 0):?>
            <?php foreach($this->faq as $item) :?>
                <?php echo $this->faqPreview($item);?>
            <?php endforeach;?>
            <?php /*paginator*/ echo $this->faq?>
            <?php else:?>
            <?php echo _('Нет данных для отображения');?>
            <?php endif;?>
        </div>
		<div class="clearfix"></div>
    </div>
<?php else: ?>
    <?php echo $this->grid;?>
<?php endif; ?>