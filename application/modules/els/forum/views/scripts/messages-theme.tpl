<?php 
$isStudent = Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),array(HM_Role_RoleModelAbstract::ROLE_STUDENT));
?>
<?php $pageMsg = $this->placeholder('page_messages'); ?>
<ul class="topic-comment<?php if(!isset($this->root)): ?>-child<?php endif ?>s">
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
    <?php if($this->isDeleting === true || !$message->flags->deleted || $hasAlive || $this->forum->moderator): ?>
    <?php $hasRating = !$this->forum->flags->subsections && !empty($message->rating); ?>
    <?php $isVisible = $message->getService()->getService('ForumSection')->isVisibleMessage($this->section, $message, null, $this->parentMessage); ?>
    <li id="msg-<?=$message->message_id ?>" class="user-<?= $message->user_id ?>-x<?php if(!$message->showed): ?> topic-comment-new<?php endif ?><?php if($message->flags->deleted): ?> topic-comment-deleted<?php endif; ?><?php if ($hasRating): ?> topic-comment-has-score<?php endif; ?><?php if (!$isVisible): ?> topic-comment-invisible<?php endif; ?>">
        <?php if($message->flags->deleted): ?>
            <div class="topic-comment-header">
                <span class="topic-comment-title"><span class="folding-dots-holder"><span class="folding-dots"></span></span></span>
            </div>
            <p>
                <?= $pageMsg->deletedComment ?>
                <?php if ($message->deleted_by && isset($this->moderatorsList[$message->deleted_by])): ?>
                <?php $userUrl = $this->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => $message->deleted_by), 'default', true); ?>
                <?= $this->cardLink($userUrl, $this->moderatorsList[$message->deleted_by], 'link', array('pcard', 'topic-comment-deleted-by')); ?>
                <?php if ($message->delete_date): ?>
                <?php $date = new HM_Date($message->delete_date); ?>
                <time class="topic-comment-deleted-date"><?= $date->get(Zend_Date::DATETIME_MEDIUM); ?></time>
                <?php endif; // $message->delete_date ?>
                <?php endif; // $message->deleted_by && isset($this->moderatorsList[$message->deleted_by]) ?>
            </p>
        <?php elseif(!$isVisible): ?>
            <div class="topic-comment-header">
                <span class="topic-comment-title"><span class="folding-dots-holder"><span class="folding-dots"></span></span></span>
            </div>
            <p><?= $pageMsg->hiddenComment ?></p>
        <?php else: ?>
            <div class="topic-comment-header">
                <span class="topic-comment-title"><span class="folding-dots-holder"><span class="folding-dots"></span></span></span>
                <span class="topic-comment-author-and-pubdate">
                    <?php
                    $userUrl = $this->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => $message->user_id), 'default', true);
                    $userImg = Zend_Registry::get('serviceContainer')->getService('User')->getImageSrc($message->user_id);
                    $userImg = ($userImg)? '/' . Zend_Registry::get('config')->src->upload->photo . $userImg : '/images/content-modules/nophoto-small.gif';
                    ?>
                    <?php if($isStudent) :?>
						<span class="pcard-link topic-comment-author-userpic lightbox">
						<?php echo '<img  src="' . $this->serverUrl($userImg) . '"><div class="topic-comment-new-marker">'.$pageMsg->newComment.'</div>'; ?>
						</span>
					<?php else : ?>
						<?php echo $this->cardLink($userUrl, '<img src="' . $this->serverUrl($userImg) . '"><div class="topic-comment-new-marker">'.$pageMsg->newComment.'</div>', 'html', array('pcard', 'topic-comment-author-userpic')); ?>
					<?php endif; ?>
                    <a href="<?= $this->url(array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'user_id' => $message->user_id), 'default', true) ?>" data-user-id="<?= $message->user_id ?>" class="topic-comment-author-username"><?= $message->user_name ?></a><?php
                    if(isset($this->parentMessage)): ?>
                    <?php $parentUserUrl = $this->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => $this->parentMessage->user_id), 'default', true); ?>
                    <span class="sep">→</span>
                    <a href="<?= $parentUserUrl ?>" data-user-id="<?= $this->parentMessage->user_id ?>" class="topic-comment-author-username"><?= $this->parentMessage->user_name ?></a><?php
                    endif ?><span class="sep">,</span>
                    <time datetime="<?= $message->created ?>"><?= $message->createdDateTime() ?></time>
                    <?php if ($message->is_hidden) { echo sprintf(" /%s/", $pageMsg->hiddenComment);}?>

                </span>

                <?php if($message->rating):?><span class="topic-comment-score"><?= $pageMsg->score ?>: <span class="topic-comment-score-value"><?= $message->rating ?></span></span><?php endif; ?>
                <?php if(isset($this->parentMessage) && !$this->parentMessage->flags->deleted): ?><span class="topic-comment-parent"><a href="#msg-<?= $this->parentMessage->message_id ?>" title="<?= $pageMsg->jumpToParent ?>"><?= $pageMsg->jumpToParent ?></a></span><?php endif ?>
            </div>
            <div class="topic-comment-text">
                <?php if(!empty($message->title)): ?><h4 class="topic-comment-title"><?= $this->escape($message->title) ?></h4><?php endif; ?>
                <?php if(!empty($message->text)): ?><div class="topic-comment-text-content"><?= $message->text ?></div><?php endif ?>
            </div>
            <?php if (!$this->section->flags->closed): ?>
            <div class="topic-comment-footer">
                <a href="<?= $this->section->url(array('answer_to' => $message->message_id)) ?>" class="ui-widget ui-button topic-comment-reply"><?= $pageMsg->reply ?></a>

                <?php if(!$this->forum->flags->subsections && $this->forum->moderator && $message->createdByStudent): ?>
                <form action="<?= $message->url() ?>" method="POST"><label for="msg-<?=$message->message_id ?>-scoreme"><?= $pageMsg->score ?></label>
                    <select id="msg-<?=$message->message_id ?>-scoreme" name="rating">
                    <?= $this->placeholder('ratings'); ?>
                    </select>
                </form>
                <?php $this->inlineScript()->captureStart(); ?>
                    $(document).ready(function () {
                        <?php if (!empty($message->rating)): ?>
                        $('#msg-<?=$message->message_id ?>-scoreme').val(<?= Zend_Json::encode($message->rating_raw) ?>);
                        <?php endif; ?>
                        $('#msg-<?=$message->message_id ?>-scoreme').selectmenu();
                    });
                <?php $this->inlineScript()->captureEnd(); ?>
                <?php endif ?>
				<?php if(!$isStudent) : /* убираем кнопку удаления от студентов */ ?>
					<?php if($this->forum->moderator || $this->placeholder('current_user')->MID == $message->user_id && empty($message->rating)): ?>
						<a href="<?= $message->url(array('message' => null, 'delete' => $message->message_id)) ?>" class="ui-widget ui-button topic-comment-delete"><?= $pageMsg->deleteComment ?></a>
					<?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif ?>
        <?php if(!empty($answers)): ?>
        <?=$this->partial('messages-theme.tpl', array(
            'forum'          => $this->forum,
            'section'        => $this->section,
            'messages'       => $answers,
            'parentMessage'  => $message,
            'moderatorsList' => $this->moderatorsList
        ))?>
        <?php endif ?>
    </li>
    <?php endif; // $this->isDeleting === true || !$message->flags->deleted || !empty($answersAlive) || $this->forum->moderator ?>
<?php endforeach ?>
</ul>