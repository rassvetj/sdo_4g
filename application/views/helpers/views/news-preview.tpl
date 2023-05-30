<div class="news-post">

    <div class="news-header">
        <div class="news-controls">
            <?php if($this->isModerator):?>
            <a class="edit" href="<?php echo $this->url(array(
                'module' => 'news',
                'controller' => 'index',
                'action' => 'edit',
                'subject' => $this->news->subject_name, 
                'subject_id' => $this->news->subject_id,
                'news_id' => $this->news->id
            ), null, true)?>"></a>
            <a class="delete" href="<?php echo $this->url(array(
                'module' => 'news',
                'controller' => 'index',
                'action' => 'delete',
                'subject' => $this->news->subject_name, 
                'subject_id' => $this->news->subject_id,
                'news_id' => $this->news->id
            ), null, true)?>"></a>
            <?php endif;?>
            <div class="news-date"><?php echo date('d.m.Y, H:i', strtotime($this->news->created))?></div>
        </div>
        <div class="news-title"><a href="<?php echo $this->url(array(
                'module' => 'news',
                'controller' => 'index',
                'action' => 'view',
                'subject' => $this->news->subject_name, 
                'subject_id' => $this->news->subject_id,
                'news_id' => $this->news->id
            ), null, true)?>"><?php echo $this->news->announce?></a></div>
    </div>
    
    <?php if($this->fullView):?>
    	<div class="spacer"></div>
    <?php endif;?>
    <div class="spacer"></div>
    
    <div class="news-body">
        <?php if($this->fullView):?>
        <?php echo stripslashes($this->news->message);?>
        <?php else:?>
        <?php echo $this->news->getCut();?>
        <?php endif;?>
    </div>
    
    <div class="spacer"></div>
    <?php if($this->fullView):?><hr/><?php endif;?>
    
    <div class="news-info">
        <div class="news-author">
            <?php if($this->news->created_by):?>
                <?php if ($this->showUserCard) :
                    echo $this->cardLink($this->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => $this->news->created_by)));
                endif; ?>
                <a href="<?php echo $this->url(array(
                    'module' => 'news',
                    'controller' => 'index',
                    'action' => 'index',
                    'subject' => $this->news->subject_name,
                    'subject_id' => $this->news->subject_id,
                    'filter' => 'author',
                    'author' => $this->news->created_by
                ), null, true)?>">
                <?php echo $this->news->author?></a>
            <?php else:?>
                <?php echo $this->news->author?>
            <?php endif;?>
        </div>
	    <div class="news-comments">
	        <?php if($this->news->comments_count > 0):?>
	        <a id="news-comments" href="<?php echo $this->url(array(
	                'module' => 'news',
	                'controller' => 'index',
	                'action' => 'view',
	                'subject' => $this->news->subject_name, 
	                'subject_id' => $this->news->subject_id,
	                'news_id' => $this->news->id
	            ), null, true)?>#comments"><?php echo _('Комментарии')?></a>
	            <b>(<?php echo $this->news->comments_count?>)</b>
	        <?php endif;?>
	        
	        <?php if($this->fullView):?>
	        	<?php if(count($this->news->comments)):?>
		            <ul class="news-comments">
		                <?php foreach($this->news->comments as $comment):?>
		                <li class="news-comment" id="news-comment_<?php echo $comment->id?>">
		                    <div class="news-comment-info">
		                        <ul class="news-comment-author">
		                            <li class="avatar"><img src="<?php echo $comment->author_avatar?>"/></li>
		                            <li class="name"><?php echo $comment->author?>,</li>
		                            <li class="date"><?php echo date('d.m.Y, H:i', strtotime($comment->created))?></li>
		                            <li class="bookmark"><a rel="bookmark" title="<?php echo _('Ссылка на комментарий')?>" href="#comment_<?php echo $comment->id?>">#</a></li>
		                        </ul>
		                    </div>
		                    <div class="cnews-omment-message"><?php echo $comment->message?></div>
		                </li>
		                <?php endforeach;?>
		            </ul>
            	<?php endif;?>
	            <div class="spacer"></div>
            	<?php //echo $this->form?>            	
	        <?php endif;?>
	    </div>
    </div>
    
    <?php if(!$this->fullView):?>
    	<div class="spacer"></div>
    	<div class="spacer"></div>
    <?php endif;?>
</div>