<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/news.css');?>
<div class="news-side-bar">
	‹ <a href="<?php echo $this->url(array('news_id' => $this->news->id, 'step' => -1))?>"><?php echo _('Предыдущая')?></a> |
	<a href="<?php echo $this->url(array('news_id' => null, 'step' => null, 'action' => 'index'))?>"><?php echo _('Список новостей')?></a> | 
	<a href="<?php echo $this->url(array('news_id' => $this->news->id, 'step' => 1))?>"><?php echo _('Следующая')?></a> ›
	</div>
	<div class="spacer">
</div>
	
<?php if ($this->news):?>
	
    <?php echo $this->newsPreview($this->news, 1)?>
    
    <div class="spacer"></div>
	<div class="news-side-bar">
	‹ <a href="<?php echo $this->url(array('news_id' => $this->news->id, 'step' => -1))?>"><?php echo _('Предыдущая')?></a> |
	<a href="<?php echo $this->url(array('news_id' => null, 'step' => null, 'action' => 'index'))?>"><?php echo _('Список новостей')?></a> | 
	<a href="<?php echo $this->url(array('news_id' => $this->news->id, 'step' => 1))?>"><?php echo _('Следующая')?></a> ›
	</div>
<?php else:?>
    <?php echo _('Новость не найдена')?>
<?php endif;?>