<div class="wiki-side-bar">
    <div class="menu-bar">
        <div class="label lwiki">Wiki</div>
        <div class="spacer"></div>       
        <a href="<?php echo $this->url(array(
                'module' => 'wiki',
                'controller' => 'index',
                'action' => 'view',
                'subject' => $this->subjectName, 
                'subject_id' => $this->subjectId,
                'title' => 'Главная страница' // @todo: сделать константу
            ), null, true)?>">
            <? echo _('Главная страница') ?>
        </a><br/>
        <a href="<?php echo $this->url(array(
                'module' => 'wiki',
                'controller' => 'index',
                'action' => 'content',
                'subject' => $this->subjectName, 
                'subject_id' => $this->subjectId
            ), null, true)?>">
            <? echo _('Оглавление') ?>
        </a><br/>
        <a href="<?php echo $this->url(array(
                'module' => 'wiki',
                'controller' => 'index',
                'action' => 'history',
                'subject' => $this->subjectName, 
                'subject_id' => $this->subjectId
            ))?>">
            <? echo _('Общая история изменений') ?>
        </a>
    </div>
    <?php echo $this->wikiHistory('wikiHis');?>
</div>
