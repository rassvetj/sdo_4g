<?php 
$isStudent = Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),array(HM_Role_RoleModelAbstract::ROLE_STUDENT));
?>
<?php $pageMsg = $this->placeholder('page_messages'); ?>
<div class="topics-list-label"><?= _("Комментарии") ?></div>
<ul class="topics-list">
    <?php foreach($this->sections as $section): ?>
    <?php $cardLink = $this->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => $section->user_id), 'default', true); ?>
    <li class="topic<?php if(!empty($section->text)): ?> topic-text-loaded<?php endif ?><?php if($section->isPinned()): ?> topic-pinned<?php endif ?><?php if ($section->flags->closed): ?> topic-closed<?php endif; ?>">
        <div class="topic-header">
            <div class="topic-author-and-pubdate">
                <?php
                $userImg = Zend_Registry::get('serviceContainer')->getService('User')->getImageSrc($section->user_id);
                $userImg = ($userImg)? '/' . Zend_Registry::get('config')->src->upload->photo . $userImg : '/images/content-modules/nophoto-small.gif';
                ?>
                <?php if($isStudent) :?>
					<span class="pcard-link topic-author-userpic lightbox">
						<?='<img src="' . $this->serverUrl($userImg) . '"><div class="topic-new-marker">' . $pageMsg->newTheme . '</div>'; ?>
					</span>
					<span class="pcard-link topic-author-username lightbox">
						<?=$section->user_name; ?>,
					</span>
                <?php else : ?>
					<?=$this->cardLink($cardLink, '<img src="' . $this->serverUrl($userImg) . '"><div class="topic-new-marker">' . $pageMsg->newTheme . '</div>', 'html', array('pcard', 'topic-author-userpic')); ?>
					<?=$this->cardLink($cardLink, $section->user_name, 'text', array('pcard', 'topic-author-username')); ?>,
				<?php endif; ?>
				<time datetime="<?= $section->created ?>"><?= $section->createdDateTime() ?></time>
                <?php if($this->moderator): ?>
                <span class="topic-pin-marker">
                    <a href="<?=$section->url(array('order' => 1), null) ?>" class="pin" title="<?=$pageMsg->pinSection ?>"><?=$pageMsg->pinSection ?></a>
                    <a href="<?=$section->url(array('order' => 0), null) ?>" class="unpin" title="<?=$pageMsg->unpinSection ?>"><?=$pageMsg->unpinSection ?></a>
                </span>
                <?php endif ?>
            </div>
            <?php /* TODO добавил параметр text_only для загрузки текста темы и url для ping'а */ ?>
            <h3 class="topic-title"><a href="<?=$section->url() ?>"><?=$this->escape($section->title) ?></a><?php if(!empty($section->text)): ?><a href="<?= $section->url(array('text_only' => 1)) ?>" class="topic-ajaxload" title="<?=$pageMsg->loadMsg ?>"><?=$pageMsg->loadMsg ?></a><?php endif ?></h3>
        </div>
        <?php if(!empty($section->text)): ?>
        <div class="topic-text">
            <div class="topic-text-content"><?= $section->text ?></div>
        </div>
        <?php endif ?>
        <div class="topic-stats"><span class="ns-aligner"></span><span class="topic-stats-text">
            <span><?=$section->getService()->getService('ForumSection')->getMessagesCount($section)/*count_msg*/ ?></span><a href="<?=$section->url(array('msglist' => 1)) ?>" class="topic-expand-comments"><?=$pageMsg->expComments ?></a>
        </span></div>
        <?php if(!empty($section->messages)): ?>
        <?=$this->partial('messages.tpl', array(
            'section'  => $section,
            'messages' => $section->messages,
            'forum'    => $this->forum,
            'root'     => true
        )) ?>
        <?php endif ?>
        <div class="topic-footer"><a href="<?=$section->url() ?>"><?= $pageMsg->openTheme ?></a> <?php if (!$section->lesson_id):?><span class="topic-expand-comments"><?= $pageMsg->hideComments ?></span><?php else:?><label for="subscribeSection"><?php echo _('Следить за темой');?>: </label><input type="checkbox" id='subscribeSection' name='subscribeSection' <?php if($this->isSubscriber):?>checked="checked"<?php endif;?> /><?php endif; ?></div>
    </li>
    <?php endforeach ?>
</ul>