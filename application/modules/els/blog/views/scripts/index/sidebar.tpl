<div class="blog-side-bar">
    <div class="links-bar">
        <div class="label"><?php echo _('Блог');?></div>
        <div class="spacer"></div>    
        <a href="<?php echo $this->url(array(
                'module' => 'blog',
                'controller' => 'index',
                'action' => 'index',
                'subject' => $this->subjectName, 
                'subject_id' => $this->subjectId), null, true)?>">
        <?php echo _('Стартовая страница')?>
        </a>
    </div>
    <?php if(count($this->cloudTags) > 0) :?>
    <div class="tags-bar">
        <div class="label"><?php echo _('Метки')?></div>
        <div class="spacer"></div>
        <ul class="tag-cloud">
        <?php foreach($this->cloudTags as $tag) :?>
            <li><a href="<?php echo $this->url(array(
                'module' => 'blog',
                'controller' => 'index',
                'action' => 'index',
                'subject' => $this->subjectName, 
                'subject_id' => $this->subjectId,
                'filter' => 'tag'), null, true)?>?tag=<?=$tag->body?>" class="tag<?php if($tag->num > 0) { print ' tag'.$tag->num; }?>" rel="tag"><?php echo $tag->body?></a></li>
        <?php endforeach;?>
        </ul>
    </div>
    <?php endif;?>
    <?php if(count($this->archiveDates) > 0) :?>
    <div class="archive-bar">
        <div class="label"><?php echo _('Архив')?></div>
        <div class="spacer"></div>
        <?php foreach($this->archiveDates as $filterDate => $arhiveDate) :?>
            <a href="<?php echo $this->url(array(
            'module' => 'blog',
            'controller' => 'index',
            'action' => 'index',
            'subject' => $this->subjectName, 
            'subject_id' => $this->subjectId,
            'filter' => 'date',
            'date' => $filterDate
            ), null, true)?>"><?php echo $arhiveDate?></a><br/>
        <?php endforeach;?>
    </div>
    <?php endif;?>
    <?php if(count($this->authors) > 0) :?>
    <div class="authors-bar">
        <div class="label"><?php echo _('Авторы')?></div>
        <div class="spacer"></div>
        <?php foreach($this->authors as $author) :?>
            <a href="<?php echo $this->url(array(
            'module' => 'blog',
            'controller' => 'index',
            'action' => 'index',
            'subject' => $this->subjectName, 
            'subject_id' => $this->subjectId,
            'filter' => 'author',
            'author' => $author->MID
            ), null, true)?>"><?php echo $author->LastName .' '.$author->FirstName.' '.$author->Patronymic?></a><br/>
        <?php endforeach;?>
    </div>
    <?php endif;?>
</div>