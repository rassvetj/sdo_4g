<div class="blog-post">
    <div class="post-header">
        <div class="post-title">
			<span><?php echo $blogPost->title?></span>
	        <div class="post-controls">
	            <?php if($this->isModerator):?>
	            <a class="edit" href="<?php echo $this->url(array(
	                'module' => 'blog',
	                'controller' => 'index',
	                'action' => 'edit',
	                'subject' => $this->subjectName,
	                'subject_id' => $this->subjectId,
	                'blog_id' => $blogPost->id
	            ), null, true)?>"></a>
	            <a class="delete" href="<?php echo $this->url(array(
	                'module' => 'blog',
	                'controller' => 'index',
	                'action' => 'delete',
	                'subject' => $this->subjectName,
	                'subject_id' => $this->subjectId,
	                'blog_id' => $blogPost->id
	            ), null, true)?>"></a>
	            <?php endif;?>
	        </div>
		</div>
    </div>
    <div class="spacer"></div>
    <div class="post-info">
        <img src="<?php echo $blogPost->author_avatar?>"/>
        <div class="post-author">
            <a href="<?php echo $this->url(array(
                'module' => 'blog',
                'controller' => 'index',
                'action' => 'index',
                'subject' => $this->subjectName,
                'subject_id' => $this->subjectId,
                'filter' => 'author',
                'author' => $blogPost->created_by
            ), null, true)?>"><?php echo $blogPost->author?></a><br/>
            <span><?php echo date('d.m.Y, H:i', strtotime($blogPost->created))?></span>
        </div>
    </div>
    <div class="spacer"></div>
    <div class="post-body formatted-text">
        <?php if($this->isFullView):?>
        <?php echo stripslashes($blogPost->body);?>
        <?php else:?>
        <?php echo $blogPost->getCut();?>
        <?php endif;?>
    </div>

    <?php if ((!$this->isFullView) && $blogPost->fullViewEnabled()): ?>
    <div class="post-more">
        <a href="<?php echo $this->url(array(
                'module' => 'blog',
                'controller' => 'index',
                'action' => 'view',
                'subject' => $this->subjectName,
                'subject_id' => $this->subjectId,
                'blog_id' => $blogPost->id
            ), null, true)?>"><?php echo _('Читать далее')?></a>
    </div>
    <?php endif;?>

    <div class="spacer"></div>
    <?php if(count($blogPost->tags) > 0):?>
    <div class="post-tags">
        <?php echo _('Метки')?>:
        <?php $i=1; foreach($blogPost->tags as $tag):?>
            <a href="<?php echo $this->url(array(
                'module' => 'blog',
                'controller' => 'index',
                'action' => 'index',
                'subject' => $this->subjectName,
                'subject_id' => $this->subjectId,
                'filter' => 'tag',
                'tag' => $tag->body), null, true)?>"><?php echo $tag->body?></a><?php if($i != count($blogPost->tags)):?>,<?php endif; $i++;?>
        <?php endforeach;?>
    </div>
    <?php endif;?>
    <?php
        $linksUrl = $this->url(array(
            'module' => 'blog',
            'controller' => 'index',
            'action' => 'view',
            'subject' => $this->subjectName,
            'subject_id' => $this->subjectId,
            'blog_id' => $blogPost->id
            ), null, true);
        echo $this->comments($blogPost->comments,
            $blogPost->comments_count,
            $linksUrl,
            $this->isFullView,
            $this->form,
            'blog'
        );
    ?>
    <hr/>
</div>
