<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/news.css');?>
<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/rgsu_style.css');?>

<?php if($this->isTutor):?>
	<span class="subject-info-caption"><?=_('Группы')?>:</span><?=$this->groups?>
	<br />
	<br />
<?php endif;?>

<?php if (!$this->gridAjaxRequest):?>
    <?php if ($this->isModerator):?>	
        <?php
            echo $this->ViewType('actions', array(
                'url' => $this->url(array('module' => 'news', 'controller' => 'index', 'action' => 'index', 'subject' => $this->subjectName, 'subject_id' => $this->subjectId), null, true)
            ));
        ?>
            <?php echo $this->Actions('news', array(
            array(
                'title' => _('Создать новость'), 
                'url' => $this->url(array('module' => 'news', 'controller' => 'index', 'action' => 'new', 'subject' => $this->subjectName, 'subject_id' => $this->subjectId), null, true)
            )
            ), null);?>
    <?php endif;?>
<?php endif;?>

<?php if ($this->isModerator):?>
<div>
	<button
		data-action="<?=$this->url(array('module' => 'news', 'controller' => 'report', 'action' => 'check', 'subject_id' => $this->subjectId), null, true)?>"
		data-container=".nrc-area"
		onClick="checkNews($(this)); return false;"
	><?=_('Проверить')?></button>
	<div class="nrc-area">
	</div>
</div>
<?php endif;?>

<?php if ($this->viewType != 'table'):?><!--
    <div class="news-header">
        <div class="header-center">
            <div class="header-left"></div>
            <div class="header-title"><?php echo $this->title?></div>
            <div class="header-right"></div>
        </div>
    </div>
    --><div class="news-middle">
        <div class="news-list">
            <?php if(count($this->news) > 0):?>
            <?php foreach($this->news as $item) :?>
                <?php echo $this->newsPreview($item);?>
            <?php endforeach;?>
            <?php /*paginator*/ echo $this->news?>
            <?php else:?>
            <?php echo _('Нет данных для отображения');?>
            <?php endif;?>
        </div>
    </div>
<?php else: ?>
	<?php echo $this->grid?>
<?php endif;?>