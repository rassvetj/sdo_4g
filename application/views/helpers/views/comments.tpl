<div class="post-comments">
    <?php if($this->comments_count > 0):?>
    <a class="comments" href="<?php echo $this->linksUrl?>#comments"><?php echo _('Комментарии')?></a>
    <b>(<?php echo $this->comments_count?>)</b>
    <?php endif;?>

    <?php if($this->isFullView):?>
    <ul class="comments">
        <?php foreach($this->comments as $comment):?>
        <li class="comment" id="comment_<?php echo $comment->id?>">
            <div class="comment-info">
                <ul class="comment-author">
                    <li class="avatar"><img src="<?php echo $comment->author_avatar?>"/></li>
                    <li class="name"><?php echo $comment->author?>,</li>
                    <li class="date"><?php echo date('d.m.Y, H:i', strtotime($comment->created))?></li>
                    <li class="bookmark"><a rel="bookmark" title="<?php echo _('Ссылка на комментарий')?>" href="#comment_<?php echo $comment->id?>">#</a></li>
                </ul>
            </div>
            <div class="comment-message"><?php echo nl2br($comment->message)?></div>
        </li>
        <?php endforeach;?>
    </ul>
    <div class="spacer"></div>
    <?php if ($this->canComment):?>
    <?php echo $this->form?>
    <?php endif; ?>
    <?php elseif($this->comments_count == 0):?>
    <a href="<?php echo $this->linksUrl?>#comments_form">
        <?php echo _('Оставить комментарий')?></a>
    <?php endif;?>
</div>