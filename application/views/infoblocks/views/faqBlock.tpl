<?php if ($this->faqs && count($this->faqs)):?>
    <div name="faq-block-accordion" id="faq-block-accordion">
    <?php foreach($this->faqs as $faq):?>
        <h3><a href="#"><?php echo $faq->question?></a></h3>
        <div><?php echo $faq->answer?></div>
    <?php endforeach;?>
    </div>
    <?php $this->inlineScript()->captureStart()?>
    $(function () {
        $('#faq-block-accordion').accordion({active: false, autoHeight: false, collapsible: true});
    });
    <?php $this->inlineScript()->captureEnd()?>
<?php else:?>
    <div align="center"><?php echo _('Отсутствуют данные для отображения')?></div>
<?php endif;?>
    <div class="bottom-links">
    	<hr />
        <a href="<?php echo $this->baseUrl($this->url(array('module' => 'faq', 'controller' => 'list', 'action' => 'index')))?>"><?php echo _('Все вопросы')?></a>
    </div>
