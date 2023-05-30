<?php if ($this->article && !$this->article->lesson_id && $this->isModerator):?>
<?php echo $this->ViewType('wikivt', array(
    'url' => $this->url(array('module' => 'wiki', 'controller' => 'index', 'action' => 'content', 'subject' => $this->subjectName, 'subject_id' => $this->subjectId), null, true)
));?>
<?php endif;?>
<div class="wiki">
    <div class="wiki-article">
        <div class="article-header">
            <?php /*<div class="article-title"> <?php echo $this->article->title?></div>*/?>
            <div class="article-controls">
                <?php if($this->isModerator):?>
                <a class="edit" href="<?php echo $this->url(array(
                    'module' => 'wiki',
                    'controller' => 'index',
                    'action' => 'edit',
                    'subject' => $this->subjectName, 
                    'subject_id' => $this->subjectId,
                    'id' => $this->article->id
                ))?>"></a>
                <a class="delete" href="<?php echo $this->url(array(
                    'module' => 'wiki',
                    'controller' => 'index',
                    'action' => 'delete',
                    'subject' => $this->subjectName, 
                    'subject_id' => $this->subjectId,
                    'id' => $this->article->id
                ))?>"></a>
                <?php endif;?>
            </div>
        </div>
        <div class="article-body">
            <div class="article-compare" style="border-right: 1px dotted">
                <ul class="article-author">
                    <li class="avatar"><img src="<?php echo $this->ver1->author_avatar?>"/></li>
                    <li class="name"><?php echo trim($this->ver1->author->getName())?>,</li>
                    <li class="date"><?php echo date('d.m.Y, H:i', strtotime($this->ver1->created))?></li>
                </ul>
            <?php echo $this->ver1->body;?>
            </div>
            <div class="article-compare">
                <ul class="article-author">
                    <li class="avatar"><img src="<?php echo $this->ver2->author_avatar?>"/></li>
                    <li class="name"><?php echo trim($this->ver2->author->getName())?>,</li>
                    <li class="date"><?php echo date('d.m.Y, H:i', strtotime($this->ver2->created))?></li>
                </ul>
            <?php echo $this->ver2->body;?>
            </div>
            <div class="spacer"></div>
        </div>
        <div class="spacer"></div>
    </div>
    <?php include 'sidebar.tpl';?>
</div>