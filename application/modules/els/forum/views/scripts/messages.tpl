<?php $pageMsg = $this->placeholder('page_messages'); ?>
<ul class="<?php if(isset($this->root)): ?>topic-comments<?php else: ?>topic-comment-childs<?php endif ?>">
<?php foreach($this->messages as $message): ?>
    <?php $answers = $message->getAnswers() ?>
    <?php
        // Проверка на существование не удалённых ответов на сообщение
        $answersAll = $message->getAnswers(true);
        $hasAlive = false;
        foreach($answersAll as $answer){
            if (!$answer->flags->deleted){
                $hasAlive = true;
                break;
            }
        }
    ?>
    <?php if(!$message->flags->deleted || $hasAlive || $this->forum->moderator): ?>
    <?php $isVisible = $message->getService()->getService('ForumSection')->isVisibleMessage($this->section, $message, null, $this->parentMessage); ?>
    <li class="<?php if(!empty($message->text)): ?>topic-comment-text-loaded<?php endif ?><?php if(!$message->showed): ?> topic-comment-new<?php endif ?><?php if($message->flags->deleted): ?> topic-comment-deleted<?php endif; ?><?php if (!$isVisible): ?> topic-comment-invisible<?php endif; ?>">
        <?php if($message->flags->deleted): ?>
            <div class="topic-comment-header">
                <span class="topic-comment-title"><span class="folding-dots-holder"><span class="folding-dots"></span></span>
                    <span><?= $pageMsg->deletedComment ?></span></span>
            </div>
        <?php elseif(!$isVisible): ?>
            <div class="topic-comment-header">
                    <span class="topic-comment-title"><span class="folding-dots-holder"><span class="folding-dots"></span></span>
                        <span><?= $pageMsg->hiddenComment ?></span></span>
            </div>
        <?php else: ?>
            <div class="topic-comment-header">
                <span class="topic-comment-title"><span class="folding-dots-holder"><span class="folding-dots"></span></span>
                    <a href="<?=$this->section->url(array('msglist' => null)) ?>#<?=$message->message_id ?>"><?=$this->escape(empty($message->title) ? $message->text_preview : $message->title) ?></a>
                    <a href="<?=$message->url(array(), null, true) ?>" data-ping-url="<?=$message->url(array(), null, true) ?>" class="topic-comment-ajaxload" title="<?=$pageMsg->loadComment ?>"><?=$pageMsg->loadComment ?></a></span>
                <span class="topic-comment-author-and-pubdate">
                    <?php echo $this->cardLink($this->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => $message->user_id), 'default', true), $message->user_name, 'link', array('pcard', 'topic-comment-author-username')); ?>,
                    <time datetime="<?= $message->created ?>"><?= $message->createdDateTime() ?></time>
                </span>
                <?php if(!$message->showed): ?><span class="topic-comment-new-marker"><?= $pageMsg->newComment ?></span><?php endif ?>
                <?php if(!empty($message->score)): ?><span class="topic-comment-score"><?= $pageMsg->score ?>: <span class="topic-comment-score-value"><?= $message->score ?></span></span><?php endif ?>
            </div>

            <?php if(!empty($message->text)): ?>
            <div class="topic-comment-text">
                <div class="topic-comment-text-content"><?= $message->text ?></div>
            </div>
            <?php endif ?>
        <?php endif ?>
        <?php if(!empty($answers)): ?>
        <?=$this->partial('messages.tpl', array(
            'section'  => $this->section,
            'messages' => $answers,
            'forum'    => $this->forum,
            'parentMessage' => $message,
        ))?>
        <?php endif ?>
    </li>
    <?php endif; // !$message->flags->deleted || $hasAlive || $this->forum->moderator ?>
<?php endforeach ?>
</ul>